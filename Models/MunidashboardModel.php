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
}
