<?php

class ContentfilterModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    // Categories methods
    public function getCategories($active_only = true)
    {
        $where = $active_only ? "WHERE is_active = 1" : "";
        return $this->select("SELECT * FROM content_filter_categories $where ORDER BY name");
    }

    public function getCategoryById($id)
    {
        return $this->selectOne("SELECT * FROM content_filter_categories WHERE id = ?", [$id]);
    }

    public function createCategory($data)
    {
        return $this->insert("content_filter_categories", $data);
    }

    public function updateCategory($id, $data)
    {
        return $this->update("content_filter_categories", $data, "id = $id");
    }

    public function deleteCategory($id)
    {
        return $this->delete("content_filter_categories", "id = $id");
    }

    // Domains methods
    public function getDomainsByCategory($category_id)
    {
        return $this->select("SELECT * FROM content_filter_domains WHERE category_id = ? AND is_active = 1 ORDER BY domain", [$category_id]);
    }

    public function addDomainToCategory($category_id, $domain)
    {
        $data = [
            'category_id' => $category_id,
            'domain' => $domain,
            'is_active' => 1
        ];
        return $this->insert("content_filter_domains", $data);
    }

    public function removeDomainFromCategory($id)
    {
        return $this->delete("content_filter_domains", "id = $id");
    }

    // Policies methods
    public function getPolicies($active_only = true)
    {
        $where = $active_only ? "WHERE is_active = 1" : "";
        return $this->select("SELECT * FROM content_filter_policies $where ORDER BY name");
    }

    public function getPolicyById($id)
    {
        return $this->selectOne("SELECT * FROM content_filter_policies WHERE id = ?", [$id]);
    }

    public function createPolicy($data)
    {
        return $this->insert("content_filter_policies", $data);
    }

    public function updatePolicy($id, $data)
    {
        return $this->update("content_filter_policies", $data, "id = $id");
    }

    public function deletePolicy($id)
    {
        return $this->delete("content_filter_policies", "id = $id");
    }

    // Policy categories methods
    public function getPolicyCategories($policy_id)
    {
        $sql = "SELECT c.*, pc.action 
                FROM content_filter_policy_categories pc
                JOIN content_filter_categories c ON c.id = pc.category_id
                WHERE pc.policy_id = ? AND c.is_active = 1
                ORDER BY c.name";
        return $this->select($sql, [$policy_id]);
    }

    public function addCategoryToPolicy($policy_id, $category_id, $action = 'block')
    {
        $data = [
            'policy_id' => $policy_id,
            'category_id' => $category_id,
            'action' => $action
        ];
        return $this->insert("content_filter_policy_categories", $data);
    }

    public function removeCategoryFromPolicy($policy_id, $category_id)
    {
        return $this->delete("content_filter_policy_categories", "policy_id = $policy_id AND category_id = $category_id");
    }

    // Client policies methods
    public function getClientPolicies($client_id)
    {
        $sql = "SELECT cp.*, p.name as policy_name, p.description as policy_description,
                       r.name as router_name, r.ip as router_ip
                FROM content_filter_client_policies cp
                JOIN content_filter_policies p ON p.id = cp.policy_id
                JOIN network_routers r ON r.id = cp.router_id
                WHERE cp.client_id = ? AND cp.is_active = 1
                ORDER BY cp.created_at DESC";
        return $this->select($sql, [$client_id]);
    }

    public function getClientPolicy($client_id, $router_id)
    {
        $sql = "SELECT cp.*, p.name as policy_name, p.description as policy_description
                FROM content_filter_client_policies cp
                JOIN content_filter_policies p ON p.id = cp.policy_id
                WHERE cp.client_id = ? AND cp.router_id = ? AND cp.is_active = 1";
        return $this->selectOne($sql, [$client_id, $router_id]);
    }

    public function assignPolicyToClient($client_id, $policy_id, $router_id)
    {
        // First deactivate any existing policy for this client/router
        $this->update("content_filter_client_policies", 
                     ['is_active' => 0], 
                     "client_id = $client_id AND router_id = $router_id");
        
        // Insert new policy assignment
        $data = [
            'client_id' => $client_id,
            'policy_id' => $policy_id,
            'router_id' => $router_id,
            'is_active' => 1,
            'applied_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert("content_filter_client_policies", $data);
    }

    public function removePolicyFromClient($client_id, $router_id)
    {
        return $this->update("content_filter_client_policies", 
                           ['is_active' => 0], 
                           "client_id = $client_id AND router_id = $router_id");
    }

    // Custom domains methods
    public function getClientCustomDomains($client_id)
    {
        return $this->select("SELECT * FROM content_filter_custom_domains WHERE client_id = ? AND is_active = 1 ORDER BY domain", [$client_id]);
    }

    public function addCustomDomain($client_id, $domain, $action = 'block', $comment = '')
    {
        $data = [
            'client_id' => $client_id,
            'domain' => $domain,
            'action' => $action,
            'comment' => $comment,
            'is_active' => 1
        ];
        return $this->insert("content_filter_custom_domains", $data);
    }

    public function removeCustomDomain($id)
    {
        return $this->delete("content_filter_custom_domains", "id = $id");
    }

    // Logs methods
    public function getFilteringLogs($client_id = null, $limit = 100)
    {
        $where = "";
        $params = [];
        
        if ($client_id) {
            $where = "WHERE l.client_id = ?";
            $params[] = $client_id;
        }
        
        $sql = "SELECT l.*, c.name as client_name, r.name as router_name, p.name as policy_name
                FROM content_filter_logs l
                JOIN clients c ON c.id = l.client_id
                JOIN network_routers r ON r.id = l.router_id
                LEFT JOIN content_filter_policies p ON p.id = l.policy_id
                $where
                ORDER BY l.created_at DESC
                LIMIT ?";
        
        $params[] = $limit;
        return $this->select($sql, $params);
    }

    public function addLog($data)
    {
        return $this->insert("content_filter_logs", $data);
    }

    // Statistics methods
    public function getFilteringStats()
    {
        $stats = [];
        
        // Total policies
        $stats['total_policies'] = $this->selectOne("SELECT COUNT(*) as count FROM content_filter_policies WHERE is_active = 1")['count'];
        
        // Total active clients with filtering
        $stats['filtered_clients'] = $this->selectOne("SELECT COUNT(DISTINCT client_id) as count FROM content_filter_client_policies WHERE is_active = 1")['count'];
        
        // Total categories
        $stats['total_categories'] = $this->selectOne("SELECT COUNT(*) as count FROM content_filter_categories WHERE is_active = 1")['count'];
        
        // Total blocked domains
        $stats['blocked_domains'] = $this->selectOne("SELECT COUNT(*) as count FROM content_filter_domains WHERE is_active = 1")['count'];
        
        // Recent logs count (last 24 hours)
        $stats['recent_activities'] = $this->selectOne("SELECT COUNT(*) as count FROM content_filter_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")['count'];
        
        return $stats;
    }

    // Helper methods
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
                ORDER BY c.name";
        
        return $this->select($sql);
    }

    public function getDefaultPolicy()
    {
        return $this->selectOne("SELECT * FROM content_filter_policies WHERE is_default = 1 AND is_active = 1");
    }
}