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
        $departmentSummaries = [];

        foreach ($activeUsers as $user) {
            $departmentKey = $this->departmentKey($user);
            $this->ensureDepartmentSummary($departmentSummaries, $departmentKey, $user);
            $hasService = $this->hasServiceAssignment($user);
            if ($hasService) {
                $assignedService++;
            } else {
                $this->addDepartmentReason($departmentSummaries, $departmentKey, 'service_incomplete');
            }

            if (($user['queue_sync_status'] ?? '') === 'synced') {
                $syncedQueues++;
            } else {
                $this->addDepartmentReason($departmentSummaries, $departmentKey, 'queue_not_synced');
            }

            $ipStatus = $this->resolveIpCompliance($user['ip_address'] ?? '', $user['ip_range'] ?? '');
            if ($ipStatus === 'compliant') {
                $ipEvaluable++;
                $ipCompliant++;
            } elseif ($ipStatus === 'mismatch') {
                $ipEvaluable++;
                $this->addDepartmentReason($departmentSummaries, $departmentKey, 'ip_out_of_range');
            } else {
                $insufficientIpEvidence++;
                $this->addDepartmentReason($departmentSummaries, $departmentKey, 'insufficient_ip_evidence');
            }
        }

        $ipPercent = $this->percentage($ipCompliant, $ipEvaluable);
        $queuePercent = $this->percentage($syncedQueues, $activeCount);
        $departments = $this->finalizeDepartmentSummaries($departmentSummaries);
        $departmentsAttentionCount = $this->countDepartmentsRequiringAttention($departments);

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
                    'value' => $departmentsAttentionCount,
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
            'departments' => $departments,
            'messages' => [],
            'metadata' => [
                'active_users' => $activeCount,
                'ip_evaluable_users' => $ipEvaluable,
                'insufficient_ip_evidence' => $insufficientIpEvidence,
                'insufficient_department_evidence' => count(array_filter(
                    $departments,
                    fn($department) => ($department['status'] ?? '') === 'Sin información suficiente'
                )),
                'uses_generated_history' => false,
            ],
        ];
    }

    public function mergeManagementKpisWithBandwidth(array $payload, array $queues): array
    {
        $observed = 0;
        $hasCurrentRateEvidence = false;

        foreach ($queues as $queue) {
            $disabled = filter_var($queue['disabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $rates = $this->extractCurrentRates($queue);

            if ($rates !== null) {
                $hasCurrentRateEvidence = true;
            }

            if (!$disabled && $rates !== null && ($rates['download'] > 0 || $rates['upload'] > 0)) {
                $observed++;
                $this->addCurrentConsumptionDepartmentEvidence($payload, $queue);
            }
        }

        $payload['source']['router'] = 'available';
        $payload['kpis']['observed_consumption']['evidence'] = 'current_only';

        if ($hasCurrentRateEvidence) {
            $payload['kpis']['observed_consumption']['value'] = $observed;
            $payload['metadata']['observed_consumption_queues'] = $observed;
            unset($payload['metadata']['observed_consumption_unavailable_reason']);
        } else {
            $payload['kpis']['observed_consumption']['value'] = 'Sin información suficiente';
            $payload['metadata']['observed_consumption_queues'] = null;
            $payload['metadata']['observed_consumption_unavailable_reason'] = 'current_rate_unavailable';
        }

        $payload['metadata']['uses_generated_history'] = false;
        $payload['departments'] = $this->finalizeDepartmentSummaries($payload['departments'] ?? []);
        $payload['kpis']['departments_attention']['value'] = $this->countDepartmentsRequiringAttention($payload['departments']);

        return $payload;
    }

    private function ensureDepartmentSummary(array &$departments, string $departmentKey, array $user): void
    {
        if (isset($departments[$departmentKey])) {
            $departments[$departmentKey]['total_users']++;
            return;
        }

        $departments[$departmentKey] = [
            'key' => $departmentKey,
            'id' => isset($user['department_id']) ? (int) $user['department_id'] : null,
            'name' => !empty($user['department_name']) ? $user['department_name'] : 'Sin área asignada',
            'status' => 'Sin observaciones',
            'attention_score' => 0,
            'insufficient_evidence_count' => 0,
            'total_users' => 1,
            'reasons' => [],
        ];
    }

    private function addDepartmentReason(array &$departments, string $departmentKey, string $code): void
    {
        if (!isset($departments[$departmentKey])) {
            $departments[$departmentKey] = [
                'key' => $departmentKey,
                'id' => null,
                'name' => 'Sin área asignada',
                'status' => 'Sin observaciones',
                'attention_score' => 0,
                'insufficient_evidence_count' => 0,
                'total_users' => 0,
                'reasons' => [],
            ];
        }

        $this->addReasonToDepartmentSummary($departments[$departmentKey], $code);
    }

    private function addReasonToDepartmentSummary(array &$department, string $code): void
    {
        $definition = $this->departmentReasonDefinitions()[$code];

        foreach ($department['reasons'] as &$reason) {
            if (($reason['code'] ?? '') === $code) {
                $reason['affected_users']++;
                break;
            }
        }
        unset($reason);

        if (!in_array($code, array_column($department['reasons'], 'code'), true)) {
            $department['reasons'][] = [
                'code' => $code,
                'label' => $definition['label'],
                'evidence' => $definition['evidence'],
                'severity' => $definition['severity'],
                'affected_users' => 1,
                'copy' => $definition['copy'],
            ];
        }

        if ($definition['severity'] === 'attention') {
            $department['attention_score']++;
            return;
        }

        if ($definition['severity'] === 'insufficient') {
            $department['insufficient_evidence_count']++;
        }
    }

    private function departmentReasonDefinitions(): array
    {
        return [
            'service_incomplete' => ['label' => 'Servicio asignado incompleto', 'evidence' => 'catalog', 'severity' => 'attention', 'copy' => 'Revisar datos de servicio asignado antes de tomar una decisión administrativa.'],
            'ip_out_of_range' => ['label' => 'IP fuera del rango registrado', 'evidence' => 'catalog', 'severity' => 'attention', 'copy' => 'Revisar consistencia entre la IP asignada y el rango del área.'],
            'queue_not_synced' => ['label' => 'Configuración pendiente de aplicar', 'evidence' => 'catalog', 'severity' => 'attention', 'copy' => 'Revisar sincronización de la configuración técnica con el router.'],
            'current_consumption_observed' => ['label' => 'Consumo actual en observación', 'evidence' => 'current_only', 'severity' => 'attention', 'copy' => 'Lectura momentánea del router; requiere revisión operativa, no prueba comportamiento previo.'],
            'insufficient_ip_evidence' => ['label' => 'Evidencia de IP insuficiente', 'evidence' => 'catalog', 'severity' => 'insufficient', 'copy' => 'Sin información suficiente para confirmar la consistencia de IP; requiere revisión de catálogo.'],
        ];
    }

    private function finalizeDepartmentSummaries(array $departments): array
    {
        $finalized = [];

        foreach (array_values($departments) as $department) {
            if (empty($department['reasons'])) {
                continue;
            }

            if (($department['attention_score'] ?? 0) > 0) {
                $department['status'] = 'Requiere revisión';
            } elseif (($department['insufficient_evidence_count'] ?? 0) > 0) {
                $department['status'] = 'Sin información suficiente';
            } else {
                $department['status'] = 'Sin observaciones';
            }

            $department['reasons'] = $this->sortDepartmentReasons($department['reasons'] ?? []);

            $finalized[] = $department;
        }

        usort($finalized, function (array $a, array $b): int {
            $score = ($b['attention_score'] ?? 0) <=> ($a['attention_score'] ?? 0);
            if ($score !== 0) {
                return $score;
            }

            $insufficient = ($b['insufficient_evidence_count'] ?? 0) <=> ($a['insufficient_evidence_count'] ?? 0);
            if ($insufficient !== 0) {
                return $insufficient;
            }

            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        });

        return $finalized;
    }

    private function sortDepartmentReasons(array $reasons): array
    {
        $priority = [
            'service_incomplete' => 10,
            'ip_out_of_range' => 20,
            'queue_not_synced' => 30,
            'current_consumption_observed' => 40,
            'insufficient_ip_evidence' => 90,
        ];

        usort($reasons, function (array $a, array $b) use ($priority): int {
            return ($priority[$a['code'] ?? ''] ?? 100) <=> ($priority[$b['code'] ?? ''] ?? 100);
        });

        return $reasons;
    }

    private function countDepartmentsRequiringAttention(array $departments): int
    {
        return count(array_filter(
            $departments,
            fn($department) => ($department['attention_score'] ?? 0) > 0
        ));
    }

    private function addCurrentConsumptionDepartmentEvidence(array &$payload, array $queue): void
    {
        $departmentName = $queue['department'] ?? 'Sin área asignada';
        $departmentIndex = null;

        foreach ($payload['departments'] ?? [] as $index => $department) {
            if (strcasecmp($department['name'] ?? '', $departmentName) === 0) {
                $departmentIndex = $index;
                break;
            }
        }

        if ($departmentIndex === null) {
            $payload['departments'][] = [
                'key' => 'queue-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($departmentName)),
                'id' => null,
                'name' => $departmentName,
                'status' => 'Sin observaciones',
                'attention_score' => 0,
                'insufficient_evidence_count' => 0,
                'total_users' => 0,
                'reasons' => [],
            ];
            $departmentIndex = array_key_last($payload['departments']);
        }

        $this->addReasonToDepartmentSummary($payload['departments'][$departmentIndex], 'current_consumption_observed');
    }

    private function extractCurrentRates(array $queue): ?array
    {
        if (array_key_exists('download_rate', $queue) || array_key_exists('upload_rate', $queue)) {
            return [
                'download' => intval($queue['download_rate'] ?? 0),
                'upload' => intval($queue['upload_rate'] ?? 0),
            ];
        }

        if (array_key_exists('rate', $queue)) {
            $parts = explode('/', (string) $queue['rate']);

            return [
                'upload' => intval($parts[0] ?? 0),
                'download' => intval($parts[1] ?? 0),
            ];
        }

        return null;
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
