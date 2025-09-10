<?php
// Test connection to router 190.56.14.34
include 'Libraries/MikroTik/Router.php';

$host = '190.56.14.34';
$port1 = 8728; // HTTP API
$port2 = 8729; // HTTPS API

// Test common credentials
$credentials = [
    ['admin', ''],
    ['admin', 'admin'],
    ['admin', '123456'],
    ['usuario', 'password'],
    ['mikrotik', 'mikrotik']
];

echo "Testing connection to router $host\n\n";

// Test socket connectivity first
echo "Testing socket connectivity:\n";
$connection1 = @fsockopen($host, $port1, $errno1, $errstr1, 5);
$connection2 = @fsockopen($host, $port2, $errno2, $errstr2, 5);

if ($connection1) {
    echo "✓ Port $port1 (HTTP API) is open\n";
    fclose($connection1);
} else {
    echo "✗ Port $port1 (HTTP API) connection failed: $errstr1\n";
}

if ($connection2) {
    echo "✓ Port $port2 (HTTPS API) is open\n";
    fclose($connection2);
} else {
    echo "✗ Port $port2 (HTTPS API) connection failed: $errstr2\n";
}

echo "\nTesting API authentication:\n";

foreach ($credentials as $cred) {
    $username = $cred[0];
    $password = $cred[1];
    
    echo "\nTrying credentials: $username / " . ($password ? $password : '(empty)') . "\n";
    
    // Test with HTTP API (port 8728)
    echo "  HTTP API (8728): ";
    $router1 = new Router($host, $port1, $username, $password, false);
    $test1 = $router1->APIQuickTest();
    
    if ($test1->success) {
        $resources1 = $router1->APIGetSystemResources();
        if ($resources1->success) {
            echo "✓ SUCCESS - API accessible\n";
            echo "    Router Identity: " . $router1->RequestBuilder("system/identity", "GET")->data->name . "\n";
            echo "    Board Name: " . $resources1->data->{'board-name'} . "\n";
            echo "    Version: " . $resources1->data->version . "\n";
            break; // Exit loop if successful
        } else {
            echo "✗ Socket OK but API failed: " . $resources1->message . "\n";
        }
    } else {
        echo "✗ Socket connection failed\n";
    }
    
    // Test with HTTPS API (port 8729)
    echo "  HTTPS API (8729): ";
    $router2 = new Router($host, $port2, $username, $password, false);
    $test2 = $router2->APIQuickTest();
    
    if ($test2->success) {
        $resources2 = $router2->APIGetSystemResources();
        if ($resources2->success) {
            echo "✓ SUCCESS - API accessible\n";
            echo "    Router Identity: " . $router2->RequestBuilder("system/identity", "GET")->data->name . "\n";
            echo "    Board Name: " . $resources2->data->{'board-name'} . "\n";
            echo "    Version: " . $resources2->data->version . "\n";
            break; // Exit loop if successful
        } else {
            echo "✗ Socket OK but API failed: " . $resources2->message . "\n";
        }
    } else {
        echo "✗ Socket connection failed\n";
    }
}

echo "\n\nNote: If all authentication attempts failed, please check:\n";
echo "1. The actual username/password for this router\n";
echo "2. If API service is enabled on the router\n";
echo "3. If the user has API permissions\n";
echo "4. If there are firewall rules blocking API access\n";
?>