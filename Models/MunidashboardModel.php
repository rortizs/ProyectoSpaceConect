<?php

class MunidashboardModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getStats($router_id = null)
    {
        $stats = [];

        $where_router = $router_id ? "AND mu.router_id = " . intval($router_id) : "";

        // Total active users
        $r1 = $this->select("SELECT COUNT(*) AS cnt FROM muni_users mu WHERE mu.status = 1 $where_router");
        $stats['active_users'] = intval($r1['cnt'] ?? 0);

        // Disabled/inactive users
        $r2 = $this->select("SELECT COUNT(*) AS cnt FROM muni_users mu WHERE mu.status = 0 $where_router");
        $stats['disabled_users'] = intval($r2['cnt'] ?? 0);

        // Total blocked domains (global)
        $r3 = $this->select("SELECT COUNT(*) AS cnt FROM content_filter_domains WHERE is_active = 1");
        $stats['blocked_domains'] = intval($r3['cnt'] ?? 0);

        return $stats;
    }

    public function getAlertsRecent(int $limit = 10)
    {
        $sql = "SELECT mal.*, u.names AS user_name
                FROM muni_audit_log mal
                LEFT JOIN users u ON mal.user_id = u.id
                ORDER BY mal.created_at DESC
                LIMIT $limit";

        return $this->select_all($sql);
    }

    public function getManagementKpiSummary(?int $router_id = null): array
    {
        return $this->buildManagementKpiPayload($this->fetchManagementUsers($router_id), $router_id);
    }

    public function buildManagementKpiPayload(array $users, ?int $router_id = null): array
    {
        $activeUsers = array_values(array_filter($users, fn($user) => intval($user['status'] ?? 0) === 1));
        $activeCount = count($activeUsers);

        $assignedService = 0;
        $ipEvaluable = 0;
        $ipCompliant = 0;
        $insufficientIpEvidence = 0;
        $syncedQueues = 0;
        $departmentsAttention = [];

        foreach ($activeUsers as $user) {
            $departmentKey = $this->departmentKey($user);
            $hasService = $this->hasServiceAssignment($user);
            if ($hasService) {
                $assignedService++;
            } else {
                $departmentsAttention[$departmentKey] = true;
            }

            if (($user['queue_sync_status'] ?? '') === 'synced') {
                $syncedQueues++;
            } else {
                $departmentsAttention[$departmentKey] = true;
            }

            $ipStatus = $this->resolveIpCompliance($user['ip_address'] ?? '', $user['ip_range'] ?? '');
            if ($ipStatus === 'compliant') {
                $ipEvaluable++;
                $ipCompliant++;
            } elseif ($ipStatus === 'mismatch') {
                $ipEvaluable++;
                $departmentsAttention[$departmentKey] = true;
            } else {
                $insufficientIpEvidence++;
            }
        }

        $ipPercent = $this->percentage($ipCompliant, $ipEvaluable);
        $queuePercent = $this->percentage($syncedQueues, $activeCount);

        return [
            'router_id' => $router_id,
            'source' => [
                'catalog' => 'available',
                'router' => 'unavailable',
            ],
            'kpis' => [
                'assigned_service' => [
                    'value' => $assignedService,
                    'label' => 'Personal con servicio asignado',
                    'evidence' => 'catalog',
                ],
                'observed_consumption' => [
                    'value' => 'Sin información suficiente',
                    'label' => 'Usuarios con consumo en observación',
                    'evidence' => 'current_only',
                ],
                'departments_attention' => [
                    'value' => count($departmentsAttention),
                    'label' => 'Áreas que requieren atención',
                    'evidence' => 'catalog',
                ],
                'ip_compliance' => [
                    'value' => $ipPercent === null ? 'Sin información suficiente' : $ipPercent . '%',
                    'percent' => $ipPercent,
                    'label' => 'Cumplimiento de IP asignada',
                    'evidence' => 'catalog',
                ],
                'queue_sync_compliance' => [
                    'value' => $queuePercent === null ? 'Sin información suficiente' : $queuePercent . '%',
                    'percent' => $queuePercent,
                    'label' => 'Configuración aplicada correctamente',
                    'evidence' => 'catalog',
                ],
            ],
            'departments' => [],
            'messages' => [],
            'metadata' => [
                'active_users' => $activeCount,
                'ip_evaluable_users' => $ipEvaluable,
                'insufficient_ip_evidence' => $insufficientIpEvidence,
                'uses_generated_history' => false,
            ],
        ];
    }

    protected function fetchManagementUsers(?int $router_id): array
    {
        $sql = "SELECT mu.id, mu.router_id, mu.department_id, mu.name, mu.ip_address,
                       mu.custom_upload, mu.custom_download, mu.queue_name,
                       mu.queue_sync_status, mu.status,
                       md.name AS department_name, md.ip_range
                FROM muni_users mu
                LEFT JOIN muni_departments md ON md.id = mu.department_id
                WHERE (? IS NULL OR mu.router_id = ?)
                ORDER BY md.name ASC, mu.name ASC";

        return $this->selectAllPrepared($sql, [$router_id, $router_id]);
    }

    protected function selectAllPrepared(string $sql, array $params = []): array
    {
        $connection = (new Conexion())->conect();
        if (!$connection instanceof PDO) {
            return [];
        }

        $statement = $connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hasServiceAssignment(array $user): bool
    {
        return !empty($user['ip_address'])
            && !empty($user['custom_upload'])
            && !empty($user['custom_download'])
            && !empty($user['queue_name']);
    }

    private function resolveIpCompliance(string $ip, string $range): string
    {
        if (empty($ip) || empty($range) || strpos($range, '-') === false) {
            return 'insufficient';
        }

        [$start, $end] = array_map('trim', explode('-', $range, 2));
        $ipLong = ip2long($ip);
        $startLong = ip2long($start);
        $endLong = ip2long($end);

        if ($ipLong === false || $startLong === false || $endLong === false || $startLong > $endLong) {
            return 'insufficient';
        }

        return ($ipLong >= $startLong && $ipLong <= $endLong) ? 'compliant' : 'mismatch';
    }

    private function percentage(int $numerator, int $denominator): ?int
    {
        if ($denominator === 0) {
            return null;
        }

        return (int) round(($numerator / $denominator) * 100);
    }

    private function departmentKey(array $user): string
    {
        if (!empty($user['department_id'])) {
            return 'dept-' . intval($user['department_id']);
        }

        return 'user-' . intval($user['id'] ?? 0);
    }
}
