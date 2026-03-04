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

        // Total active users
        $where_router = $router_id ? "AND mu.router_id = " . intval($router_id) : "";
        $r1 = $this->select("SELECT COUNT(*) AS cnt FROM muni_users mu WHERE mu.status = 1 $where_router");
        $stats['active_users'] = intval($r1['cnt'] ?? 0);

        // Total departments
        $where_router_dept = $router_id ? "WHERE d.router_id = " . intval($router_id) : "";
        $r2 = $this->select("SELECT COUNT(*) AS cnt FROM muni_departments d $where_router_dept");
        $stats['total_departments'] = intval($r2['cnt'] ?? 0);

        // Users with sync errors
        $r3 = $this->select("SELECT COUNT(*) AS cnt FROM muni_users mu WHERE mu.queue_sync_status = 'error' $where_router");
        $stats['sync_errors'] = intval($r3['cnt'] ?? 0);

        // Users pending sync
        $r4 = $this->select("SELECT COUNT(*) AS cnt FROM muni_users mu WHERE mu.queue_sync_status = 'pending' $where_router");
        $stats['pending_sync'] = intval($r4['cnt'] ?? 0);

        // Departments with QoS errors
        $r5 = $this->select("SELECT COUNT(*) AS cnt FROM muni_departments d WHERE d.qos_sync_status = 'error' " . ($router_id ? "AND d.router_id = " . intval($router_id) : ""));
        $stats['qos_errors'] = intval($r5['cnt'] ?? 0);

        // Total blocked domains (global)
        $r6 = $this->select("SELECT COUNT(*) AS cnt FROM content_filter_domains WHERE is_active = 1");
        $stats['blocked_domains'] = intval($r6['cnt'] ?? 0);

        // Total filter categories
        $r7 = $this->select("SELECT COUNT(*) AS cnt FROM content_filter_categories WHERE is_active = 1");
        $stats['total_categories'] = intval($r7['cnt'] ?? 0);

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

    public function getDepartmentSummary($router_id = null)
    {
        $where = $router_id ? "WHERE d.router_id = " . intval($router_id) : "";

        $sql = "SELECT d.id, d.name, d.priority, d.ip_range,
                    d.default_upload, d.default_download,
                    d.qos_max_limit, d.qos_sync_status, d.status,
                    (SELECT COUNT(*) FROM muni_users mu WHERE mu.department_id = d.id AND mu.status = 1) AS active_users,
                    (SELECT COUNT(*) FROM muni_users mu WHERE mu.department_id = d.id AND mu.queue_sync_status = 'error') AS error_users
                FROM muni_departments d
                $where
                ORDER BY d.priority ASC";

        return $this->select_all($sql);
    }
}
