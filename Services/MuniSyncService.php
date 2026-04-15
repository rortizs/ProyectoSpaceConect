<?php

require_once(__DIR__ . '/../Libraries/MikroTik/RouterFactory.php');
require_once(__DIR__ . '/../Libraries/NetworkUtils/utils.php');

class MuniSyncService extends BaseService
{
    private $router;
    private $routerId;
    private $model;

    public function __construct(int $router_id)
    {
        parent::__construct();
        $this->routerId = $router_id;
        $this->model = new MuniredModel();
    }

    /**
     * Initialize router connection via RouterFactory
     * @return bool Connection success
     */
    private function connectRouter(): bool
    {
        if ($this->router && $this->router->connected) {
            return true;
        }

        $this->router = RouterFactory::createFromDatabase($this->routerId);

        if (!$this->router || !$this->router->connected) {
            return false;
        }

        return true;
    }

    // =============================================
    // USER SIMPLE QUEUE SYNC
    // =============================================

    /**
     * Sync a single user's Simple Queue to the router
     * Creates or updates the queue based on user IP and bandwidth settings
     */
    public function syncUserQueue(int $user_id): object
    {
        $res = (object) ['success' => false, 'message' => ''];

        $user = $this->model->getUser($user_id);
        if (empty($user)) {
            $res->message = 'Usuario no encontrado';
            return $res;
        }

        if ($user['status'] != 1) {
            $res->message = 'Usuario deshabilitado, no se puede sincronizar';
            return $res;
        }

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            $this->model->updateUserSyncStatus($user_id, 'error');
            return $res;
        }

        // Determine bandwidth: user's own values (already resolved with COALESCE in model)
        $upload = !empty($user['custom_upload']) ? $user['custom_upload'] : (!empty($user['default_upload']) ? $user['default_upload'] : '5M');
        $download = !empty($user['custom_download']) ? $user['custom_download'] : (!empty($user['default_download']) ? $user['default_download'] : '10M');
        $maxLimit = "{$upload}/{$download}";
        $queueName = $user['queue_name'];
        $target = $user['ip_address'] . '/32';

        try {
            // Check if queue already exists for this IP
            $existing = $this->router->APIGetQueuesSimple($user['ip_address']);

            $hasQueueData = is_array($existing->data ?? null)
                && isset($existing->data[0])
                && is_array($existing->data[0])
                && isset($existing->data[0]['.id']);

            if ($hasQueueData) {
                // Update existing queue (API returns arrays)
                $queueId = $existing->data[0]['.id'];
                $result = $this->router->APIModifyQueuesSimple($queueId, $queueName, $target, $maxLimit);
            } else {
                // Create new queue
                $result = $this->router->APIAddQueuesSimple($queueName, $target, $maxLimit);
            }

            if ($result->success) {
                $this->model->updateUserSyncStatus($user_id, 'synced');
                $res->success = true;
                $res->message = "Cola sincronizada: {$queueName} ({$maxLimit})";
            } else {
                $this->model->updateUserSyncStatus($user_id, 'error');
                $res->message = 'Error del router: ' . ($result->message ?? 'desconocido');
            }
        } catch (Exception $e) {
            $this->model->updateUserSyncStatus($user_id, 'error');
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    /**
     * Remove a user's Simple Queue from the router
     */
    public function removeUserQueue(int $user_id): object
    {
        $res = (object) ['success' => false, 'message' => ''];

        $user = $this->model->getUser($user_id);
        if (empty($user)) {
            $res->message = 'Usuario no encontrado';
            return $res;
        }

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            $result = $this->router->APIDeleteQueuesSimple($user['ip_address']);

            if ($result->success) {
                $this->model->updateUserSyncStatus($user_id, 'disabled');
                $res->success = true;
                $res->message = "Cola eliminada para {$user['ip_address']}";
            } else {
                // If queue doesn't exist, that's fine — still mark as disabled
                $this->model->updateUserSyncStatus($user_id, 'disabled');
                $res->success = true;
                $res->message = 'Cola no existia en el router (ya estaba limpio)';
            }
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    /**
     * Toggle a user's queue (disable = throttle to 1k/1k, enable = restore bandwidth)
     */
    public function toggleUserQueue(int $user_id, bool $enable): object
    {
        $res = (object) ['success' => false, 'message' => ''];

        $user = $this->model->getUser($user_id);
        if (empty($user)) {
            $res->message = 'Usuario no encontrado';
            return $res;
        }

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            $existing = $this->router->APIGetQueuesSimple($user['ip_address']);

            $hasQueueData = is_array($existing->data ?? null)
                && isset($existing->data[0])
                && is_array($existing->data[0])
                && isset($existing->data[0]['.id']);

            if ($hasQueueData) {
                // API returns arrays
                $queueId = $existing->data[0]['.id'];

                if ($enable) {
                    // Restore full bandwidth
                    $upload = !empty($user['custom_upload']) ? $user['custom_upload'] : (!empty($user['default_upload']) ? $user['default_upload'] : '5M');
                    $download = !empty($user['custom_download']) ? $user['custom_download'] : (!empty($user['default_download']) ? $user['default_download'] : '10M');
                    $maxLimit = "{$upload}/{$download}";
                } else {
                    // Throttle to minimum
                    $maxLimit = '1k/1k';
                }

                $result = $this->router->APIModifyQueuesSimple(
                    $queueId,
                    $user['queue_name'],
                    $user['ip_address'] . '/32',
                    $maxLimit
                );

                if ($result->success) {
                    $res->success = true;
                    $res->message = $enable ? 'Usuario habilitado' : 'Usuario deshabilitado (1k/1k)';
                } else {
                    $res->message = 'Error del router: ' . ($result->message ?? 'desconocido');
                }
            } else {
                if ($enable) {
                    // Queue doesn't exist, create it
                    return $this->syncUserQueue($user_id);
                } else {
                    $res->success = true;
                    $res->message = 'No habia cola activa para deshabilitar';
                }
            }
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    // =============================================
    // DEPARTMENT QUEUE SYNC
    // =============================================

    /**
     * Sync all active users' queues in a department
     */
    public function syncDepartmentQueues(int $dept_id): object
    {
        $res = (object) ['success' => false, 'synced' => 0, 'errors' => []];

        // Read all active users in the department
        $users = $this->model->getUsersByDepartment($dept_id);

        if (empty($users)) {
            $res->success = true;
            $res->message = 'No hay usuarios activos en este departamento';
            return $res;
        }

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        // Build set of existing queue IPs in router to detect missing queues
        $existingQueueIPs = [];
        $listResult = $this->router->APIListQueuesSimple();
        if ($listResult->success && is_array($listResult->data)) {
            foreach ($listResult->data as $q) {
                $targetRaw = '';
                if (is_array($q)) {
                    $targetRaw = $q['target'] ?? '';
                } elseif (is_object($q)) {
                    $targetRaw = $q->target ?? '';
                }

                if (!empty($targetRaw)) {
                    // target can come as: "10.100.0.10/32" or "10.100.0.10/32,0.0.0.0/0"
                    $firstTarget = explode(',', $targetRaw)[0];
                    $ip = str_replace('/32', '', trim($firstTarget));
                    if (!empty($ip)) {
                        $existingQueueIPs[$ip] = true;
                    }
                }
            }
        }

        foreach ($users as $user) {
            $ip = $user['ip_address'] ?? '';
            $syncStatus = $user['queue_sync_status'] ?? '';

            // Sync when explicitly pending/error OR when queue is missing in router
            $needsSync = ($syncStatus !== 'synced') || empty($existingQueueIPs[$ip]);
            if (!$needsSync) {
                continue;
            }

            $result = $this->syncUserQueue($user['id']);

            if ($result->success) {
                $res->synced++;
            } else {
                $res->errors[] = [
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'ip' => $user['ip_address'],
                    'error' => $result->message,
                ];
            }
        }

        $res->success = empty($res->errors);
        $res->message = "Sincronizados: {$res->synced}, Errores: " . count($res->errors);
        return $res;
    }

    // =============================================
    // QoS STATUS (Queue Trees - READ ONLY)
    // =============================================

    /**
     * Read existing Queue Trees from router (Digicom's DESCARGAS/SUBIDAS trees)
     * This is READ-ONLY — we do NOT create or modify Queue Trees
     */
    public function getQoSStatus(): object
    {
        $res = (object) ['success' => false, 'trees' => [], 'message' => ''];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router. Verifique que la API REST este habilitada.';
            return $res;
        }

        try {
            $existingTrees = $this->router->APIListQueueTree();

            if ($existingTrees->success && is_array($existingTrees->data)) {
                foreach ($existingTrees->data as $tree) {
                    $res->trees[] = [
                        'id' => $tree->{'.id'} ?? '',
                        'name' => $tree->name ?? '',
                        'parent' => $tree->parent ?? '',
                        'max_limit' => $tree->{'max-limit'} ?? '',
                        'priority' => $tree->priority ?? '',
                        'comment' => $tree->comment ?? '',
                    ];
                }
                $res->success = true;
                $res->message = 'Queue Trees leidos: ' . count($res->trees);
            } else {
                $res->message = 'No se pudieron leer los Queue Trees';
            }
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    // =============================================
    // CONTENT FILTERING SYNC
    // =============================================

    /**
     * Sync content filtering: DNS blocks (global) + firewall address-lists (per-dept whitelist)
     */
    public function syncContentFiltering(): object
    {
        $res = (object) ['success' => false, 'blocked' => 0, 'whitelisted' => 0, 'errors' => []];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            // Step 1: Sync global DNS blocks from content_filter_domains
            $blacklist = $this->model->getGlobalBlacklist();

            foreach ($blacklist as $entry) {
                $result = $this->router->APIAddDNSBlock($entry['domain'], '0.0.0.0');
                if ($result->success || (isset($result->message) && strpos($result->message, 'already exists') !== false)) {
                    $res->blocked++;
                } else {
                    $res->errors[] = [
                        'type' => 'dns_block',
                        'domain' => $entry['domain'],
                        'error' => $result->message ?? 'desconocido',
                    ];
                }
            }

            // Step 2: Sync per-department whitelist via firewall address-lists
            // Uses individual user IPs (flat network), not subnet ranges
            $departments = $this->model->getDepartments($this->routerId);

            foreach ($departments as $dept) {
                $whitelist = $this->model->getDeptWhitelist($dept['id']);
                $listName = 'muni-whitelist-' . sanitizeQueueName($dept['name']);

                if (empty($whitelist)) {
                    continue;
                }

                // Get active user IPs for this department
                $users = $this->model->getUsersByDepartment($dept['id']);

                foreach ($users as $user) {
                    $result = $this->router->APIAddFirewallAddress(
                        $user['ip_address'],
                        $listName,
                        'Whitelist user: ' . $user['name'] . ' (' . $dept['name'] . ')'
                    );

                    if ($result->success || (isset($result->message) && strpos($result->message, 'already exists') !== false)) {
                        $res->whitelisted++;
                    } else {
                        $res->errors[] = [
                            'type' => 'whitelist',
                            'dept' => $dept['name'],
                            'user' => $user['name'],
                            'ip' => $user['ip_address'],
                            'error' => $result->message ?? 'desconocido',
                        ];
                    }
                }
            }

            $res->success = true;
            $res->message = "Filtrado sincronizado. Bloqueados: {$res->blocked}, Whitelist: {$res->whitelisted}, Errores: " . count($res->errors);
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    // =============================================
    // FULL SYNC
    // =============================================

    /**
     * Full sync: all user queues (Simple Queues) + content filtering
     * Note: Queue Trees are managed by Digicom (DESCARGAS/SUBIDAS) — we only read their status
     */
    public function syncAll(): object
    {
        $res = (object) [
            'success' => false,
            'results' => [
                'queues' => null,
                'qos_status' => null,
                'filtering' => null,
            ],
        ];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router. Verifique que la API REST este habilitada.';
            return $res;
        }

        // 1. Sync all department queues (Simple Queues per user)
        $departments = $this->model->getDepartments($this->routerId);
        $queueResults = (object) ['synced' => 0, 'errors' => []];

        foreach ($departments as $dept) {
            $deptResult = $this->syncDepartmentQueues($dept['id']);
            $queueResults->synced += $deptResult->synced;
            $queueResults->errors = array_merge($queueResults->errors, $deptResult->errors ?? []);
        }
        $res->results['queues'] = $queueResults;

        // 2. Read QoS status (read-only, no modifications)
        $res->results['qos_status'] = $this->getQoSStatus();

        // 3. Sync content filtering
        // Legacy RouterOS API can make this stage very slow/noisy in full sync;
        // keep full-sync fast and stable by skipping filtering here for legacy routers.
        if ($this->router instanceof RouterLegacy) {
            $res->results['filtering'] = (object) [
                'success' => true,
                'blocked' => 0,
                'whitelisted' => 0,
                'errors' => [],
                'message' => 'Filtrado omitido en syncAll para router legacy (use sincronización de filtrado dedicada).',
            ];
        } else {
            $res->results['filtering'] = $this->syncContentFiltering();
        }

        $res->success = true;
        $res->message = sprintf(
            'Sync completo. Colas: %d, Queue Trees: %d, Dominios bloqueados: %d',
            $queueResults->synced,
            count($res->results['qos_status']->trees ?? []),
            $res->results['filtering']->blocked ?? 0
        );

        return $res;
    }

    // =============================================
    // BANDWIDTH STATS (Real-time from Router)
    // =============================================

    /**
     * Get bandwidth stats from Simple Queues (real-time from router)
     * Parses MikroTik queue stats and cross-references with DB for metadata
     */
    public function getBandwidthStats(): object
    {
        $res = (object) [
            'success' => false,
            'queues' => [],
            'total_download' => 0,
            'total_upload' => 0,
            'active_count' => 0,
            'disabled_count' => 0,
            'message' => '',
        ];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            $apiResult = $this->router->APIListQueuesSimple();

            if (!$apiResult->success || !is_array($apiResult->data)) {
                $res->message = 'No se pudieron leer las Simple Queues';
                return $res;
            }

            // Get all muni users for cross-referencing (indexed by IP)
            $allUsers = $this->model->getUsers(['router_id' => $this->routerId]);
            $usersByIP = [];
            foreach ($allUsers as $u) {
                if (!empty($u['ip_address'])) {
                    $usersByIP[$u['ip_address']] = $u;
                }
            }

            foreach ($apiResult->data as $queue) {
                // RouterOS API returns arrays, not objects
                $name = $queue['name'] ?? '';
                $target = $queue['target'] ?? '';
                $maxLimit = $queue['max-limit'] ?? '0/0';
                $bytesStr = $queue['bytes'] ?? '0/0';
                $disabled = isset($queue['disabled']) && ($queue['disabled'] === 'true' || $queue['disabled'] === true);

                // Parse bytes "upload/download" format
                $bytesParts = $this->parseQueueBytes($bytesStr);
                $limitParts = explode('/', $maxLimit);

                // Extract IP from target (remove /32)
                $ip = str_replace('/32', '', $target);
                
                // Try to match with a muni user by IP address (more reliable than queue name)
                $userName = $name; // fallback to queue name if no match
                $userDepartment = null;
                $userId = null;

                if (isset($usersByIP[$ip])) {
                    $u = $usersByIP[$ip];
                    $userName = $u['name'] ?? $name;
                    $userDepartment = $u['department_name'] ?? null;
                    $userId = $u['id'] ?? null;
                }

                $queueData = [
                    'user_id' => $userId,
                    'name' => $userName,
                    'department' => $userDepartment,
                    'queue_name' => $name,
                    'ip' => $ip,
                    'upload_bytes' => $bytesParts['upload'],
                    'download_bytes' => $bytesParts['download'],
                    'max_limit' => $maxLimit,
                    'max_upload' => $limitParts[0] ?? '0',
                    'max_download' => $limitParts[1] ?? '0',
                    'disabled' => $disabled,
                ];

                $res->queues[] = $queueData;

                if ($disabled) {
                    $res->disabled_count++;
                } else {
                    $res->active_count++;
                }

                $res->total_download += $bytesParts['download'];
                $res->total_upload += $bytesParts['upload'];
            }

            // Sort queues by download bytes descending (top consumers first)
            usort($res->queues, function ($a, $b) {
                return $b['download_bytes'] - $a['download_bytes'];
            });

            $res->success = true;
            $res->message = 'Stats obtenidos: ' . count($res->queues) . ' queues';
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    /**
     * Parse MikroTik bytes format "upload/download" into integers
     */
    private function parseQueueBytes(string $bytesStr): array
    {
        $parts = explode('/', $bytesStr);
        return [
            'upload' => intval($parts[0] ?? 0),
            'download' => intval($parts[1] ?? 0),
        ];
    }

    // =============================================
    // ROUTER STATUS
    // =============================================

    /**
     * Get router connection status and system resources
     */
    public function getRouterStatus(): object
    {
        $res = (object) ['success' => false, 'connected' => false, 'data' => null];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            $resources = $this->router->APIGetSystemResources();

            if ($resources->success) {
                $res->success = true;
                $res->connected = true;
                $res->data = [
                    'version' => $resources->data->version ?? 'N/A',
                    'board_name' => $resources->data->{'board-name'} ?? 'N/A',
                    'cpu_load' => $resources->data->{'cpu-load'} ?? 0,
                    'free_memory' => $resources->data->{'free-memory'} ?? 0,
                    'total_memory' => $resources->data->{'total-memory'} ?? 0,
                    'uptime' => $resources->data->uptime ?? 'N/A',
                ];
            }
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    /**
     * Get user stats from router by user ID
     */
    public function getUserStats(int $userId): object
    {
        $res = (object) ['success' => false, 'data' => null];

        // Get user IP from database
        $user = $this->model->getUser($userId);
        if (empty($user) || empty($user['ip_address'])) {
            $res->message = 'Usuario no encontrado';
            return $res;
        }

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        try {
            // Get queue stats for this IP
            $queueResult = $this->router->APIGetQueuesSimple($user['ip_address']);

            if ($queueResult->success && !empty($queueResult->data)) {
                $queue = $queueResult->data[0];
                $bytesStr = $queue['bytes'] ?? '0/0';
                $bytesParts = $this->parseQueueBytes($bytesStr);

                $res->success = true;
                $res->data = [
                    'ip' => $user['ip_address'],
                    'name' => $queue['name'] ?? $user['queue_name'],
                    'bytes' => $bytesStr,
                    'upload_bytes' => $bytesParts['upload'],
                    'download_bytes' => $bytesParts['download'],
                    'max_limit' => $queue['max-limit'] ?? '0/0',
                ];
            } else {
                $res->message = 'Queue no encontrada en router';
            }
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }
}
