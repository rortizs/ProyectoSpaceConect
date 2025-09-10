<?php
// Debug script for content filter errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== Content Filter Debug ===\n\n";

try {
    // Include required files
    include 'Config/Config.php';
    include 'Libraries/Core/Autoload.php';
    
    echo "1. Config and autoloader loaded successfully\n";
    
    // Test database connection
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$connection) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    echo "2. Database connection successful\n";
    
    // Check if content filter tables exist
    $tables = [
        'content_filter_categories',
        'content_filter_domains', 
        'content_filter_policies',
        'content_filter_policy_categories',
        'content_filter_client_policies',
        'content_filter_custom_domains',
        'content_filter_logs'
    ];
    
    echo "3. Checking database tables:\n";
    foreach ($tables as $table) {
        $result = mysqli_query($connection, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "   ✓ $table exists\n";
        } else {
            echo "   ✗ $table missing\n";
        }
    }
    
    // Try to include core files
    echo "\n4. Testing core file includes:\n";
    
    if (file_exists('Libraries/Core/Mysql.php')) {
        include 'Libraries/Core/Mysql.php';
        echo "   ✓ Mysql.php included\n";
    } else {
        echo "   ✗ Mysql.php not found\n";
    }
    
    if (file_exists('Services/BaseService.php')) {
        include 'Services/BaseService.php';
        echo "   ✓ BaseService.php included\n";
    } else {
        echo "   ✗ BaseService.php not found\n";
    }
    
    if (file_exists('Services/ContentFilterService.php')) {
        include 'Services/ContentFilterService.php';
        echo "   ✓ ContentFilterService.php included\n";
    } else {
        echo "   ✗ ContentFilterService.php not found\n";
    }
    
    if (file_exists('Models/ContentfilterModel.php')) {
        include 'Models/ContentfilterModel.php';
        echo "   ✓ ContentfilterModel.php included\n";
    } else {
        echo "   ✗ ContentfilterModel.php not found\n";
    }
    
    // Test service instantiation
    echo "\n5. Testing service instantiation:\n";
    
    try {
        $contentFilterService = new ContentFilterService();
        echo "   ✓ ContentFilterService instantiated\n";
    } catch (Exception $e) {
        echo "   ✗ ContentFilterService error: " . $e->getMessage() . "\n";
    }
    
    try {
        $contentfilterModel = new ContentfilterModel();
        echo "   ✓ ContentfilterModel instantiated\n";
    } catch (Exception $e) {
        echo "   ✗ ContentfilterModel error: " . $e->getMessage() . "\n";
    }
    
    echo "\n6. Testing basic queries:\n";
    
    // Test basic database query
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM content_filter_categories");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "   ✓ Categories count: " . $row['count'] . "\n";
    } else {
        echo "   ✗ Categories query failed: " . mysqli_error($connection) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check if all required constants are defined
echo "\n7. Checking required constants:\n";
$required_constants = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME', 'SECRET_IV'];
foreach ($required_constants as $const) {
    if (defined($const)) {
        echo "   ✓ $const defined\n";
    } else {
        echo "   ✗ $const not defined\n";
    }
}

echo "\n=== Debug Complete ===\n";
?>