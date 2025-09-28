<?php

class Queuetree extends Controllers
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['login'])) {
            header('Location: ' . base_url() . '/login');
            die();
        }
        
        // Verificar permisos para el módulo de red
        if (empty($_SESSION['permits_module']['v'])) {
            header("Location: " . base_url() . '/dashboard');
            die();
        }
    }

    public function queuetree()
    {
        $this->index();
    }

    // Vista principal de Queue Tree
    public function index()
    {
        $data['page_tag'] = "Queue Tree - Gestión QoS";
        $data['page_title'] = "GESTIÓN QUEUE TREE";
        $data['page_name'] = "queuetree";
        $data['page_functions_js'] = "queuetree.js";

        $this->views->getView($this, "index", $data);
    }

    // Listar políticas Queue Tree
    public function getPolicies()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $router_filter = "";
                if (!empty($_POST['router_id'])) {
                    $router_id = intval($_POST['router_id']);
                    $router_filter = "WHERE qtp.router_id = $router_id AND qtp.status = 'active'";
                }
                
                $sql = "SELECT 
                    qtp.id,
                    qtp.name,
                    qtp.target,
                    qtp.max_limit,
                    qtp.burst_limit,
                    qtp.priority,
                    qtp.status,
                    qtp.description,
                    nr.name as router_name,
                    nr.ip as router_ip,
                    nr.port as router_port,
                    (SELECT COUNT(*) FROM client_queue_assignments WHERE queue_policy_id = qtp.id AND status = 'active') as clients_count
                FROM queue_tree_policies qtp
                LEFT JOIN network_routers nr ON qtp.router_id = nr.id
                $router_filter
                ORDER BY qtp.created_at DESC";
                
                $result = sql($sql);
                $policies = [];
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $policies[] = $row;
                }
                
                echo json_encode([
                    'result' => 'success',
                    'data' => $policies
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Obtener templates disponibles
    public function getTemplates()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $sql = "SELECT * FROM queue_tree_templates WHERE is_active = 1 ORDER BY category, name";
                $result = sql($sql);
                $templates = [];
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $templates[] = $row;
                }
                
                echo json_encode([
                    'result' => 'success',
                    'data' => $templates
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Obtener routers disponibles
    public function getRouters()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $sql = "SELECT id, name, ip, port, status FROM network_routers WHERE status = 'connected' ORDER BY name";
                $result = sql($sql);
                $routers = [];
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $routers[] = $row;
                }
                
                echo json_encode([
                    'result' => 'success',
                    'data' => $routers
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Crear nueva política Queue Tree
    public function createPolicy()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Validar datos requeridos
                $required_fields = ['name', 'router_id', 'target', 'max_limit'];
                foreach ($required_fields as $field) {
                    if (empty($_POST[$field])) {
                        echo json_encode([
                            'result' => 'error',
                            'message' => "Campo requerido: $field"
                        ]);
                        return;
                    }
                }

                // Verificar que no exista una política con el mismo nombre
                $check_sql = "SELECT id FROM queue_tree_policies WHERE name = '" . mysqli_real_escape_string(sql("SELECT 1"), $_POST['name']) . "'";
                if (sqlCount($check_sql) > 0) {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Ya existe una política con ese nombre'
                    ]);
                    return;
                }

                // Insertar política
                $data = [
                    'name' => mysqli_real_escape_string(sql("SELECT 1"), $_POST['name']),
                    'router_id' => intval($_POST['router_id']),
                    'parent_queue' => isset($_POST['parent_queue']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['parent_queue']) : 'global',
                    'target' => mysqli_real_escape_string(sql("SELECT 1"), $_POST['target']),
                    'max_limit' => mysqli_real_escape_string(sql("SELECT 1"), $_POST['max_limit']),
                    'burst_limit' => isset($_POST['burst_limit']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['burst_limit']) : NULL,
                    'burst_threshold' => isset($_POST['burst_threshold']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['burst_threshold']) : NULL,
                    'burst_time' => isset($_POST['burst_time']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['burst_time']) : NULL,
                    'priority' => isset($_POST['priority']) ? intval($_POST['priority']) : 4,
                    'queue_type' => isset($_POST['queue_type']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['queue_type']) : 'default',
                    'packet_mark' => isset($_POST['packet_mark']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['packet_mark']) : NULL,
                    'connection_mark' => isset($_POST['connection_mark']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['connection_mark']) : NULL,
                    'dscp' => isset($_POST['dscp']) ? intval($_POST['dscp']) : NULL,
                    'description' => isset($_POST['description']) ? mysqli_real_escape_string(sql("SELECT 1"), $_POST['description']) : NULL
                ];

                $sql = "INSERT INTO queue_tree_policies (
                    name, router_id, parent_queue, target, max_limit, burst_limit, 
                    burst_threshold, burst_time, priority, queue_type, packet_mark, 
                    connection_mark, dscp, description, status
                ) VALUES (
                    '{$data['name']}', {$data['router_id']}, " . 
                    ($data['parent_queue'] ? "'{$data['parent_queue']}'" : "NULL") . ", 
                    '{$data['target']}', '{$data['max_limit']}', " .
                    ($data['burst_limit'] ? "'{$data['burst_limit']}'" : "NULL") . ", " .
                    ($data['burst_threshold'] ? "'{$data['burst_threshold']}'" : "NULL") . ", " .
                    ($data['burst_time'] ? "'{$data['burst_time']}'" : "NULL") . ", 
                    {$data['priority']}, '{$data['queue_type']}', " .
                    ($data['packet_mark'] ? "'{$data['packet_mark']}'" : "NULL") . ", " .
                    ($data['connection_mark'] ? "'{$data['connection_mark']}'" : "NULL") . ", " .
                    ($data['dscp'] ? "{$data['dscp']}" : "NULL") . ", " .
                    ($data['description'] ? "'{$data['description']}'" : "NULL") . ", 'active'
                )";

                if (sql($sql)) {
                    echo json_encode([
                        'result' => 'success',
                        'message' => 'Política Queue Tree creada exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Error al crear la política'
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Sincronizar política con MikroTik
    public function syncPolicy()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $policy_id = intval($_POST['policy_id']);
                
                // Obtener datos de la política
                $sql = "SELECT qtp.*, nr.ip, nr.port, nr.username, nr.password, nr.api_type 
                        FROM queue_tree_policies qtp
                        JOIN network_routers nr ON qtp.router_id = nr.id
                        WHERE qtp.id = $policy_id";
                        
                $result = sqlArray($sql);
                if (!$result) {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Política no encontrada'
                    ]);
                    return;
                }

                // Conectar al router
                require_once('Libraries/MikroTik/RouterFactory.php');
                $router = RouterFactory::create(
                    $result['ip'],
                    $result['port'],
                    $result['username'],
                    decrypt_aes($result['password'], SECRET_IV),
                    $result['api_type'] ?? 'auto'
                );

                if (!$router) {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'No se pudo conectar al router'
                    ]);
                    return;
                }

                // Crear Queue Tree en MikroTik
                $params = [
                    'name' => $result['name'],
                    'parent' => $result['parent_queue'] ?? 'global',
                    'max-limit' => $result['max_limit'],
                    'priority' => $result['priority'],
                    'queue-type' => $result['queue_type'],
                    'comment' => $result['description'] ?? ''
                ];

                if ($result['burst_limit']) $params['burst-limit'] = $result['burst_limit'];
                if ($result['burst_threshold']) $params['burst-threshold'] = $result['burst_threshold'];
                if ($result['burst_time']) $params['burst-time'] = $result['burst_time'];
                if ($result['packet_mark']) $params['packet-mark'] = $result['packet_mark'];
                if ($result['connection_mark']) $params['connection-mark'] = $result['connection_mark'];

                $sync_result = $router->APICreateQueueTree($params);

                if ($sync_result->success) {
                    // Actualizar estado de la política
                    sql("UPDATE queue_tree_policies SET status = 'active' WHERE id = $policy_id");
                    
                    echo json_encode([
                        'result' => 'success',
                        'message' => 'Política sincronizada exitosamente con MikroTik'
                    ]);
                } else {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Error al sincronizar: ' . ($sync_result->error ?? 'Error desconocido')
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Eliminar política
    public function deletePolicy()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $policy_id = intval($_POST['policy_id']);
                
                // Verificar si hay clientes asignados
                $clients_count = sqlCount("SELECT id FROM client_queue_assignments WHERE queue_policy_id = $policy_id AND status = 'active'");
                
                if ($clients_count > 0) {
                    echo json_encode([
                        'result' => 'error',
                        'message' => "No se puede eliminar la política. Tiene $clients_count clientes asignados."
                    ]);
                    return;
                }

                // Eliminar política
                if (sql("DELETE FROM queue_tree_policies WHERE id = $policy_id")) {
                    echo json_encode([
                        'result' => 'success',
                        'message' => 'Política eliminada exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Error al eliminar la política'
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Obtener clientes para asignación
    public function getClients()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $sql = "SELECT 
                    c.id,
                    c.names,
                    c.surnames,
                    c.email,
                    c.phone,
                    c.address,
                    c.ip_address,
                    c.status,
                    p.name as product_name,
                    cqa.queue_policy_id,
                    cqa.upload_limit,
                    cqa.download_limit,
                    cqa.sync_status,
                    qtp.name as policy_name
                FROM clients c
                LEFT JOIN products p ON c.productid = p.id
                LEFT JOIN client_queue_assignments cqa ON c.id = cqa.client_id AND cqa.status = 'active'
                LEFT JOIN queue_tree_policies qtp ON cqa.queue_policy_id = qtp.id
                WHERE c.status IN ('active', 'suspended')
                ORDER BY c.names, c.surnames";
                
                $result = sql($sql);
                $clients = [];
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $clients[] = $row;
                }
                
                echo json_encode([
                    'result' => 'success',
                    'data' => $clients
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Asignar política a cliente
    public function assignPolicy()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $client_id = intval($_POST['client_id']);
                $policy_id = intval($_POST['policy_id']);
                $upload_limit = mysqli_real_escape_string(sql("SELECT 1"), $_POST['upload_limit']);
                $download_limit = mysqli_real_escape_string(sql("SELECT 1"), $_POST['download_limit']);
                $priority = isset($_POST['priority']) ? intval($_POST['priority']) : 4;

                // Obtener IP del cliente
                $client = sqlArray("SELECT ip_address FROM clients WHERE id = $client_id");
                if (!$client || empty($client['ip_address'])) {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Cliente no encontrado o sin IP asignada'
                    ]);
                    return;
                }

                // Verificar si ya tiene asignación activa
                $existing = sqlCount("SELECT id FROM client_queue_assignments WHERE client_id = $client_id AND status = 'active'");
                if ($existing > 0) {
                    // Actualizar asignación existente
                    $sql = "UPDATE client_queue_assignments SET 
                            queue_policy_id = $policy_id,
                            upload_limit = '$upload_limit',
                            download_limit = '$download_limit',
                            priority = $priority,
                            sync_status = 'pending'
                            WHERE client_id = $client_id AND status = 'active'";
                } else {
                    // Crear nueva asignación
                    $sql = "INSERT INTO client_queue_assignments (
                        client_id, queue_policy_id, client_ip, upload_limit, 
                        download_limit, priority, status, sync_status
                    ) VALUES (
                        $client_id, $policy_id, '{$client['ip_address']}', 
                        '$upload_limit', '$download_limit', $priority, 'active', 'pending'
                    )";
                }

                if (sql($sql)) {
                    echo json_encode([
                        'result' => 'success',
                        'message' => 'Política asignada exitosamente al cliente'
                    ]);
                } else {
                    echo json_encode([
                        'result' => 'error',
                        'message' => 'Error al asignar la política'
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    // Sincronizar asignaciones con MikroTik
    public function syncAssignments()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Obtener asignaciones pendientes de sincronización
                $sql = "SELECT 
                    cqa.*,
                    qtp.router_id,
                    nr.ip as router_ip,
                    nr.port as router_port,
                    nr.username as router_username,
                    nr.password as router_password,
                    nr.api_type
                FROM client_queue_assignments cqa
                JOIN queue_tree_policies qtp ON cqa.queue_policy_id = qtp.id
                JOIN network_routers nr ON qtp.router_id = nr.id
                WHERE cqa.sync_status = 'pending' AND cqa.status = 'active'";
                
                $result = sql($sql);
                $success_count = 0;
                $error_count = 0;
                $errors = [];

                while ($assignment = mysqli_fetch_assoc($result)) {
                    try {
                        // Conectar al router
                        require_once('Libraries/MikroTik/RouterFactory.php');
                        $router = RouterFactory::create(
                            $assignment['router_ip'],
                            $assignment['router_port'],
                            $assignment['router_username'],
                            decrypt_aes($assignment['router_password'], SECRET_IV),
                            $assignment['api_type'] ?? 'auto'
                        );

                        if (!$router) {
                            throw new Exception("No se pudo conectar al router");
                        }

                        // Configurar burst si existe
                        $options = [
                            'priority' => $assignment['priority'],
                            'parent_interface' => 'global'
                        ];

                        if ($assignment['burst_upload'] && $assignment['burst_download']) {
                            $options['burst_upload'] = $assignment['burst_upload'];
                            $options['burst_download'] = $assignment['burst_download'];
                        }

                        // Crear Queue Tree para el cliente
                        $sync_result = $router->APICreateClientQueueTree(
                            $assignment['client_ip'],
                            $assignment['upload_limit'],
                            $assignment['download_limit'],
                            $options
                        );

                        if ($sync_result->success) {
                            // Actualizar estado de sincronización
                            sql("UPDATE client_queue_assignments SET 
                                sync_status = 'synced', 
                                last_sync = NOW() 
                                WHERE id = {$assignment['id']}");
                            $success_count++;
                        } else {
                            // Registrar error
                            sql("UPDATE client_queue_assignments SET 
                                sync_status = 'error' 
                                WHERE id = {$assignment['id']}");
                            $errors[] = "Cliente {$assignment['client_ip']}: " . ($sync_result->error ?? 'Error desconocido');
                            $error_count++;
                        }

                    } catch (Exception $e) {
                        $errors[] = "Cliente {$assignment['client_ip']}: " . $e->getMessage();
                        $error_count++;
                    }
                }

                $message = "Sincronización completada. Éxito: $success_count";
                if ($error_count > 0) {
                    $message .= ", Errores: $error_count";
                }

                echo json_encode([
                    'result' => $error_count == 0 ? 'success' : 'warning',
                    'message' => $message,
                    'errors' => $errors,
                    'success_count' => $success_count,
                    'error_count' => $error_count
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    'result' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
    
    // Obtener Queue Trees desde los routers MikroTik
    public function get_router_queue_trees()
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
        $res->data = [];
        
        try {
            // Get all active routers
            $routers_query = sql("SELECT * FROM network_routers WHERE (status = 'connected' OR status IS NULL)");
            
            require_once('Libraries/MikroTik/RouterFactory.php');
            
            while ($router_row = mysqli_fetch_array($routers_query, MYSQLI_ASSOC)) {
                try {
                    $router = RouterFactory::create(
                        $router_row["ip"], 
                        $router_row["port"], 
                        $router_row["username"], 
                        decrypt_aes($router_row["password"], SECRET_IV),
                        $router_row["api_type"] ?? 'auto'
                    );
                    
                    if ($router && $router->connected) {
                        $queue_trees = $router->APIListQueueTree();
                        
                        if ($queue_trees->success && isset($queue_trees->data)) {
                            foreach ($queue_trees->data as $qt) {
                                $queue_data = [
                                    'router_id' => $router_row['id'],
                                    'router_name' => $router_row['name'],
                                    'router_ip' => $router_row['ip'] . ':' . $router_row['port'],
                                    'name' => $qt->name ?? 'N/A',
                                    'parent' => $qt->parent ?? 'N/A',
                                    'packet_marks' => $qt->{'packet-marks'} ?? ($qt->{'packet-mark'} ?? 'N/A'),
                                    'limit_at' => $qt->{'limit-at'} ?? 'N/A',
                                    'max_limit' => $qt->{'max-limit'} ?? 'N/A',
                                    'burst_limit' => $qt->{'burst-limit'} ?? 'N/A',
                                    'burst_threshold' => $qt->{'burst-threshold'} ?? 'N/A',
                                    'burst_time' => $qt->{'burst-time'} ?? 'N/A',
                                    'priority' => $qt->priority ?? 'N/A',
                                    'queue' => $qt->queue ?? 'N/A',
                                    'bytes' => $qt->bytes ?? 0,
                                    'packets' => $qt->packets ?? 0,
                                    'queued_bytes' => $qt->{'queued-bytes'} ?? 0,
                                    'queued_packets' => $qt->{'queued-packets'} ?? 0,
                                    'disabled' => isset($qt->disabled) && $qt->disabled === 'true',
                                    'invalid' => isset($qt->invalid) && $qt->invalid === 'true'
                                ];
                                $res->data[] = $queue_data;
                            }
                        }
                    } else {
                        // Router no conectado, agregar entrada de estado
                        $queue_data = [
                            'router_id' => $router_row['id'],
                            'router_name' => $router_row['name'],
                            'router_ip' => $router_row['ip'] . ':' . $router_row['port'],
                            'name' => 'ROUTER NO CONECTADO',
                            'parent' => 'N/A',
                            'packet_marks' => 'N/A',
                            'limit_at' => 'N/A',
                            'max_limit' => 'N/A',
                            'burst_limit' => 'N/A',
                            'burst_threshold' => 'N/A',
                            'burst_time' => 'N/A',
                            'priority' => 'N/A',
                            'queue' => 'N/A',
                            'bytes' => 0,
                            'packets' => 0,
                            'queued_bytes' => 0,
                            'queued_packets' => 0,
                            'disabled' => true,
                            'invalid' => true
                        ];
                        $res->data[] = $queue_data;
                    }
                } catch (Exception $e) {
                    // Continue with next router if one fails
                    error_log("Error getting queue trees from router {$router_row['name']}: " . $e->getMessage());
                    
                    // Agregar entrada de error para este router
                    $queue_data = [
                        'router_id' => $router_row['id'],
                        'router_name' => $router_row['name'],
                        'router_ip' => $router_row['ip'] . ':' . $router_row['port'],
                        'name' => 'ERROR DE CONEXIÓN',
                        'parent' => $e->getMessage(),
                        'packet_marks' => 'N/A',
                        'limit_at' => 'N/A',
                        'max_limit' => 'N/A',
                        'burst_limit' => 'N/A',
                        'burst_threshold' => 'N/A',
                        'burst_time' => 'N/A',
                        'priority' => 'N/A',
                        'queue' => 'N/A',
                        'bytes' => 0,
                        'packets' => 0,
                        'queued_bytes' => 0,
                        'queued_packets' => 0,
                        'disabled' => true,
                        'invalid' => true
                    ];
                    $res->data[] = $queue_data;
                }
            }
            
            $res->result = "success";
            $res->message = "Queue Trees obtenidos correctamente";
            $res->total = count($res->data);
            
        } catch (Exception $e) {
            $res->result = "failed";
            $res->message = "Error al obtener Queue Trees: " . $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
}