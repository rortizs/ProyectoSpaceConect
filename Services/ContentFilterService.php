<?php

class ContentFilterService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all content filter categories
     */
    public function getCategories($active_only = true)
    {
        $where = $active_only ? "WHERE is_active = 1" : "";
        $sql = "SELECT * FROM content_filter_categories $where ORDER BY name";
        
        $result = sql($sql);
        $categories = [];
        
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    /**
     * Get domains for specific categories
     */
    public function getDomainsByCategories($category_ids)
    {
        if (empty($category_ids)) {
            return [];
        }
        
        $ids = implode(',', array_map('intval', $category_ids));
        $sql = "SELECT d.domain, c.name as category_name 
                FROM content_filter_domains d 
                JOIN content_filter_categories c ON c.id = d.category_id 
                WHERE d.category_id IN ($ids) AND d.is_active = 1 AND c.is_active = 1
                ORDER BY c.name, d.domain";
        
        $result = sql($sql);
        $domains = [];
        
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $domains[] = $row['domain'];
        }
        
        return $domains;
    }

    /**
     * Get all filtering policies
     */
    public function getPolicies($active_only = true)
    {
        $where = $active_only ? "WHERE is_active = 1" : "";
        $sql = "SELECT * FROM content_filter_policies $where ORDER BY name";
        
        $result = sql($sql);
        $policies = [];
        
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            // Get associated categories
            $row['categories'] = $this->getPolicyCategories($row['id']);
            $policies[] = $row;
        }
        
        return $policies;
    }

    /**
     * Get categories for a specific policy
     */
    public function getPolicyCategories($policy_id)
    {
        $sql = "SELECT c.*, pc.action 
                FROM content_filter_policy_categories pc
                JOIN content_filter_categories c ON c.id = pc.category_id
                WHERE pc.policy_id = ? AND c.is_active = 1
                ORDER BY c.name";
        
        $result = sql($sql, [$policy_id]);
        $categories = [];
        
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    /**
     * Apply content filtering policy to a client
     */
    public function applyPolicyToClient($client_id, $policy_id, $router_id)
    {
        try {
            // Get client information
            $client = sqlObject("SELECT * FROM clients WHERE id = ?", [$client_id]);
            if (!$client) {
                throw new Exception("Cliente no encontrado");
            }

            // Get router information
            $router_info = sqlObject("SELECT * FROM network_routers WHERE id = ?", [$router_id]);
            if (!$router_info) {
                throw new Exception("Router no encontrado");
            }

            // Get policy categories
            $policy_categories = $this->getPolicyCategories($policy_id);
            $blocked_category_ids = [];
            
            foreach ($policy_categories as $cat) {
                if ($cat['action'] === 'block') {
                    $blocked_category_ids[] = $cat['id'];
                }
            }

            // Get domains to block
            $blocked_domains = $this->getDomainsByCategories($blocked_category_ids);
            
            if (empty($blocked_domains)) {
                throw new Exception("No hay dominios para bloquear en esta política");
            }

            // Connect to router and apply filtering
            include_once 'Libraries/MikroTik/Router.php';
            $router = new Router(
                $router_info['ip'], 
                $router_info['port'], 
                $router_info['username'], 
                decrypt_aes($router_info['password'], SECRET_IV)
            );

            if (!$router->connected) {
                throw new Exception("No se pudo conectar al router");
            }

            // Apply content filter
            $results = $router->APIApplyContentFilter($client['net_ip'], $blocked_domains);

            // Save client policy assignment
            $this->saveClientPolicy($client_id, $policy_id, $router_id);

            // Log the action
            $this->logFilteringAction($client_id, $router_id, 'apply', $policy_id, $results);

            return [
                'success' => true,
                'message' => 'Política de filtrado aplicada correctamente',
                'domains_blocked' => count($blocked_domains),
                'results' => $results
            ];

        } catch (Exception $e) {
            // Log error
            $this->logFilteringAction($client_id, $router_id, 'apply', $policy_id, null, 'error', $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove content filtering from a client
     */
    public function removePolicyFromClient($client_id, $router_id)
    {
        try {
            // Get client information
            $client = sqlObject("SELECT * FROM clients WHERE id = ?", [$client_id]);
            if (!$client) {
                throw new Exception("Cliente no encontrado");
            }

            // Get current policy assignment
            $client_policy = sqlObject("SELECT * FROM content_filter_client_policies WHERE client_id = ? AND router_id = ? AND is_active = 1", [$client_id, $router_id]);
            if (!$client_policy) {
                throw new Exception("No hay política activa para este cliente");
            }

            // Get router information
            $router_info = sqlObject("SELECT * FROM network_routers WHERE id = ?", [$router_id]);
            if (!$router_info) {
                throw new Exception("Router no encontrado");
            }

            // Get blocked domains from current policy
            $policy_categories = $this->getPolicyCategories($client_policy['policy_id']);
            $blocked_category_ids = [];
            
            foreach ($policy_categories as $cat) {
                if ($cat['action'] === 'block') {
                    $blocked_category_ids[] = $cat['id'];
                }
            }

            $blocked_domains = $this->getDomainsByCategories($blocked_category_ids);

            // Connect to router and remove filtering
            include_once 'Libraries/MikroTik/Router.php';
            $router = new Router(
                $router_info['ip'], 
                $router_info['port'], 
                $router_info['username'], 
                decrypt_aes($router_info['password'], SECRET_IV)
            );

            if (!$router->connected) {
                throw new Exception("No se pudo conectar al router");
            }

            // Remove content filter
            $results = $router->APIRemoveContentFilter($client['net_ip'], $blocked_domains);

            // Deactivate client policy assignment
            sql("UPDATE content_filter_client_policies SET is_active = 0 WHERE id = ?", [$client_policy['id']]);

            // Log the action
            $this->logFilteringAction($client_id, $router_id, 'remove', $client_policy['policy_id'], $results);

            return [
                'success' => true,
                'message' => 'Política de filtrado removida correctamente',
                'domains_unblocked' => count($blocked_domains),
                'results' => $results
            ];

        } catch (Exception $e) {
            // Log error
            $this->logFilteringAction($client_id, $router_id, 'remove', null, null, 'error', $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get client's current filtering policy
     */
    public function getClientPolicy($client_id, $router_id)
    {
        $sql = "SELECT cp.*, p.name as policy_name, p.description as policy_description
                FROM content_filter_client_policies cp
                JOIN content_filter_policies p ON p.id = cp.policy_id
                WHERE cp.client_id = ? AND cp.router_id = ? AND cp.is_active = 1";
        
        return sqlObject($sql, [$client_id, $router_id]);
    }

    /**
     * Save client policy assignment
     */
    private function saveClientPolicy($client_id, $policy_id, $router_id)
    {
        // First deactivate any existing policy for this client/router
        sql("UPDATE content_filter_client_policies SET is_active = 0 WHERE client_id = ? AND router_id = ?", [$client_id, $router_id]);
        
        // Insert new policy assignment
        $data = [
            'client_id' => $client_id,
            'policy_id' => $policy_id,
            'router_id' => $router_id,
            'is_active' => 1,
            'applied_at' => date('Y-m-d H:i:s')
        ];
        
        sqlInsert('content_filter_client_policies', (object)$data);
    }

    /**
     * Log filtering actions
     */
    private function logFilteringAction($client_id, $router_id, $action, $policy_id = null, $details = null, $status = 'success', $error_message = null)
    {
        $data = [
            'client_id' => $client_id,
            'router_id' => $router_id,
            'action' => $action,
            'policy_id' => $policy_id,
            'details' => $details ? json_encode($details) : null,
            'status' => $status,
            'error_message' => $error_message
        ];
        
        sqlInsert('content_filter_logs', (object)$data);
    }

    /**
     * Get filtering logs
     */
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
        $result = sql($sql, $params);
        $logs = [];
        
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $logs[] = $row;
        }
        
        return $logs;
    }

    /**
     * Create new content filter policy
     */
    public function createPolicy($name, $description, $category_ids, $is_default = false)
    {
        try {
            // Check if policy name already exists
            $existing = sqlObject("SELECT id FROM content_filter_policies WHERE name = ?", [$name]);
            if ($existing) {
                throw new Exception("Ya existe una política con ese nombre");
            }

            // If this is set as default, remove default flag from other policies
            if ($is_default) {
                sql("UPDATE content_filter_policies SET is_default = 0");
            }

            // Create policy
            $policy_data = [
                'name' => $name,
                'description' => $description,
                'is_default' => $is_default ? 1 : 0,
                'is_active' => 1
            ];
            
            $policy_id = sqlInsert('content_filter_policies', (object)$policy_data);

            // Add categories to policy
            foreach ($category_ids as $category_id) {
                $category_data = [
                    'policy_id' => $policy_id,
                    'category_id' => $category_id,
                    'action' => 'block'
                ];
                sqlInsert('content_filter_policy_categories', (object)$category_data);
            }

            return [
                'success' => true,
                'message' => 'Política creada correctamente',
                'policy_id' => $policy_id
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}