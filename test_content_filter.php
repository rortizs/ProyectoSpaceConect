<?php
// Test Content Filter Functionality
include 'Config/Config.php';
include 'Libraries/Core/Mysql.php';
include 'Services/BaseService.php';
include 'Services/ContentFilterService.php';
include 'Models/ContentfilterModel.php';

echo "=== Content Filter Test ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$connection) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    echo "✓ Database connection successful\n\n";

    // Test content filter model
    echo "2. Testing ContentFilterModel...\n";
    $model = new ContentfilterModel();
    
    // Test categories
    $categories = $model->getCategories();
    echo "✓ Found " . count($categories) . " content categories\n";
    
    foreach ($categories as $category) {
        echo "  - {$category['name']}: {$category['description']}\n";
    }
    
    // Test policies
    $policies = $model->getPolicies();
    echo "✓ Found " . count($policies) . " filtering policies\n";
    
    foreach ($policies as $policy) {
        echo "  - {$policy['name']}: {$policy['description']}\n";
    }
    
    // Test content filter service
    echo "\n3. Testing ContentFilterService...\n";
    $service = new ContentFilterService();
    
    $stats = $model->getFilteringStats();
    echo "✓ Statistics retrieved:\n";
    echo "  - Total policies: {$stats['total_policies']}\n";
    echo "  - Filtered clients: {$stats['filtered_clients']}\n";
    echo "  - Total categories: {$stats['total_categories']}\n";
    echo "  - Blocked domains: {$stats['blocked_domains']}\n";
    
    // Test domain retrieval by categories
    echo "\n4. Testing domain retrieval...\n";
    $category_ids = array_column($categories, 'id');
    if (!empty($category_ids)) {
        $domains = $service->getDomainsByCategories(array_slice($category_ids, 0, 2));
        echo "✓ Retrieved " . count($domains) . " domains for first 2 categories\n";
        echo "  Sample domains: " . implode(', ', array_slice($domains, 0, 5)) . "\n";
    }
    
    // Test clients without filtering
    echo "\n5. Testing unfiltered clients query...\n";
    $unfiltered = $model->getClientsWithoutFiltering();
    echo "✓ Found " . count($unfiltered) . " clients without content filtering\n";
    
    echo "\n=== All Tests Passed! ===\n";
    echo "\nContent Filtering Module is ready for testing with MikroTik router.\n";
    echo "\nNext steps:\n";
    echo "1. Configure proper credentials for router 190.56.14.34\n";
    echo "2. Enable REST API on the MikroTik router\n";
    echo "3. Test domain blocking functionality\n";
    echo "4. Apply content filtering policies to test clients\n";

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
}

// Clean up
if (isset($connection)) {
    mysqli_close($connection);
}
?>