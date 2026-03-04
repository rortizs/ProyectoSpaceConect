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

        // Determine bandwidth: custom or department default
        $upload = !empty($user['custom_upload']) ? $user['custom_upload'] : $user['default_upload'];
        $download = !empty($user['custom_download']) ? $user['custom_download'] : $user['default_download'];
        $maxLimit = "{$upload}/{$download}";
        $queueName = $user['queue_name'];
        $target = $user['ip_address'] . '/32';

        try {
            // Check if queue already exists for this IP
            $existing = $this->router->APIGetQueuesSimple($user['ip_address']);

            if (!empty($existing->data) && count($existing->data) > 0) {
                // Update existing queue
                $queueId = $existing->data[0]->{'.id'};
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

            if (!empty($existing->data) && count($existing->data) > 0) {
                $queueId = $existing->data[0]->{'.id'};

                if ($enable) {
                    // Restore full bandwidth
                    $upload = !empty($user['custom_upload']) ? $user['custom_upload'] : $user['default_upload'];
                    $download = !empty($user['custom_download']) ? $user['custom_download'] : $user['default_download'];
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

        foreach ($users as $user) {
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
    // QoS HIERARCHY (Queue Trees)
    // =============================================

    /**
     * Sync the full QoS hierarchy for all departments on a router
     * Creates: parent "muni-global" Queue Tree → child per department
     */
    public function syncQoSHierarchy(): object
    {
        $res = (object) ['success' => false, 'synced' => 0, 'errors' => []];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        $departments = $this->model->getDepartments($this->routerId);

        if (empty($departments)) {
            $res->success = true;
            $res->message = 'No hay departamentos configurados';
            return $res;
        }

        try {
            // Step 1: Ensure parent Queue Tree "muni-global" exists
            $parentName = 'muni-global';
            $parentResult = $this->ensureQueueTree($parentName, 'global', '37M/251M', [
                'comment' => 'Municipal Network - Global QoS Parent',
                'priority' => 1,
            ]);

            if (!$parentResult->success) {
                $res->message = "Error creando Queue Tree padre: " . ($parentResult->message ?? '');
                return $res;
            }

            // Step 2: Create/update per-department Queue Trees
            foreach ($departments as $dept) {
                if (empty($dept['qos_max_limit'])) {
                    continue; // Skip departments without QoS config
                }

                $deptQueueName = 'muni-dept-' . sanitizeQueueName($dept['name']);

                $deptResult = $this->ensureQueueTree($deptQueueName, $parentName, $dept['qos_max_limit'], [
                    'comment' => 'Municipal Dept: ' . $dept['name'],
                    'priority' => intval($dept['priority']),
                ]);

                if ($deptResult->success) {
                    // Store the MikroTik .id for reference
                    $queueTreeId = $deptResult->queue_tree_id ?? null;
                    $this->model->updateQosSyncStatus($dept['id'], 'synced', $queueTreeId);
                    $res->synced++;
                } else {
                    $this->model->updateQosSyncStatus($dept['id'], 'error');
                    $res->errors[] = [
                        'dept_id' => $dept['id'],
                        'name' => $dept['name'],
                        'error' => $deptResult->message ?? 'desconocido',
                    ];
                }
            }

            $res->success = empty($res->errors);
            $res->message = "QoS sincronizado. Deptos: {$res->synced}, Errores: " . count($res->errors);
        } catch (Exception $e) {
            $res->message = 'Excepcion: ' . $e->getMessage();
        }

        return $res;
    }

    /**
     * Ensure a Queue Tree exists (create or update)
     */
    private function ensureQueueTree(string $name, string $parent, string $maxLimit, array $options = []): object
    {
        $res = (object) ['success' => false, 'message' => '', 'queue_tree_id' => null];

        // Search for existing Queue Tree by name
        $existingTrees = $this->router->APIListQueueTree();

        $existingId = null;
        if ($existingTrees->success && is_array($existingTrees->data)) {
            foreach ($existingTrees->data as $tree) {
                if (isset($tree->name) && $tree->name === $name) {
                    $existingId = $tree->{'.id'};
                    break;
                }
            }
        }

        $params = [
            'name' => $name,
            'parent' => $parent,
            'max-limit' => $maxLimit,
        ];

        if (isset($options['priority'])) $params['priority'] = $options['priority'];
        if (isset($options['comment'])) $params['comment'] = $options['comment'];

        if ($existingId) {
            // Update existing
            $result = $this->router->APIUpdateQueueTree($existingId, $params);
            $res->queue_tree_id = $existingId;
        } else {
            // Create new
            $result = $this->router->APICreateQueueTree($params);
            if ($result->success && isset($result->data->{'.id'})) {
                $res->queue_tree_id = $result->data->{'.id'};
            }
        }

        $res->success = $result->success;
        $res->message = $result->message ?? ($result->error ?? '');
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
            $departments = $this->model->getDepartments($this->routerId);

            foreach ($departments as $dept) {
                $whitelist = $this->model->getDeptWhitelist($dept['id']);
                $listName = 'muni-whitelist-' . sanitizeQueueName($dept['name']);

                foreach ($whitelist as $entry) {
                    // Add whitelisted domain's IPs to address list
                    // Note: We add the department's IP range to a whitelist address-list
                    // The firewall rule will match src-address-list + dst and ACCEPT before DNS redirect
                    $result = $this->router->APIAddFirewallAddress(
                        $dept['ip_range'],
                        $listName,
                        'Whitelist: ' . $entry['domain'] . ' for ' . $dept['name']
                    );

                    if ($result->success || (isset($result->message) && strpos($result->message, 'already exists') !== false)) {
                        $res->whitelisted++;
                    } else {
                        $res->errors[] = [
                            'type' => 'whitelist',
                            'dept' => $dept['name'],
                            'domain' => $entry['domain'],
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
     * Full sync: all user queues + QoS hierarchy + content filtering
     */
    public function syncAll(): object
    {
        $res = (object) [
            'success' => false,
            'results' => [
                'queues' => null,
                'qos' => null,
                'filtering' => null,
            ],
        ];

        if (!$this->connectRouter()) {
            $res->message = 'No se pudo conectar al router';
            return $res;
        }

        // 1. Sync all department queues (users)
        $departments = $this->model->getDepartments($this->routerId);
        $queueResults = (object) ['synced' => 0, 'errors' => []];

        foreach ($departments as $dept) {
            $deptResult = $this->syncDepartmentQueues($dept['id']);
            $queueResults->synced += $deptResult->synced;
            $queueResults->errors = array_merge($queueResults->errors, $deptResult->errors ?? []);
        }
        $res->results['queues'] = $queueResults;

        // 2. Sync QoS hierarchy
        $res->results['qos'] = $this->syncQoSHierarchy();

        // 3. Sync content filtering
        $res->results['filtering'] = $this->syncContentFiltering();

        $res->success = true;
        $res->message = sprintf(
            'Sync completo. Colas: %d, QoS deptos: %d, Dominios bloqueados: %d',
            $queueResults->synced,
            $res->results['qos']->synced ?? 0,
            $res->results['filtering']->blocked ?? 0
        );

        return $res;
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
}
