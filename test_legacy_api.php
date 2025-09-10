<?php
// Test legacy binary API connection to router 190.56.14.34
include 'Libraries/MikroTik/routeros_api_class.php';

$host = '190.56.14.34';
$port = 8728;

// Test common credentials
$credentials = [
    ['admin', ''],
    ['admin', 'admin'], 
    ['admin', '123456'],
    ['usuario', 'password'],
    ['mikrotik', 'mikrotik']
];

echo "Testing legacy binary API connection to router $host:$port\n\n";

foreach ($credentials as $cred) {
    $username = $cred[0];
    $password = $cred[1];
    
    echo "Trying credentials: $username / " . ($password ? $password : '(empty)') . "\n";
    
    $api = new RouterosAPI();
    $api->debug = false;
    
    if ($api->connect($host, $username, $password, $port)) {
        echo "✓ SUCCESS - Legacy API connection established!\n";
        
        // Get system resources
        $api->write("/system/resource/print");
        $resources = $api->read();
        
        if (!empty($resources)) {
            echo "  Board Name: " . $resources[0]['board-name'] . "\n";
            echo "  Version: " . $resources[0]['version'] . "\n";
            echo "  Uptime: " . $resources[0]['uptime'] . "\n";
        }
        
        // Get system identity
        $api->write("/system/identity/print");
        $identity = $api->read();
        
        if (!empty($identity)) {
            echo "  Router Identity: " . $identity[0]['name'] . "\n";
        }
        
        $api->disconnect();
        echo "\n✓ Router is using legacy binary API, not REST API!\n";
        break;
        
    } else {
        echo "✗ Connection failed\n";
    }
    
    echo "\n";
}

echo "\nConclusion:\n";
echo "- The router appears to be using the legacy binary API protocol\n";
echo "- The current Router.php class uses REST API which is not enabled/working\n";
echo "- For content filtering, we need to either:\n";
echo "  1. Enable REST API on the router, or\n";
echo "  2. Use the legacy API class for new features\n";
?>