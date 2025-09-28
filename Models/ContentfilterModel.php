<?php

class ContentfilterModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    // Statistics methods - only what's needed for the controller
    public function getFilteringStats()
    {
        $stats = [];
        
        // Total policies
        $result1 = sql("SELECT COUNT(*) as count FROM content_filter_policies WHERE is_active = 1");
        $stats['total_policies'] = mysqli_fetch_array($result1)['count'];
        
        // Total active clients with filtering
        $result2 = sql("SELECT COUNT(DISTINCT client_id) as count FROM content_filter_client_policies WHERE is_active = 1");
        $stats['filtered_clients'] = mysqli_fetch_array($result2)['count'];
        
        // Total categories
        $result3 = sql("SELECT COUNT(*) as count FROM content_filter_categories WHERE is_active = 1");
        $stats['total_categories'] = mysqli_fetch_array($result3)['count'];
        
        // Total blocked domains
        $result4 = sql("SELECT COUNT(*) as count FROM content_filter_domains WHERE is_active = 1");
        $stats['blocked_domains'] = mysqli_fetch_array($result4)['count'];
        
        // Recent logs count (last 24 hours)
        $result5 = sql("SELECT COUNT(*) as count FROM content_filter_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['recent_activities'] = mysqli_fetch_array($result5)['count'];
        
        return $stats;
    }

    // Get clients without filtering
    public function getClientsWithoutFiltering($router_id = null)
    {
        $where = "WHERE c.id NOT IN (SELECT client_id FROM content_filter_client_policies WHERE is_active = 1)";
        if ($router_id) {
            $where .= " AND c.net_router = $router_id";
        }
        
        $sql = "SELECT c.*, r.name as router_name 
                FROM clients c 
                JOIN network_routers r ON r.id = c.net_router 
                $where 
                AND c.net_ip IS NOT NULL 
                ORDER BY c.names";
        
        $result = sql($sql);
        $clients = [];
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $clients[] = $row;
        }
        return $clients;
    }

    // Get client's current filtering policy
    public function getClientPolicy($client_id, $router_id)
    {
        $sql = "SELECT cp.*, p.name as policy_name, p.description as policy_description
                FROM content_filter_client_policies cp
                JOIN content_filter_policies p ON p.id = cp.policy_id
                WHERE cp.client_id = $client_id AND cp.router_id = $router_id AND cp.is_active = 1";
        
        return sqlObject($sql);
    }

    // Get categories for UI
    public function getCategories($active_only = true)
    {
        $where = $active_only ? "WHERE is_active = 1" : "";
        $result = sql("SELECT * FROM content_filter_categories $where ORDER BY name");
        $categories = [];
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }
}