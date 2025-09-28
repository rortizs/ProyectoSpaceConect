<?php

class Network extends Controllers
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['login'])) {
            header('Location: ' . base_url() . '/login');
            die();
        }
        consent_permission(INSTALLATIONS);
    }
    /* ROUTERS */
    public function routers()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $data['page_name'] = "Routers";
        $data['page_title'] = "Gestión de Routers";
        $data['home_page'] = "Dashboard";
        $data['actual_page'] = "Routers";
        $data['page_functions_js'] = "routers.js";

        $data['records'] = array();
        $data['zones'] = array();


        $mode_label = [];
        $mode_label["1"] = "Simple Queues";
        $mode_label["2"] = "PPPoE";
        $mode_label["3"] = "Queue Tree";

        $status_id = [];
        $status_id["0"] = "0";
        $status_id["CONNECTED"] = "1";
        $status_id["DISCONNECTED"] = "2";
        //GET ROUTERS

        $result = sql("SELECT r.*, z.id zone_id, z.name zone_name, z.mode zone_mode, (SELECT COUNT(*) FROM clients WHERE net_router = r.id) customers FROM network_routers r JOIN network_zones z ON z.id = r.zoneid");

        // Include RouterFactory for hybrid support
        require_once('Libraries/MikroTik/RouterFactory.php');

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            try {
                $router = RouterFactory::create(
                    $row["ip"], 
                    $row["port"], 
                    $row["username"], 
                    decrypt_aes($row["password"], SECRET_IV),
                    $row["api_type"] ?? 'auto'
                );
                
                if ($router && $router->connected) {
                    $resources = $router->APIGetSystemResources();

                    if ($resources->success) {
                        // Use stored values from database if available, otherwise get from API
                        $row["identity"] = $row["identity"] ?? $row["board_name"] ?? "Unknown";
                        $row["board_name"] = $row["board_name"] ?? $resources->data->{"board-name"} ?? "Unknown";
                        $row["version"] = $row["routeros_version"] ?? $resources->data->{"version"} ?? "Unknown";
                        $row["status"] = "CONNECTED";
                    } else {
                        $row["identity"] = $row["identity"] ?? "-";
                        $row["board_name"] = $row["board_name"] ?? "-";
                        $row["version"] = $row["routeros_version"] ?? "-";
                        $row["status"] = "DISCONNECTED";
                    }
                } else {
                    $row["identity"] = $row["identity"] ?? "-";
                    $row["board_name"] = $row["board_name"] ?? "-";
                    $row["version"] = $row["routeros_version"] ?? "-";
                    $row["status"] = "DISCONNECTED";
                }
            } catch (Exception $e) {
                // If connection fails, use stored data from database
                $row["identity"] = $row["identity"] ?? "-";
                $row["board_name"] = $row["board_name"] ?? "-";
                $row["version"] = $row["routeros_version"] ?? "-";
                $row["status"] = "DISCONNECTED";
            }

            $row["status_id"] = $status_id[$row["status"]];

            $row["zone"] = $row["zone_name"] . ' (' . $mode_label[$row["zone_mode"]] . ')';

            $data['records'][] = $row;
        }

        //GET ZONES

        $result = sql("SELECT * FROM network_zones");

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            $data['zones'][] = $row;
        }

        ////

        $this->views->getView($this, "routers", $data);
    }
    public function add_router()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada o sin permisos. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        $res = (object) array();

        if (!empty($_POST['name']) && !empty($_POST['ip']) && !empty($_POST['port']) && !empty($_POST['username']) && !empty($_POST['password'])) {

            try {
                require_once('Libraries/MikroTik/RouterFactory.php');

                // Use hybrid system to detect router type and connect
                $router_info = RouterFactory::getRouterInfo(
                    $_POST['ip'],
                    intval($_POST['port']),
                    $_POST['username'],
                    $_POST['password']
                );

                if ($router_info['connected']) {
                    $item = (object) array();
                    $item->name = $_POST['name'];
                    $item->ip = $_POST['ip'];
                    $item->port = $_POST['port'];
                    $item->username = $_POST['username'];
                    $item->password = encrypt_aes($_POST['password'], SECRET_IV);
                    $item->ip_range = $_POST['ip_range'];
                    $item->zoneid = $_POST['zoneid'];
                    
                    // Add detected router information
                    $item->routeros_version = $router_info['version'];
                    $item->api_type = $router_info['api_type'];
                    $item->board_name = $router_info['board_name'] ?? '';
                    $item->status = 'connected';

                    // Use direct SQL insert to avoid field issues
                    $insert_sql = "INSERT INTO network_routers (name, ip, port, username, password, ip_range, zoneid, routeros_version, api_type, board_name, status, identity, version) VALUES (
                        '{$item->name}',
                        '{$item->ip}',
                        {$item->port},
                        '{$item->username}',
                        '{$item->password}',
                        '{$item->ip_range}',
                        {$item->zoneid},
                        '{$item->routeros_version}',
                        '{$item->api_type}',
                        '{$item->board_name}',
                        '{$item->status}',
                        '{$item->board_name}',
                        '{$item->routeros_version}'
                    )";
                    
                    sql($insert_sql);

                    $res->result = "success";
                    $res->message = "Router agregado correctamente";
                    $res->router_info = $router_info;
                } else {
                    $res->result = "failed";
                    $res->message = "No se pudo conectar al router: " . ($router_info['error'] ?? 'Error desconocido');
                }
            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al conectar con el router: " . $e->getMessage();
            }
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function get_edit_router()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada o sin permisos. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {

                $item = (object) array();

                $r->password = base64_encode(decrypt_aes($r->password, SECRET_IV));

                $res->result = "success";
                $res->data = $r;
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function edit_router()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada o sin permisos. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {

                try {
                    require_once('Libraries/MikroTik/RouterFactory.php');
                    
                    // Decode password if it comes base64 encoded from frontend
                    $password = $_POST["password"];
                    if (base64_encode(base64_decode($password, true)) === $password) {
                        $password = base64_decode($password);
                    }
                    
                    $router = RouterFactory::create(
                        $_POST["ip"], 
                        $_POST["port"], 
                        $_POST["username"], 
                        $password,
                        'auto'
                    );

                    if ($router && $router->connected) {

                    $item = (object) array();

                    if (isset($_POST['name'])) {
                        sqlUpdate("network_routers", "name", $_POST['name'], $_POST['id']);
                    }
                    if (isset($_POST['ip'])) {
                        sqlUpdate("network_routers", "ip", $_POST['ip'], $_POST['id']);
                    }
                    if (isset($_POST['port'])) {
                        sqlUpdate("network_routers", "port", $_POST['port'], $_POST['id']);
                    }
                    if (isset($_POST['username'])) {
                        sqlUpdate("network_routers", "username", $_POST['username'], $_POST['id']);
                    }
                    if (isset($_POST['password'])) {
                        // Use the decoded password for encryption
                        sqlUpdate("network_routers", "password", encrypt_aes($password, SECRET_IV), $_POST['id']);
                    }
                    if (isset($_POST['ip_range'])) {
                        sqlUpdate("network_routers", "ip_range", $_POST['ip_range'], $_POST['id']);
                    }
                    if (isset($_POST['zoneid'])) {
                        sqlUpdate("network_routers", "zoneid", $_POST['zoneid'], $_POST['id']);
                    }
                    
                    // Update router information from API detection if successful connection
                    $resources = $router->APIGetSystemResources();
                    if ($resources && $resources->success) {
                        if (isset($resources->data->version)) {
                            sqlUpdate("network_routers", "routeros_version", $resources->data->version, $_POST['id']);
                        }
                        if (isset($resources->data->{"board-name"})) {
                            sqlUpdate("network_routers", "board_name", $resources->data->{"board-name"}, $_POST['id']);
                        }
                        sqlUpdate("network_routers", "status", "connected", $_POST['id']);
                    }

                        $res->result = "success";
                        $res->message = "Router actualizado correctamente";
                    } else {
                        $res->result = "failed";
                        $res->message = "Could not connect to router.";
                    }
                } catch (Exception $e) {
                    $res->result = "failed";
                    $res->message = "Error al conectar: " . $e->getMessage();
                }
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function remove_router()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {

                sqlDelete("network_routers", $_POST['id']);

                $res->result = "success";
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }

            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }
    }

    public function router_system_info()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {
                require_once('Libraries/MikroTik/RouterFactory.php');
                
                $router = RouterFactory::create(
                    $r->ip, 
                    $r->port, 
                    $r->username, 
                    decrypt_aes($r->password, SECRET_IV),
                    $r->api_type ?? 'auto'
                );
                
                $rres = null;
                if ($router && $router->connected) {
                    $rres = $router->APIGetSystemResources();
                }

                if ($rres && $rres->success) {

                    $res->result = "success";

                    $resources = $rres->data;

                    // Handle memory information with null checks
                    $free_mem = $resources->{"free-memory"} ?? 0;
                    $total_mem = $resources->{"total-memory"} ?? 0;
                    $resources->free_memory = humanReadableBytes($free_mem) . ($total_mem > 0 ? " (" . round((($free_mem / $total_mem) * 100), 2) . "%)" : "");
                    $resources->total_memory = humanReadableBytes($total_mem);
                    
                    // Handle CPU information with null checks
                    $resources->cpu_count = $resources->{"cpu-count"} ?? 'N/A';
                    $resources->cpu_frequency = $resources->{"cpu-frequency"} ?? 'N/A';
                    $resources->cpu_load = $resources->{"cpu-load"} ?? 'N/A';
                    $resources->factory_software = $resources->{"factory-software"} ?? 'N/A';
                    
                    // Handle HDD information with null checks
                    $free_hdd = $resources->{"free-hdd-space"} ?? 0;
                    $total_hdd = $resources->{"total-hdd-space"} ?? 0;
                    $resources->free_hdd_space = humanReadableBytes($free_hdd) . ($total_hdd > 0 ? " (" . round((($free_hdd / $total_hdd) * 100), 2) . "%)" : "");
                    $resources->total_hdd_space = humanReadableBytes($total_hdd);
                    // Handle other system information with null checks
                    $resources->build_time = $resources->{"build-time"} ?? 'N/A';
                    $resources->board_name = $resources->{"board-name"} ?? 'N/A';
                    $resources->bad_blocks = $resources->{"bad-blocks"} ?? 'N/A';
                    $resources->architecture_name = $resources->{"architecture-name"} ?? 'N/A';
                    $resources->write_sect_since_reboot = $resources->{"write-sect-since-reboot"} ?? 'N/A';
                    $resources->write_sect_total = $resources->{"write-sect-total"} ?? 'N/A';
                    $resources->uptime = $resources->uptime ?? 'N/A';
                    $resources->cpu = $resources->cpu ?? 'N/A';

                    $res->html = "
                    <tr><td>Uptime</td><td>$resources->uptime</td></tr>
                    <tr><td>Free Memory</td><td>$resources->free_memory</td></tr>
                    <tr><td>Total Memory</td><td>$resources->total_memory</td></tr>
                    <tr><td>CPU</td><td>$resources->cpu</td></tr>
                    <tr><td>CPU Count</td><td>$resources->cpu_count</td></tr>
                    <tr><td>CPU Frequency</td><td>$resources->cpu_frequency</td></tr>
                    <tr><td>CPU Load</td><td>$resources->cpu_load</td></tr>
                    <tr><td>Free HDD Space</td><td>$resources->free_hdd_space</td></tr>
                    <tr><td>Total HDD Size</td><td>$resources->total_hdd_space</td></tr>
                    <tr><td>Sector Writes Since Reboot</td><td>$resources->write_sect_since_reboot</td></tr>
                    <tr><td>Total Sector Writes</td><td>$resources->write_sect_total</td></tr>
                    <tr><td>Bad Blocks</td><td>$resources->bad_blocks</td></tr>
                    <tr><td>Architecture Name</td><td>$resources->architecture_name</td></tr>
                    <tr><td>Board Name</td><td>$resources->board_name</td></tr>
                    <tr><td>Version</td><td>$resources->version</td></tr>
                    <tr><td>Build Time</td><td>$resources->build_time</td></tr>
                    <tr><td>Factory Software</td><td>$resources->factory_software</td></tr>
                    ";
                } else {

                    $res->result = "failed";
                    $res->html = "
                    <tr><td>Could not connect with Router</td><td></td></tr>";
                }
            }
        }

        ////

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function regla_moroso()
    {
        $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

        if ($r && !empty($r->id)) {
            $router_ip = $r->ip;
            $router_port = $r->port;
            $username = $r->username;
            $password = decrypt_aes($r->password, SECRET_IV);

            // Obtener reglas actuales del firewall
            $existing_rules = $this->getExistingRules("http://$router_ip:$router_port/rest/ip/firewall/filter", $username, $password);

            $firewall_rules = [
                ["action" => "accept", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Permitir HTTPS a página de corte", "dst-address" => "46.202.183.172", "dst-port" => "443", "protocol" => "tcp", "src-address-list" => "moroso"],
                ["action" => "accept", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Permitir acceso a página de corte", "dst-port" => "53", "protocol" => "udp", "src-address-list" => "moroso"],
                ["action" => "accept", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Permitir acceso a página de corte", "protocol" => "udp", "src-address-list" => "moroso", "src-port" => "53"],
                ["action" => "drop", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Bloqueo de Internet para morosos", "src-address-list" => "moroso"],
                ["action" => "drop", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Bloqueo HTTPS para morosos", "dst-port" => "443", "protocol" => "tcp", "src-address-list" => "moroso"],
                ["action" => "drop", "chain" => "forward", "comment" => "REGLA CREADA DESDE EL SISTEMA - Bloqueo HTTP para morosos", "dst-port" => "80", "protocol" => "tcp", "src-address-list" => "moroso"]
            ];

            foreach ($firewall_rules as $rule) {
                if (!$this->ruleExists($rule['comment'], $existing_rules)) {
                    $url = "http://$router_ip:$router_port/rest/ip/firewall/filter";
                    $this->sendRequest($url, $username, $password, $rule);
                }
            }

            // Obtener reglas actuales del NAT
            $existing_nat = $this->getExistingRules("http://$router_ip:$router_port/rest/ip/firewall/nat", $username, $password);

            $nat_rule = [
                "action" => "redirect",
                "chain" => "dstnat",
                "comment" => "REGLA CREADA DESDE EL SISTEMA - Redirección Web Proxy para morosos",
                "dst-port" => "80",
                "protocol" => "tcp",
                "src-address-list" => "moroso",
                "to-ports" => "3128"
            ];

            if (!$this->ruleExists($nat_rule['comment'], $existing_nat)) {
                $url_nat = "http://$router_ip:$router_port/rest/ip/firewall/nat";
                $this->sendRequest($url_nat, $username, $password, $nat_rule);
            }

            // Obtener reglas actuales del proxy
            $existing_proxy = $this->getExistingRules("http://$router_ip:$router_port/rest/ip/proxy/access", $username, $password);

            $proxy_rules = [
                ["action" => "allow", "dst-address" => "46.202.183.20", "comment" => "REGLA CREADA DESDE EL SISTEMA - Permitir acceso"],
                ["action" => "redirect", "dst-port" => "80", "action-data" => "corte.globalsn.pe", "comment" => "REGLA CREADA DESDE EL SISTEMA - Redirección Web Proxy"]
            ];

            foreach ($proxy_rules as $rule) {
                if (!$this->ruleExists($rule['comment'], $existing_proxy)) {
                    $url_proxy = "http://$router_ip:$router_port/rest/ip/proxy/access";
                    $this->sendRequest($url_proxy, $username, $password, $rule);
                }
            }

            echo json_encode(["result" => "success", "message" => "Reglas aplicadas correctamente."]);
            exit;
        }

        echo json_encode(["result" => "failed", "message" => "Router no encontrado."]);
        exit;
    }

    private function sendRequest($url, $username, $password, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function getExistingRules($url, $username, $password)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: [];
    }

    private function ruleExists($comment, $existing_rules)
    {
        foreach ($existing_rules as $rule) {
            if (isset($rule['comment']) && $rule['comment'] === $comment) {
                return true;
            }
        }
        return false;
    }

    public function router_reboot()
    {
        $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

        if ($r && !empty($r->id)) {
            $url = "http://{$r->ip}:{$r->port}/rest/system/reboot";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_USERPWD, "{$r->username}:" . decrypt_aes($r->password, SECRET_IV));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            $response = curl_exec($ch);
            curl_close($ch);

            echo json_encode(["result" => "success", "message" => "El MikroTik se está reiniciando."]);
            exit;
        }

        echo json_encode(["result" => "failed", "message" => "Router no encontrado."]);
        exit;
    }


    public function router_system_interface()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada o sin permisos. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {
                try {
                    // Use RouterFactory for better performance and compatibility
                    require_once('Libraries/MikroTik/RouterFactory.php');
                    $router = RouterFactory::create(
                        $r->ip, 
                        $r->port, 
                        $r->username, 
                        decrypt_aes($r->password, SECRET_IV),
                        $r->api_type ?? 'auto'
                    );

                    if ($router && $router->connected) {
                        // Get interface statistics efficiently
                        if ($router instanceof RouterLegacy) {
                            // Legacy API
                            $interface_result = $router->api->comm('/interface/print', array('?disabled' => 'false'));
                        } else {
                            // REST API
                            $interface_result = $router->makeRequest('GET', '/interface', null, ['disabled' => 'false']);
                        }
                        
                        if ($interface_result) {
                            $res->result = "success";
                            $res->interface = $interface_result;
                        } else {
                            $res->result = "failed";
                            $res->message = "No se pudieron obtener las interfaces";
                        }
                    } else {
                        $res->result = "failed";
                        $res->message = "No se pudo conectar al router";
                    }
                } catch (Exception $e) {
                    $res->result = "failed";
                    $res->message = "Error al conectar: " . $e->getMessage();
                }
            } else {
                $res->result = "failed";
                $res->message = "Router no encontrado";
            }
        } else {
            $res->result = "failed";
            $res->message = "ID de router requerido";
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function router_system_log()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada o sin permisos. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {
                try {
                    // Use RouterFactory for better compatibility
                    require_once('Libraries/MikroTik/RouterFactory.php');
                    $router = RouterFactory::create(
                        $r->ip, 
                        $r->port, 
                        $r->username, 
                        decrypt_aes($r->password, SECRET_IV),
                        $r->api_type ?? 'auto'
                    );

                    if ($router && $router->connected) {
                        // Limit logs to last 100 entries for better performance
                        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 100;
                        
                        // Get logs with limit - different approach for Legacy vs REST
                        if ($router instanceof RouterLegacy) {
                            // Legacy API - use print with count
                            $log_result = $router->api->comm('/log/print', array('?count' => $limit));
                        } else {
                            // REST API - use query parameter
                            $log_result = $router->makeRequest('GET', '/log', null, ['limit' => $limit]);
                        }

                        if ($log_result) {
                            $res->result = "success";
                            $res->html = "";
                            $count = 0;
                            
                            // Process logs efficiently
                            if (is_array($log_result)) {
                                // Reverse array to show newest first
                                $logs = array_reverse(array_slice($log_result, 0, $limit));
                                
                                foreach ($logs as $log) {
                                    if ($count >= $limit) break;
                                    
                                    $log_obj = is_object($log) ? $log : (object)$log;
                                    
                                    $topics = $log_obj->topics ?? ($log_obj->{'topics'} ?? '');
                                    $message = $log_obj->message ?? ($log_obj->{'message'} ?? '');
                                    $time = $log_obj->time ?? ($log_obj->{'time'} ?? '');
                                    
                                    $error = (stripos($topics, 'error') !== false || stripos($topics, 'critical') !== false) ? 'class="log-error"' : '';
                                    
                                    $res->html .= "<tr $error><td>" . htmlspecialchars($time) . "</td><td>" . htmlspecialchars($topics) . "</td><td>" . htmlspecialchars($message) . "</td></tr>";
                                    $count++;
                                }
                            }
                            
                            $res->total_logs = $count;
                            
                            if ($count == 0) {
                                $res->html = "<tr><td colspan='3' class='text-center'>No se encontraron logs recientes</td></tr>";
                            }
                            
                        } else {
                            $res->result = "failed";
                            $res->message = "No se pudieron obtener los logs";
                        }
                    } else {
                        $res->result = "failed";
                        $res->message = "No se pudo conectar al router";
                    }
                } catch (Exception $e) {
                    $res->result = "failed";
                    $res->message = "Error al conectar: " . $e->getMessage();
                }
            } else {
                $res->result = "failed";
                $res->message = "Router no encontrado";
            }
        } else {
            $res->result = "failed";
            $res->message = "ID de router requerido";
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function router_available_ips()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();
        $querySearch = isset($_POST['querySearch']) ? $_POST['querySearch'] : null;

        if (!empty($_POST['id'])) {

            $id = $_POST['id'];
            $r = sqlObject("SELECT * FROM network_routers WHERE id = " . $id);

            if (!is_null($r->id)) {
                if (!empty($r->ip_range)) {
                    $lines = explode("\n", $r->ip_range);

                    $res = [];
                    $exl = array();

                    $result = sql("SELECT c.net_ip ip FROM clients c WHERE net_router = $r->id");

                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                        $exl[] = $row["ip"];
                    }


                    foreach ($lines as $k => $ip) {
                        $res = array_merge($res, getAvailableIPs($ip, $exl));
                    }

                    if ($querySearch) {
                        $res = array_filter($res, function ($id) use ($querySearch) {
                            return strpos($id, $querySearch) !== false;
                        });
                    }
                }
            }
        }

        $this->json($res);
    }

    /* ZONES */
    public function zones()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $data['page_name'] = "Zonas";
        $data['page_title'] = "Gestión de Zonas";
        $data['home_page'] = "Dashboard";
        $data['actual_page'] = "Zonas";
        $data['page_functions_js'] = "zones.js";

        $data['records'] = array();

        //GET ROUTERS

        $result = sql("SELECT z.*, (SELECT COUNT(*) FROM network_routers r WHERE r.zoneid = z.id) routers FROM network_zones z;");

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            $data['records'][] = $row;
        }

        ////

        $this->views->getView($this, "zones", $data);
    }
    public function add_zone()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['name']) && !empty($_POST['mode'])) {

            $item = (object) array();
            $item->name = $_POST['name'];
            $item->mode = $_POST['mode'];

            sqlInsert("network_zones", $item);

            $res->result = "success";
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function get_edit_zone()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_zones WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {

                $res->result = "success";
                $res->data = $r;
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function edit_zone()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_zones WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {

                $item = (object) array();

                if (isset($_POST['name'])) {
                    sqlUpdate("network_zones", "name", $_POST['name'], $_POST['id']);
                }
                if (isset($_POST['mode'])) {
                    sqlUpdate("network_zones", "mode", $_POST['mode'], $_POST['id']);
                }

                $res->result = "success";
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }
        } else {
            $res->result = "failed";
            $res->message = "Invalid request";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function remove_zone()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['id'])) {

            $r = sqlObject("SELECT * FROM network_zones WHERE id = " . $_POST['id']);

            if (!is_null($r->id)) {


                $c = sqlObject("SELECT COUNT(*) count FROM network_routers WHERE zoneid = " . $_POST['id'])->count;

                if ($c == 0) {
                    sqlDelete("network_zones", $_POST['id']);

                    $res->result = "success";
                } else {
                    $res->result = "failed";
                    $res->message = "This zone have routers assigned";
                }
            } else {
                $res->result = "failed";
                $res->message = "Invalid request";
            }

            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }
    }

    public function network_template()
    {
        return $this->views->getView($this, "template", []);
    }

    public function network_ip_template()
    {
        return $this->views->getView($this, "ip-template", []);
    }

    /* CONTENT FILTERING */
    public function contentfilter()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
            exit();
        }
        $data['page_name'] = "Filtro de Contenido";
        $data['page_title'] = "Gestión de Filtro de Contenido";
        $data['home_page'] = "Dashboard";
        $data['actual_page'] = "Filtro de Contenido";
        $data['page_functions_js'] = "contentfilter.js";

        // Initialize default data arrays to prevent errors
        $data['categories'] = [];
        $data['policies'] = [];
        $data['stats'] = [
            'total_policies' => 0,
            'filtered_clients' => 0,
            'total_categories' => 0,
            'blocked_domains' => 0
        ];
        $data['unfiltered_clients'] = [];
        $data['recent_logs'] = [];

        try {
            // Check if content filter tables exist first
            $table_check = sql("SHOW TABLES LIKE 'content_filter_categories'");
            if (!$table_check || mysqli_num_rows($table_check) == 0) {
                // Continue with default data
            } else {
                // Get content filter service
                $contentFilterService = new ContentFilterService();
                
                $data['categories'] = $contentFilterService->getCategories();
                $data['policies'] = $contentFilterService->getPolicies();
                
                // Get statistics and clients without filtering
                $contentfilterModel = new ContentfilterModel();
                $data['stats'] = $contentfilterModel->getFilteringStats();
                $data['unfiltered_clients'] = $contentfilterModel->getClientsWithoutFiltering();
                
                // Get recent logs
                $data['recent_logs'] = $contentFilterService->getFilteringLogs(null, 20);
            }
        } catch (Exception $e) {
            // Continue with default empty data arrays
        } catch (Error $e) {
            // Continue with default empty data arrays
        }

        
        $this->views->getView($this, "contentfilter", $data);
    }

    public function apply_content_filter()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['client_id']) && !empty($_POST['policy_id']) && !empty($_POST['router_id'])) {
            
            $contentFilterService = new ContentFilterService();
            $result = $contentFilterService->applyPolicyToClient(
                $_POST['client_id'], 
                $_POST['policy_id'], 
                $_POST['router_id']
            );

            $res->result = $result['success'] ? "success" : "failed";
            $res->message = $result['message'];
            if (isset($result['domains_blocked'])) {
                $res->domains_blocked = $result['domains_blocked'];
            }
        } else {
            $res->result = "failed";
            $res->message = "Parámetros requeridos faltantes";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function remove_content_filter()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['client_id']) && !empty($_POST['router_id'])) {
            
            $contentFilterService = new ContentFilterService();
            $result = $contentFilterService->removePolicyFromClient(
                $_POST['client_id'], 
                $_POST['router_id']
            );

            $res->result = $result['success'] ? "success" : "failed";
            $res->message = $result['message'];
            if (isset($result['domains_unblocked'])) {
                $res->domains_unblocked = $result['domains_unblocked'];
            }
        } else {
            $res->result = "failed";
            $res->message = "Parámetros requeridos faltantes";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function get_client_filter_status()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['client_id']) && !empty($_POST['router_id'])) {
            
            $contentfilterModel = new ContentfilterModel();
            $policy = $contentfilterModel->getClientPolicy($_POST['client_id'], $_POST['router_id']);
            
            if ($policy) {
                $res->result = "success";
                $res->has_policy = true;
                $res->policy = $policy;
            } else {
                $res->result = "success";
                $res->has_policy = false;
            }
        } else {
            $res->result = "failed";
            $res->message = "Parámetros requeridos faltantes";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function create_filter_policy()
    {
        // Debug session info
        error_log("create_filter_policy - Session login: " . ($_SESSION['login'] ?? 'NOT_SET'));
        error_log("create_filter_policy - POST data: " . print_r($_POST, true));
        
        // Check basic login first - if user is logged in, allow
        if (empty($_SESSION['login'])) {
            error_log("create_filter_policy - No login session, returning error");
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        error_log("create_filter_policy - Session OK, proceeding");
        $res = (object) array();

        // Handle both category_ids and categories[] formats
        $categories = [];
        if (!empty($_POST['category_ids'])) {
            $categories = $_POST['category_ids'];
        } elseif (!empty($_POST['categories'])) {
            $categories = $_POST['categories'];
        }

        if (!empty($_POST['name']) && !empty($categories)) {
            try {
                // Create policy directly in database (simplified approach)
                $name = addslashes($_POST['name']);
                $description = addslashes($_POST['description'] ?? '');
                $is_default = isset($_POST['is_default']) ? 1 : 0;
                
                // Insert policy
                $policy_sql = "INSERT INTO content_filter_policies (name, description, is_default, is_active, created_at) 
                              VALUES ('$name', '$description', $is_default, 1, NOW())";
                
                error_log("create_filter_policy - Executing SQL: $policy_sql");
                
                // Use direct mysqli connection to get insert_id
                global $mysqli;
                $con = new mysqli($mysqli->server, $mysqli->user, $mysqli->password, $mysqli->database);
                $con->set_charset("utf8");
                
                if ($con->query($policy_sql)) {
                    $policy_id = $con->insert_id;
                    error_log("create_filter_policy - Policy created with ID: $policy_id");
                    $con->close();
                    
                    if ($policy_id && $policy_id > 0) {
                        // Insert policy-category relationships
                        foreach ($categories as $category_id) {
                            $cat_id = intval($category_id);
                            $cat_sql = "INSERT INTO content_filter_policy_categories (policy_id, category_id) VALUES ($policy_id, $cat_id)";
                            error_log("create_filter_policy - Executing category SQL: $cat_sql");
                            $cat_result = sql($cat_sql);
                            if (!$cat_result) {
                                error_log("create_filter_policy - Failed to insert category $cat_id for policy $policy_id");
                            }
                        }
                    } else {
                        throw new Exception("Failed to get valid policy ID: $policy_id");
                    }
                    
                    $res->result = "success";
                    $res->message = "Política creada correctamente";
                    $res->policy_id = $policy_id;
                    
                    error_log("create_filter_policy - Success: Policy created with ID $policy_id");
                } else {
                    $con->close();
                    $res->result = "failed";
                    $res->message = "Error al crear la política: " . $con->error;
                    error_log("create_filter_policy - Error: Failed to insert policy - " . $con->error);
                }
                
            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al crear la política: " . $e->getMessage();
                error_log("create_filter_policy - Exception: " . $e->getMessage());
            }
        } else {
            $res->result = "failed";
            $res->message = "Nombre de política y categorías son requeridos";
            error_log("create_filter_policy - Validation failed - Name: " . ($_POST['name'] ?? 'missing') . ", Categories: " . (!empty($categories) ? count($categories) : 0));
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }


    public function get_filter_categories()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        $contentfilterModel = new ContentfilterModel();
        $categories = $contentfilterModel->getCategories();
        
        $res->result = "success";
        $res->data = $categories;

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function get_filter_policies()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        $contentFilterService = new ContentFilterService();
        $policies = $contentFilterService->getPolicies();
        
        $res->result = "success";
        $res->data = $policies;

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function get_filtering_logs()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : null;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;

        $contentFilterService = new ContentFilterService();
        $logs = $contentFilterService->getFilteringLogs($client_id, $limit);
        
        $res->result = "success";
        $res->data = $logs;

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function bulk_apply_filter()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
        }
        $res = (object) array();

        if (!empty($_POST['client_ids']) && !empty($_POST['policy_id'])) {
            
            $contentFilterService = new ContentFilterService();
            $client_ids = explode(',', $_POST['client_ids']);
            $results = [];
            $success_count = 0;
            $error_count = 0;

            foreach ($client_ids as $client_id) {
                $client_id = trim($client_id);
                if (empty($client_id)) continue;

                // Get client's router
                $client = sqlObject("SELECT net_router FROM clients WHERE id = ?", [$client_id]);
                if (!$client) continue;

                $result = $contentFilterService->applyPolicyToClient(
                    $client_id, 
                    $_POST['policy_id'], 
                    $client->net_router
                );

                if ($result['success']) {
                    $success_count++;
                } else {
                    $error_count++;
                }

                $results[] = [
                    'client_id' => $client_id,
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
            }

            $res->result = "success";
            $res->message = "Procesados: {$success_count} exitosos, {$error_count} errores";
            $res->results = $results;
            $res->success_count = $success_count;
            $res->error_count = $error_count;
        } else {
            $res->result = "failed";
            $res->message = "IDs de clientes y política son requeridos";
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function get_policy_details()
    {
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location:" . base_url() . '/dashboard');
            exit();
        }
        
        $res = (object) array();
        
        if (!empty($_POST['policy_id'])) {
            try {
                // Get policy details from database
                $policy_result = sql("SELECT * FROM content_filter_policies WHERE id = " . intval($_POST['policy_id']));
                
                if ($policy_result && mysqli_num_rows($policy_result) > 0) {
                    $policy = mysqli_fetch_array($policy_result, MYSQLI_ASSOC);
                    
                    // Get associated categories
                    $categories_result = sql("SELECT c.* FROM content_filter_categories c 
                                            JOIN content_filter_policy_categories pc ON c.id = pc.category_id 
                                            WHERE pc.policy_id = " . intval($_POST['policy_id']));
                    
                    $categories = [];
                    while ($cat = mysqli_fetch_array($categories_result, MYSQLI_ASSOC)) {
                        $categories[] = $cat['id'];
                    }
                    
                    $policy['selected_categories'] = $categories;
                    
                    $res->result = "success";
                    $res->policy = $policy;
                } else {
                    $res->result = "failed";
                    $res->message = "Política no encontrada";
                }
            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al obtener detalles de la política";
            }
        } else {
            $res->result = "failed";
            $res->message = "ID de política requerido";
        }
        
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function update_policy()
    {
        // Debug session info
        error_log("update_policy - Session login: " . ($_SESSION['login'] ?? 'NOT_SET'));
        error_log("update_policy - Session user: " . ($_SESSION['userData']['names'] ?? 'NOT_SET'));
        error_log("update_policy - Session profile: " . ($_SESSION['userData']['profile'] ?? 'NOT_SET'));
        
        // Check basic login first - if user is logged in, allow (temporary fix)
        if (empty($_SESSION['login'])) {
            error_log("update_policy - No login session, redirecting");
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        error_log("update_policy - Session OK, proceeding");
        
        $res = (object) array();
        
        if (!empty($_POST['policy_id']) && !empty($_POST['name'])) {
            try {
                $policy_id = intval($_POST['policy_id']);
                $name = addslashes($_POST['name']);
                $description = addslashes($_POST['description'] ?? '');
                
                // Update policy
                $update_sql = "UPDATE content_filter_policies SET 
                              name = '$name', 
                              description = '$description', 
                              updated_at = NOW() 
                              WHERE id = $policy_id";
                
                $result = sql($update_sql);
                
                if ($result) {
                    // Update categories
                    if (isset($_POST['categories'])) {
                        // Remove existing categories
                        sql("DELETE FROM content_filter_policy_categories WHERE policy_id = $policy_id");
                        
                        // Add new categories
                        foreach ($_POST['categories'] as $category_id) {
                            $cat_id = intval($category_id);
                            sql("INSERT INTO content_filter_policy_categories (policy_id, category_id) VALUES ($policy_id, $cat_id)");
                        }
                    }
                    
                    $res->result = "success";
                    $res->message = "Política actualizada correctamente";
                } else {
                    $res->result = "failed";
                    $res->message = "Error al actualizar la política";
                }
                
            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al actualizar la política: " . $e->getMessage();
            }
        } else {
            $res->result = "failed";
            $res->message = "Datos requeridos faltantes";
        }
        
        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function delete_policy()
    {
        // Check basic login first
        if (empty($_SESSION['login'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $res = (object) array();
        
        if (!empty($_POST['policy_id'])) {
            try {
                $policy_id = intval($_POST['policy_id']);
                
                // Check if policy is not default
                $check_result = sql("SELECT is_default FROM content_filter_policies WHERE id = $policy_id");
                $policy = mysqli_fetch_array($check_result);
                
                if ($policy && $policy['is_default'] == 1) {
                    $res->result = "failed";
                    $res->message = "No se puede eliminar la política por defecto";
                } else {
                    // Check if policy is being used by clients
                    $usage_result = sql("SELECT COUNT(*) as count FROM content_filter_client_policies WHERE policy_id = $policy_id AND is_active = 1");
                    $usage = mysqli_fetch_array($usage_result);
                    
                    if ($usage['count'] > 0) {
                        $res->result = "failed";
                        $res->message = "No se puede eliminar la política porque está siendo utilizada por {$usage['count']} cliente(s)";
                    } else {
                        // Delete policy categories first
                        sql("DELETE FROM content_filter_policy_categories WHERE policy_id = $policy_id");
                        
                        // Delete policy
                        $delete_result = sql("DELETE FROM content_filter_policies WHERE id = $policy_id");
                        
                        if ($delete_result) {
                            $res->result = "success";
                            $res->message = "Política eliminada correctamente";
                        } else {
                            $res->result = "failed";
                            $res->message = "Error al eliminar la política";
                        }
                    }
                }
                
            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al eliminar la política: " . $e->getMessage();
            }
        } else {
            $res->result = "failed";
            $res->message = "ID de política requerido";
        }
        
        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function detect_router_version()
    {
        // Detect RouterOS version and API type for hybrid system
        if (empty($_SESSION['login'])) {
            $res = (object) array();
            $res->result = "failed";
            $res->message = "Sesión expirada. Por favor recarga la página.";
            header('Content-Type: application/json');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $res = (object) array();

        if (!empty($_POST['ip']) && !empty($_POST['port']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            try {
                require_once('Libraries/MikroTik/RouterFactory.php');

                $router_info = RouterFactory::getRouterInfo(
                    $_POST['ip'],
                    intval($_POST['port']),
                    $_POST['username'],
                    $_POST['password']
                );

                if ($router_info['connected']) {
                    $res->result = "success";
                    $res->data = $router_info;
                    $res->message = "Router detectado correctamente";
                } else {
                    $res->result = "failed";
                    $res->message = "No se pudo conectar al router: " . ($router_info['error'] ?? 'Error desconocido');
                }

            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al detectar router: " . $e->getMessage();
            }
        } else {
            $res->result = "failed";
            $res->message = "Datos de conexión requeridos";
        }

        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function test_router_connection()
    {
        // Test router connection without authentication dependency
        $res = (object) array();
        
        if (!empty($_POST['ip']) && !empty($_POST['port']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            try {
                require_once('Libraries/MikroTik/RouterFactory.php');

                $router_info = RouterFactory::getRouterInfo(
                    $_POST['ip'],
                    intval($_POST['port']),
                    $_POST['username'],
                    $_POST['password']
                );

                if ($router_info['connected']) {
                    $res->result = "success";
                    $res->message = "Conexión exitosa con el router";
                    $res->router_info = $router_info;
                } else {
                    $res->result = "failed";
                    $res->message = "No se pudo conectar al router: " . ($router_info['error'] ?? 'Error desconocido');
                }

            } catch (Exception $e) {
                $res->result = "failed";
                $res->message = "Error al conectar con el router: " . $e->getMessage();
            }
        } else {
            $res->result = "failed";
            $res->message = "Datos de conexión requeridos";
        }
        
        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
