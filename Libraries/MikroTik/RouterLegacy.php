<?php
// Legacy Router adapter for RouterOS 6.x
require_once(__DIR__ . '/../RouterOS_API.php');

class RouterLegacy
{
    private $api;
    private $host;
    private $port;
    private $user;
    private $password;
    
    public $connected = false;

    public function __construct($host, $port, $user, $password, $try_connect = true)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        
        $this->api = new RouterosAPI();
        $this->api->port = $port;
        $this->api->timeout = 10;
        
        if ($try_connect) {
            $this->connect();
        }
    }
    
    private function connect()
    {
        try {
            if ($this->api->connect($this->host, $this->user, $this->password)) {
                $this->connected = true;
                return true;
            }
        } catch (Exception $e) {
            $this->connected = false;
        }
        return false;
    }
    
    public function APIQuickTest()
    {
        $res = (object) array();
        $res->success = $this->connected;
        return $res;
    }
    
    public function APIGetSystemResources()
    {
        $res = (object) array();
        
        if (!$this->connected) {
            $res->success = false;
            $res->error = "Not connected";
            return $res;
        }
        
        try {
            $response = $this->api->comm('/system/resource/print');
            
            if (!empty($response) && is_array($response)) {
                $resource = $response[0];
                
                $res->success = true;
                $res->data = (object) array(
                    'cpu-load' => isset($resource['cpu-load']) ? rtrim($resource['cpu-load'], '%') : '0',
                    'free-memory' => isset($resource['free-memory']) ? $this->parseMemory($resource['free-memory']) : 0,
                    'total-memory' => isset($resource['total-memory']) ? $this->parseMemory($resource['total-memory']) : 0,
                    'board-name' => $resource['board-name'] ?? 'Unknown',
                    'architecture-name' => $resource['architecture-name'] ?? 'Unknown',
                    'version' => $resource['version'] ?? 'Unknown'
                );
            } else {
                $res->success = false;
                $res->error = "No system resource data";
            }
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    private function parseMemory($memString)
    {
        // Convert memory strings like "964.2MiB" to bytes
        if (preg_match('/([0-9.]+)([KMGT]?i?B?)/', $memString, $matches)) {
            $value = floatval($matches[1]);
            $unit = strtoupper($matches[2]);
            
            switch ($unit) {
                case 'KIB':
                case 'KB':
                    return $value * 1024;
                case 'MIB':
                case 'MB':
                    return $value * 1024 * 1024;
                case 'GIB':
                case 'GB':
                    return $value * 1024 * 1024 * 1024;
                default:
                    return $value;
            }
        }
        return 0;
    }
    
    // DNS Static methods for content filtering
    public function APIGetDNSStatic($domain)
    {
        $res = (object) array();
        
        if (!$this->connected) {
            $res->success = false;
            $res->error = "Not connected";
            return $res;
        }
        
        try {
            $response = $this->api->comm('/ip/dns/static/print', array(
                '?name=' . $domain
            ));
            
            $res->success = true;
            $res->data = is_array($response) ? $response : array();
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    public function APIAddDNSBlock($domain, $redirect_ip = "0.0.0.0")
    {
        $res = (object) array();
        
        if (!$this->connected) {
            $res->success = false;
            $res->error = "Not connected";
            return $res;
        }
        
        try {
            // Check if domain already exists
            $existing = $this->APIGetDNSStatic($domain);
            if ($existing->success && count($existing->data) > 0) {
                $res->success = true;
                $res->message = "Domain already blocked";
                return $res;
            }
            
            // Add DNS static entry
            $response = $this->api->comm('/ip/dns/static/add', array(
                'name' => $domain,
                'address' => $redirect_ip,
                'comment' => 'Content Filter Block'
            ));
            
            $res->success = true;
            $res->data = $response;
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    public function APIRemoveDNSBlock($domain)
    {
        $res = (object) array();
        
        if (!$this->connected) {
            $res->success = false;
            $res->error = "Not connected";
            return $res;
        }
        
        try {
            // Find the DNS entry
            $entries = $this->APIGetDNSStatic($domain);
            if ($entries->success && count($entries->data) > 0) {
                foreach ($entries->data as $entry) {
                    if (isset($entry['.id'])) {
                        $this->api->comm('/ip/dns/static/remove', array(
                            '.id' => $entry['.id']
                        ));
                    }
                }
            }
            
            $res->success = true;
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    // Bulk domain blocking methods
    public function APIBlockDomainsForClient($client_ip, $domains, $redirect_ip = "0.0.0.0")
    {
        $results = array();
        
        foreach ($domains as $domain) {
            $results[$domain] = $this->APIAddDNSBlock($domain, $redirect_ip);
        }
        
        return $results;
    }
    
    public function APIUnblockDomainsForClient($client_ip, $domains)
    {
        $results = array();
        
        foreach ($domains as $domain) {
            $results[$domain] = $this->APIRemoveDNSBlock($domain);
        }
        
        return $results;
    }
    
    public function disconnect()
    {
        if ($this->connected && $this->api) {
            $this->api->disconnect();
            $this->connected = false;
        }
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }
    
    // QUEUE TREE METHODS para RouterOS Legacy (6.x)
    // Métodos para gestión avanzada de QoS con Queue Tree
    
    /**
     * Listar todos los Queue Trees
     */
    public function APIListQueueTree()
    {
        $res = (object) array();
        try {
            if (!$this->connected) {
                $this->connect();
            }
            
            $response = $this->api->comm('/queue/tree/print');
            $res->success = true;
            $res->data = $response;
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        return $res;
    }
    
    /**
     * Crear nuevo Queue Tree
     */
    public function APICreateQueueTree($params)
    {
        $res = (object) array();
        try {
            if (!$this->connected) {
                $this->connect();
            }
            
            // Parámetros requeridos
            $required = ['name', 'parent', 'max-limit'];
            foreach ($required as $param) {
                if (!isset($params[$param]) || empty($params[$param])) {
                    $res->success = false;
                    $res->error = "Parámetro requerido faltante: $param";
                    return $res;
                }
            }
            
            // Construir comando para API legacy
            $command_params = array();
            $command_params['name'] = $params['name'];
            $command_params['parent'] = $params['parent'];
            $command_params['max-limit'] = $params['max-limit'];
            
            // Parámetros opcionales
            if (isset($params['burst-limit'])) $command_params['burst-limit'] = $params['burst-limit'];
            if (isset($params['burst-threshold'])) $command_params['burst-threshold'] = $params['burst-threshold'];
            if (isset($params['burst-time'])) $command_params['burst-time'] = $params['burst-time'];
            if (isset($params['priority'])) $command_params['priority'] = $params['priority'];
            if (isset($params['queue-type'])) $command_params['queue-type'] = $params['queue-type'];
            if (isset($params['packet-mark'])) $command_params['packet-mark'] = $params['packet-mark'];
            if (isset($params['connection-mark'])) $command_params['connection-mark'] = $params['connection-mark'];
            if (isset($params['comment'])) $command_params['comment'] = $params['comment'];
            
            $response = $this->api->comm('/queue/tree/add', $command_params);
            
            $res->success = true;
            $res->data = $response;
            $res->message = "Queue Tree creado exitosamente";
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        return $res;
    }
    
    /**
     * Actualizar Queue Tree existente
     */
    public function APIUpdateQueueTree($queue_id, $params)
    {
        $res = (object) array();
        try {
            if (!$this->connected) {
                $this->connect();
            }
            
            $command_params = array('.id' => $queue_id);
            
            // Solo incluir parámetros que se van a actualizar
            if (isset($params['name'])) $command_params['name'] = $params['name'];
            if (isset($params['parent'])) $command_params['parent'] = $params['parent'];
            if (isset($params['max-limit'])) $command_params['max-limit'] = $params['max-limit'];
            if (isset($params['burst-limit'])) $command_params['burst-limit'] = $params['burst-limit'];
            if (isset($params['burst-threshold'])) $command_params['burst-threshold'] = $params['burst-threshold'];
            if (isset($params['burst-time'])) $command_params['burst-time'] = $params['burst-time'];
            if (isset($params['priority'])) $command_params['priority'] = $params['priority'];
            if (isset($params['queue-type'])) $command_params['queue-type'] = $params['queue-type'];
            if (isset($params['packet-mark'])) $command_params['packet-mark'] = $params['packet-mark'];
            if (isset($params['connection-mark'])) $command_params['connection-mark'] = $params['connection-mark'];
            if (isset($params['comment'])) $command_params['comment'] = $params['comment'];
            if (isset($params['disabled'])) $command_params['disabled'] = $params['disabled'] ? 'yes' : 'no';
            
            $response = $this->api->comm('/queue/tree/set', $command_params);
            
            $res->success = true;
            $res->data = $response;
            $res->message = "Queue Tree actualizado exitosamente";
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        return $res;
    }
    
    /**
     * Eliminar Queue Tree
     */
    public function APIDeleteQueueTree($queue_id)
    {
        $res = (object) array();
        try {
            if (!$this->connected) {
                $this->connect();
            }
            
            $response = $this->api->comm('/queue/tree/remove', array('.id' => $queue_id));
            
            $res->success = true;
            $res->message = "Queue Tree eliminado exitosamente";
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        return $res;
    }
    
    /**
     * Crear Queue Tree para cliente específico
     */
    public function APICreateClientQueueTree($client_ip, $upload_limit, $download_limit, $options = [])
    {
        $res = (object) array();
        
        try {
            // Nombre único para el queue del cliente
            $queue_name = "client-" . str_replace(['.', ':'], '-', $client_ip);
            $parent_interface = $options['parent_interface'] ?? 'global';
            
            // Parámetros del Queue Tree
            $params = [
                'name' => $queue_name,
                'parent' => $parent_interface,
                'max-limit' => $upload_limit . "/" . $download_limit,
                'priority' => $options['priority'] ?? '4',
                'queue-type' => $options['queue-type'] ?? 'default',
                'comment' => "Queue para cliente IP: $client_ip"
            ];
            
            // Agregar burst si está especificado
            if (isset($options['burst_upload']) && isset($options['burst_download'])) {
                $params['burst-limit'] = $options['burst_upload'] . "/" . $options['burst_download'];
                $params['burst-threshold'] = $upload_limit . "/" . $download_limit;
                $params['burst-time'] = $options['burst-time'] ?? '8s/4s';
            }
            
            // Agregar packet mark si está especificado
            if (isset($options['packet_mark'])) {
                $params['packet-mark'] = $options['packet_mark'];
            }
            
            return $this->APICreateQueueTree($params);
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    /**
     * Eliminar Queue Tree de cliente específico
     */
    public function APIDeleteClientQueueTree($client_ip)
    {
        $res = (object) array();
        
        try {
            // Buscar el queue del cliente
            $queue_name = "client-" . str_replace(['.', ':'], '-', $client_ip);
            $queues = $this->APIListQueueTree();
            
            if ($queues->success && is_array($queues->data)) {
                foreach ($queues->data as $queue) {
                    if (isset($queue['name']) && $queue['name'] === $queue_name) {
                        return $this->APIDeleteQueueTree($queue['.id']);
                    }
                }
            }
            
            $res->success = false;
            $res->error = "Queue Tree no encontrado para cliente IP: $client_ip";
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
    
    /**
     * Sincronizar múltiples Queue Trees para clientes
     */
    public function APISyncClientQueues($clients_data)
    {
        $res = (object) array();
        $results = [];
        
        try {
            foreach ($clients_data as $client) {
                $client_ip = $client['ip'];
                $upload = $client['upload_limit'];
                $download = $client['download_limit'];
                $options = $client['options'] ?? [];
                
                // Eliminar queue existente si existe
                $this->APIDeleteClientQueueTree($client_ip);
                
                // Crear nuevo queue
                $create_result = $this->APICreateClientQueueTree($client_ip, $upload, $download, $options);
                $results[$client_ip] = $create_result;
            }
            
            $res->success = true;
            $res->data = $results;
            $res->message = "Sincronización de Queue Trees completada";
            
        } catch (Exception $e) {
            $res->success = false;
            $res->error = $e->getMessage();
        }
        
        return $res;
    }
}
?>