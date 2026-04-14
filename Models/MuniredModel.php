<?php

class MuniredModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    // =============================================
    // DEPARTMENT CRUD
    // =============================================

    public function getDepartments($router_id = null)
    {
        $where = "";
        if ($router_id) {
            $router_id = intval($router_id);
            $where = "WHERE d.router_id = $router_id";
        }

        $sql = "SELECT d.*,
                    nr.name AS router_name,
                    nr.ip AS router_ip,
                    (SELECT COUNT(*) FROM muni_users mu WHERE mu.department_id = d.id AND mu.status = 1) AS user_count,
                    (SELECT COUNT(*) FROM muni_users mu WHERE mu.department_id = d.id) AS total_users
                FROM muni_departments d
                LEFT JOIN network_routers nr ON d.router_id = nr.id
                $where
                ORDER BY d.priority ASC, d.name ASC";

        return $this->select_all($sql);
    }

    public function getDepartment(int $id)
    {
        $sql = "SELECT d.*,
                    nr.name AS router_name,
                    nr.ip AS router_ip,
                    nr.ip_range AS router_ip_range
                FROM muni_departments d
                LEFT JOIN network_routers nr ON d.router_id = nr.id
                WHERE d.id = $id";

        return $this->select($sql);
    }

    public function createDepartment(array $data)
    {
        // Check duplicate name
        $check = $this->select("SELECT id FROM muni_departments WHERE name = '{$data['name']}'");
        if (!empty($check)) {
            return 'exists';
        }

        $query = "INSERT INTO muni_departments (router_id, name, description, ip_range, priority, default_upload, default_download, burst_upload, burst_download, burst_threshold_up, burst_threshold_down, burst_time, qos_max_limit)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $values = [
            intval($data['router_id']),
            $data['name'],
            $data['description'] ?? null,
            $data['ip_range'],
            intval($data['priority'] ?? 4),
            $data['default_upload'] ?? '5M',
            $data['default_download'] ?? '10M',
            $data['burst_upload'] ?? null,
            $data['burst_download'] ?? null,
            $data['burst_threshold_up'] ?? null,
            $data['burst_threshold_down'] ?? null,
            $data['burst_time'] ?? null,
            $data['qos_max_limit'] ?? null,
        ];

        $result = $this->insert($query, $values);
        return $result ? 'success' : 'error';
    }

    public function updateDepartment(int $id, array $data)
    {
        // Check duplicate name (excluding self)
        $check = $this->select("SELECT id FROM muni_departments WHERE name = '{$data['name']}' AND id != $id");
        if (!empty($check)) {
            return 'exists';
        }

        $query = "UPDATE muni_departments SET
                    router_id = ?, name = ?, description = ?, ip_range = ?,
                    priority = ?, default_upload = ?, default_download = ?,
                    burst_upload = ?, burst_download = ?,
                    burst_threshold_up = ?, burst_threshold_down = ?,
                    burst_time = ?, qos_max_limit = ?, qos_sync_status = 'pending'
                  WHERE id = $id";

        $values = [
            intval($data['router_id']),
            $data['name'],
            $data['description'] ?? null,
            $data['ip_range'],
            intval($data['priority'] ?? 4),
            $data['default_upload'] ?? '5M',
            $data['default_download'] ?? '10M',
            $data['burst_upload'] ?? null,
            $data['burst_download'] ?? null,
            $data['burst_threshold_up'] ?? null,
            $data['burst_threshold_down'] ?? null,
            $data['burst_time'] ?? null,
            $data['qos_max_limit'] ?? null,
        ];

        $result = $this->update($query, $values);
        return $result ? 'success' : 'error';
    }

    public function deleteDepartment(int $id)
    {
        // Guard: check if department has users
        $count = $this->select("SELECT COUNT(*) AS cnt FROM muni_users WHERE department_id = $id");
        if (!empty($count) && intval($count['cnt']) > 0) {
            return 'exists';
        }

        $this->delete("DELETE FROM muni_departments WHERE id = $id");
        return 'success';
    }

    public function validateIpRange(string $range, int $router_id, int $exclude_id = 0)
    {
        // Parse simple range (format: 192.168.88.10-192.168.88.50)
        $parsed = parseSimpleRange($range);
        if ($parsed === null) {
            return ['valid' => false, 'error' => 'Formato de rango invalido. Use: IP_INICIO-IP_FIN (ej: 192.168.88.10-192.168.88.50)'];
        }

        // Check router scope - verify IPs are within router's network
        $router = $this->select("SELECT ip_range FROM network_routers WHERE id = $router_id");
        if (!empty($router) && !empty($router['ip_range'])) {
            // Router ip_range might be CIDR or simple range
            if (strpos($router['ip_range'], '/') !== false) {
                if (!ipInCidr($parsed['start'], $router['ip_range']) || !ipInCidr($parsed['end'], $router['ip_range'])) {
                    return ['valid' => false, 'error' => 'El rango IP esta fuera del alcance del router (' . $router['ip_range'] . ')'];
                }
            }
        }

        // Check overlap with existing departments
        $exclude = $exclude_id > 0 ? "AND id != $exclude_id" : "";
        $departments = $this->select_all("SELECT id, name, ip_range FROM muni_departments WHERE router_id = $router_id $exclude");

        foreach ($departments as $dept) {
            if (simpleRangesOverlap($range, $dept['ip_range'])) {
                return ['valid' => false, 'error' => 'El rango IP se superpone con el departamento "' . $dept['name'] . '" (' . $dept['ip_range'] . ')'];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    // =============================================
    // USER CRUD
    // =============================================

    public function getUsers(array $filters = [])
    {
        $where = "WHERE 1=1";

        if (!empty($filters['department_id'])) {
            $dept_id = intval($filters['department_id']);
            $where .= " AND mu.department_id = $dept_id";
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = intval($filters['status']);
            $where .= " AND mu.status = $status";
        }
        if (!empty($filters['search'])) {
            $search = addslashes($filters['search']);
            $where .= " AND (mu.name LIKE '%$search%' OR mu.ip_address LIKE '%$search%')";
        }
        if (!empty($filters['router_id'])) {
            $router_id = intval($filters['router_id']);
            $where .= " AND mu.router_id = $router_id";
        }

        $sql = "SELECT mu.*,
                    md.name AS department_name,
                    COALESCE(mu.custom_upload, md.default_upload, '5M') AS effective_upload,
                    COALESCE(mu.custom_download, md.default_download, '10M') AS effective_download
                FROM muni_users mu
                LEFT JOIN muni_departments md ON mu.department_id = md.id
                $where
                ORDER BY mu.name ASC";

        return $this->select_all($sql);
    }

    public function getUser(int $id)
    {
        $sql = "SELECT mu.*,
                    md.name AS department_name,
                    md.ip_range AS dept_ip_range,
                    COALESCE(mu.custom_upload, md.default_upload, '5M') AS default_upload,
                    COALESCE(mu.custom_download, md.default_download, '10M') AS default_download
                FROM muni_users mu
                LEFT JOIN muni_departments md ON mu.department_id = md.id
                WHERE mu.id = $id";

        return $this->select($sql);
    }

    public function createUser(array $data)
    {
        // Check IP uniqueness
        $check = $this->select("SELECT id FROM muni_users WHERE ip_address = '{$data['ip_address']}'");
        if (!empty($check)) {
            return 'ip_exists';
        }

        $router_id = intval($data['router_id']);
        $dept_id = !empty($data['department_id']) ? intval($data['department_id']) : null;

        // If department provided, validate IP within dept range
        if ($dept_id) {
            $dept = $this->select("SELECT ip_range FROM muni_departments WHERE id = $dept_id");
            if (!empty($dept) && !empty($dept['ip_range'])) {
                $parsed = parseSimpleRange($dept['ip_range']);
                if ($parsed !== null && !ipInRange($data['ip_address'], $parsed['start'], $parsed['end'])) {
                    return 'ip_out_of_range';
                }
            }
        }

        $queue_name = sanitizeQueueName('muni-' . $data['name'] . '-' . $data['ip_address']);

        $upload = !empty($data['custom_upload']) ? $data['custom_upload'] : '5M';
        $download = !empty($data['custom_download']) ? $data['custom_download'] : '10M';

        $query = "INSERT INTO muni_users (department_id, router_id, name, ip_address, mac_address, custom_upload, custom_download, queue_name)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $values = [
            $dept_id,
            $router_id,
            $data['name'],
            $data['ip_address'],
            $data['mac_address'] ?? null,
            $upload,
            $download,
            $queue_name,
        ];

        $result = $this->insert($query, $values);
        return $result ? 'success' : 'error';
    }

    public function updateUser(int $id, array $data)
    {
        // Check IP uniqueness (excluding self)
        $check = $this->select("SELECT id FROM muni_users WHERE ip_address = '{$data['ip_address']}' AND id != $id");
        if (!empty($check)) {
            return 'ip_exists';
        }

        $dept_id = !empty($data['department_id']) ? intval($data['department_id']) : null;

        // If department provided, validate IP within dept range
        if ($dept_id) {
            $dept = $this->select("SELECT ip_range FROM muni_departments WHERE id = $dept_id");
            if (!empty($dept) && !empty($dept['ip_range'])) {
                $parsed = parseSimpleRange($dept['ip_range']);
                if ($parsed !== null && !ipInRange($data['ip_address'], $parsed['start'], $parsed['end'])) {
                    return 'ip_out_of_range';
                }
            }
        }

        $queue_name = sanitizeQueueName('muni-' . $data['name'] . '-' . $data['ip_address']);
        $upload = !empty($data['custom_upload']) ? $data['custom_upload'] : '5M';
        $download = !empty($data['custom_download']) ? $data['custom_download'] : '10M';

        $query = "UPDATE muni_users SET
                    department_id = ?, name = ?, ip_address = ?, mac_address = ?,
                    custom_upload = ?, custom_download = ?,
                    queue_name = ?, queue_sync_status = 'pending'
                  WHERE id = $id";

        $values = [
            $dept_id,
            $data['name'],
            $data['ip_address'],
            $data['mac_address'] ?? null,
            $upload,
            $download,
            $queue_name,
        ];

        $result = $this->update($query, $values);
        return $result ? 'success' : 'error';
    }

    public function deleteUser(int $id)
    {
        $this->delete("DELETE FROM muni_users WHERE id = $id");
        return 'success';
    }

    public function toggleUser(int $id, int $status)
    {
        $sync_status = $status === 1 ? 'pending' : 'disabled';
        $query = "UPDATE muni_users SET status = ?, queue_sync_status = ? WHERE id = $id";
        $result = $this->update($query, [$status, $sync_status]);
        return $result ? 'success' : 'error';
    }

    public function updateUserBandwidth(int $id, string $upload, string $download)
    {
        $query = "UPDATE muni_users SET 
                    custom_upload = ?, 
                    custom_download = ?, 
                    queue_sync_status = 'pending' 
                  WHERE id = $id";
        $result = $this->update($query, [$upload, $download]);
        return $result ? 'success' : 'error';
    }

    public function getUserByIP(string $ip)
    {
        $sql = "SELECT mu.*,
                    md.name AS department_name,
                    md.default_upload,
                    md.default_download
                FROM muni_users mu
                LEFT JOIN muni_departments md ON mu.department_id = md.id
                WHERE mu.ip_address = '$ip'
                LIMIT 1";
        
        return $this->select($sql);
    }

    public function getUsersByDepartment(int $dept_id)
    {
        $sql = "SELECT mu.*,
                    md.default_upload,
                    md.default_download
                FROM muni_users mu
                JOIN muni_departments md ON mu.department_id = md.id
                WHERE mu.department_id = $dept_id AND mu.status = 1
                ORDER BY mu.ip_address ASC";

        return $this->select_all($sql);
    }

    private function getDeptNameById(int $id): string
    {
        $dept = $this->select("SELECT name FROM muni_departments WHERE id = $id");
        return $dept ? $dept['name'] : 'unknown';
    }

    // =============================================
    // FILTERING POLICIES
    // =============================================

    public function getDeptFilterPolicies(int $dept_id)
    {
        $sql = "SELECT dfp.*, cfc.name AS category_name, cfc.icon, cfc.color
                FROM muni_dept_filter_policies dfp
                JOIN content_filter_categories cfc ON dfp.category_id = cfc.id
                WHERE dfp.department_id = $dept_id
                ORDER BY cfc.name ASC";

        return $this->select_all($sql);
    }

    public function saveDeptFilterPolicy(int $dept_id, array $category_ids, string $action = 'block')
    {
        // Remove existing policies for this dept
        $this->delete("DELETE FROM muni_dept_filter_policies WHERE department_id = $dept_id");

        // Insert new ones
        foreach ($category_ids as $cat_id) {
            $cat_id = intval($cat_id);
            $query = "INSERT INTO muni_dept_filter_policies (department_id, category_id, action) VALUES (?, ?, ?)";
            $this->insert($query, [$dept_id, $cat_id, $action]);
        }

        return 'success';
    }

    public function getDeptWhitelist(int $dept_id = null)
    {
        if ($dept_id === null) {
            $sql = "SELECT dw.*, u.names AS added_by_name
                    FROM muni_dept_whitelist dw
                    LEFT JOIN users u ON dw.added_by = u.id
                    WHERE dw.department_id IS NULL
                    ORDER BY dw.domain ASC";
        } else {
            $sql = "SELECT dw.*, u.names AS added_by_name
                    FROM muni_dept_whitelist dw
                    LEFT JOIN users u ON dw.added_by = u.id
                    WHERE dw.department_id = $dept_id OR dw.department_id IS NULL
                    ORDER BY dw.department_id IS NULL ASC, dw.domain ASC";
        }

        return $this->select_all($sql);
    }

    public function addWhitelistDomain(int $dept_id = null, string $domain, int $user_id)
    {
        $query = "INSERT INTO muni_dept_whitelist (department_id, domain, added_by) VALUES (?, ?, ?)";
        $result = $this->insert($query, [$dept_id, $domain, $user_id]);
        return $result ? 'success' : 'error';
    }

    public function removeWhitelistDomain(int $id)
    {
        $this->delete("DELETE FROM muni_dept_whitelist WHERE id = $id");
        return 'success';
    }

    public function getGlobalBlacklist()
    {
        $sql = "SELECT cfd.*, cfc.name AS category_name
                FROM content_filter_domains cfd
                JOIN content_filter_categories cfc ON cfd.category_id = cfc.id
                WHERE cfd.is_active = 1
                ORDER BY cfc.name ASC, cfd.domain ASC";

        return $this->select_all($sql);
    }

    public function getCategories()
    {
        $sql = "SELECT * FROM content_filter_categories WHERE is_active = 1 ORDER BY name";
        return $this->select_all($sql);
    }

    // =============================================
    // AUDIT LOG
    // =============================================

    public function logAction(int $user_id, string $action, string $entity_type = null, int $entity_id = null, string $details = null, string $status = 'success')
    {
        $query = "INSERT INTO muni_audit_log (user_id, action, entity_type, entity_id, details, status) VALUES (?, ?, ?, ?, ?, ?)";
        $this->insert($query, [$user_id, $action, $entity_type, $entity_id, $details, $status]);
    }

    // =============================================
    // QoS HELPERS
    // =============================================

    public function updateQosSyncStatus(int $dept_id, string $status, string $queue_tree_id = null)
    {
        if ($queue_tree_id) {
            $query = "UPDATE muni_departments SET qos_sync_status = ?, qos_queue_tree_id = ? WHERE id = $dept_id";
            $this->update($query, [$status, $queue_tree_id]);
        } else {
            $query = "UPDATE muni_departments SET qos_sync_status = ? WHERE id = $dept_id";
            $this->update($query, [$status]);
        }
    }

    public function updateUserSyncStatus(int $user_id, string $status)
    {
        $query = "UPDATE muni_users SET queue_sync_status = ? WHERE id = $user_id";
        $this->update($query, [$status]);
    }

    public function getRouters()
    {
        $sql = "SELECT id, name, ip, port, username, password, ip_range, status
                FROM network_routers ORDER BY name";
        return $this->select_all($sql);
    }

    public function getRouter(int $id)
    {
        $sql = "SELECT * FROM network_routers WHERE id = $id";
        return $this->select($sql);
    }
}
