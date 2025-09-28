<?php
// Router Factory - Automatically detects RouterOS version and returns appropriate adapter
require_once(__DIR__ . '/Router.php');          // REST API for RouterOS 7.x+
require_once(__DIR__ . '/RouterLegacy.php');    // Legacy API for RouterOS 6.x

class RouterFactory
{
    /**
     * Create appropriate router instance based on RouterOS version
     * 
     * @param string $host Router IP address
     * @param int $port API port (8728 or 8729)
     * @param string $user Username
     * @param string $password Password
     * @param string $force_type Force specific API type ('auto', 'legacy', 'rest')
     * @return Router|RouterLegacy|false
     */
    public static function create($host, $port, $user, $password, $force_type = 'auto')
    {
        if ($force_type === 'legacy') {
            return new RouterLegacy($host, $port, $user, $password, true);
        }
        
        if ($force_type === 'rest') {
            return new Router($host, $port, $user, $password, true);
        }
        
        // Auto-detection (default)
        return self::autoDetectAndCreate($host, $port, $user, $password);
    }
    
    /**
     * Auto-detect RouterOS version and return appropriate adapter
     */
    private static function autoDetectAndCreate($host, $port, $user, $password)
    {
        // Strategy 1: Try Legacy API first (more reliable for version detection)
        $legacy_router = new RouterLegacy($host, $port, $user, $password, true);
        
        if ($legacy_router->connected) {
            $resources = $legacy_router->APIGetSystemResources();
            
            if ($resources->success && isset($resources->data->version)) {
                $version = $resources->data->version;
                $version_major = self::parseVersion($version);
                
                // Store detection result
                self::updateRouterVersion($host, $version, $version_major >= 7 ? 'rest' : 'legacy');
                
                if ($version_major >= 7) {
                    // RouterOS 7.x+ - use REST API
                    $legacy_router->disconnect();
                    return new Router($host, $port, $user, $password, true);
                } else {
                    // RouterOS 6.x - use Legacy API
                    return $legacy_router;
                }
            }
            
            // If we can't get version but legacy works, use legacy
            return $legacy_router;
        }
        
        // Strategy 2: Try REST API if Legacy failed
        $rest_router = new Router($host, $port, $user, $password, true);
        
        if ($rest_router->connected) {
            $resources = $rest_router->APIGetSystemResources();
            
            if ($resources->success) {
                // Assume RouterOS 7.x+ if REST API works
                self::updateRouterVersion($host, '7.x+', 'rest');
                return $rest_router;
            }
        }
        
        // Both failed
        return false;
    }
    
    /**
     * Parse version string to get major version number
     * Examples: "6.49.15" -> 6, "7.1.5" -> 7
     */
    private static function parseVersion($version_string)
    {
        if (preg_match('/^(\d+)\./', $version_string, $matches)) {
            return intval($matches[1]);
        }
        return 6; // Default to 6 if can't parse
    }
    
    /**
     * Update router version and API type in database
     */
    private static function updateRouterVersion($host, $version, $api_type)
    {
        try {
            require_once(__DIR__ . '/../../Libraries/XEPanel/mysqli_functions.php');
            
            $host_safe = addslashes($host);
            $version_safe = addslashes($version);
            $api_type_safe = addslashes($api_type);
            
            $update_sql = "UPDATE network_routers SET 
                          routeros_version = '$version_safe',
                          api_type = '$api_type_safe'
                          WHERE ip = '$host_safe'";
            
            sql($update_sql);
        } catch (Exception $e) {
            // Silent fail - version tracking is not critical
            error_log("RouterFactory: Failed to update version for $host: " . $e->getMessage());
        }
    }
    
    /**
     * Create router from database configuration
     */
    public static function createFromDatabase($router_id)
    {
        require_once(__DIR__ . '/../../Libraries/XEPanel/mysqli_functions.php');
        require_once(__DIR__ . '/../../Libraries/NetworkUtils/utils.php');
        
        $router_result = sql("SELECT * FROM network_routers WHERE id = " . intval($router_id));
        
        if (!$router_result || mysqli_num_rows($router_result) == 0) {
            return false;
        }
        
        $config = mysqli_fetch_array($router_result, MYSQLI_ASSOC);
        
        return self::create(
            $config['ip'],
            $config['port'],
            $config['username'],
            decrypt_aes($config['password'], SECRET_IV),
            $config['api_type'] ?? 'auto'
        );
    }
    
    /**
     * Get router info including detected version
     */
    public static function getRouterInfo($host, $port, $user, $password)
    {
        $router = self::create($host, $port, $user, $password, 'auto');
        
        if (!$router || !$router->connected) {
            return [
                'connected' => false,
                'version' => null,
                'api_type' => null,
                'error' => 'Connection failed'
            ];
        }
        
        $resources = $router->APIGetSystemResources();
        
        if ($resources->success) {
            return [
                'connected' => true,
                'version' => $resources->data->version ?? 'Unknown',
                'api_type' => ($router instanceof RouterLegacy) ? 'legacy' : 'rest',
                'board_name' => $resources->data->{'board-name'} ?? 'Unknown',
                'cpu_load' => $resources->data->{'cpu-load'} ?? 0,
                'free_memory' => $resources->data->{'free-memory'} ?? 0
            ];
        }
        
        return [
            'connected' => true,
            'version' => 'Unknown',
            'api_type' => ($router instanceof RouterLegacy) ? 'legacy' : 'rest',
            'error' => 'Could not get system info'
        ];
    }
}
?>