<?php
// Test content filter in web context (simulating the web environment)
require_once("Config/Config.php");
require_once("Helpers/Helpers.php");
require_once("Helpers/SystemInfo.php");
require_once('Libraries/XEPanel/mysqli_functions.php');
require_once('Libraries/NetworkUtils/utils.php');
require_once('Libraries/MikroTik/Router.php');
require_once('Libraries/MikroTik/CronjobMethods.php');
require_once("Libraries/Emitter2/ObserverInterface.php");
require_once("Libraries/Emitter2/EventManager.php");
require_once("Libraries/Core/Autoload.php");
require_once("Libraries/Core/Load.php");

echo "=== Web Context Test ===\n\n";

try {
    echo "1. Testing basic database functions...\n";
    
    // Test basic sql function
    $result = sql("SELECT COUNT(*) as count FROM content_filter_categories");
    $row = mysqli_fetch_array($result);
    echo "   ✓ Categories count: " . $row['count'] . "\n";
    
    echo "\n2. Testing ContentFilterService...\n";
    $contentFilterService = new ContentFilterService();
    echo "   ✓ ContentFilterService instantiated\n";
    
    $categories = $contentFilterService->getCategories();
    echo "   ✓ Got " . count($categories) . " categories\n";
    
    echo "\n3. Testing ContentfilterModel...\n";
    $model = new ContentfilterModel();
    echo "   ✓ ContentfilterModel instantiated\n";
    
    $stats = $model->getFilteringStats();
    echo "   ✓ Stats retrieved\n";
    echo "     - Total policies: {$stats['total_policies']}\n";
    echo "     - Filtered clients: {$stats['filtered_clients']}\n";
    echo "     - Total categories: {$stats['total_categories']}\n";
    echo "     - Blocked domains: {$stats['blocked_domains']}\n";
    
    echo "\n✅ All tests passed! Content filter module is ready.\n";
    echo "\nYou can now access: http://online.test/network/contentfilter\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>