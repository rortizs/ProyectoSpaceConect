<?php

/**
 * Database Fixtures Usage Examples
 *
 * This file demonstrates how to use the database fixtures system
 * for different scenarios: development, testing, and QA.
 */

// Ensure the config and autoloader are available
require_once __DIR__ . '/../../../Config/Config.php';
require_once __DIR__ . '/../../../Libraries/Core/Autoload.php';
require_once __DIR__ . '/FixtureManager.php';

echo "=== Database Fixtures Usage Examples ===\n\n";

// Example 1: Quick Setup for Development
echo "1. Quick Setup for Development Environment\n";
echo "-------------------------------------------\n";

try {
    $devManager = FixtureManager::quickSetup('development');
    echo "✓ Development fixtures loaded successfully\n";

    // Get some data for demonstration
    $clientsData = $devManager->getFixtureData('ClientsFixture');
    $plansData = $devManager->getFixtureData('PlansFixture');

    echo "   - Clients loaded: " . count($clientsData['clients'] ?? []) . "\n";
    echo "   - Plans loaded: " . count($plansData['plans'] ?? []) . "\n";

    $devManager->cleanupAll();
    echo "✓ Development fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in development setup: " . $e->getMessage() . "\n\n";
}

// Example 2: Testing Environment with Specific Fixtures
echo "2. Testing Environment - Load Specific Fixtures\n";
echo "------------------------------------------------\n";

try {
    $testManager = new FixtureManager();

    // Load only what we need for testing
    echo "Loading essential data...\n";
    $testManager->loadFixture('EssentialDataFixture');

    echo "Loading plans...\n";
    $testManager->loadFixture('PlansFixture');

    echo "Loading routers...\n";
    $testManager->loadFixture('RouterFixture');

    echo "✓ Test fixtures loaded successfully\n";

    // Demonstrate accessing specific fixture instances
    $plansFixture = $testManager->getFixtureInstance('PlansFixture');
    $activePlans = $plansFixture->getActivePlans();
    $businessPlans = $plansFixture->getBusinessPlans();

    echo "   - Active plans: " . count($activePlans) . "\n";
    echo "   - Business plans: " . count($businessPlans) . "\n";

    $testManager->cleanupAll();
    echo "✓ Test fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in testing setup: " . $e->getMessage() . "\n\n";
}

// Example 3: Complete Data Set for QA
echo "3. Complete Data Set for QA Environment\n";
echo "----------------------------------------\n";

try {
    $qaManager = new FixtureManager();

    echo "Loading complete data set...\n";
    $allData = $qaManager->loadDataSet('complete');

    echo "✓ Complete QA fixtures loaded successfully\n";

    // Generate statistics
    $stats = $qaManager->getStats();
    echo "   - Total fixtures: " . $stats['total_fixtures'] . "\n";
    echo "   - Loaded fixtures: " . $stats['loaded_fixtures'] . "\n";

    // Show detailed statistics
    foreach ($stats['fixture_details'] as $name => $details) {
        if ($details['loaded'] && isset($details['record_counts'])) {
            echo "   - {$name}:\n";
            foreach ($details['record_counts'] as $table => $count) {
                echo "     • {$table}: {$count} records\n";
            }
        }
    }

    $qaManager->cleanupAll();
    echo "✓ QA fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in QA setup: " . $e->getMessage() . "\n\n";
}

// Example 4: Performance Testing with Bulk Data
echo "4. Performance Testing with Bulk Data\n";
echo "--------------------------------------\n";

try {
    // Enable bulk data creation
    define('CREATE_BULK_DATA', true);
    $perfManager = new FixtureManager(true);

    echo "Loading performance fixtures with bulk data...\n";
    $startTime = microtime(true);

    $perfManager->loadDataSet('complete');

    $loadTime = round(microtime(true) - $startTime, 2);
    echo "✓ Performance fixtures loaded in {$loadTime}s\n";

    // Get statistics with bulk data
    $stats = $perfManager->getStats();
    echo "   - Bulk data enabled: " . ($stats['bulk_data_enabled'] ? 'Yes' : 'No') . "\n";

    // Show record counts
    foreach ($stats['fixture_details'] as $name => $details) {
        if ($details['loaded'] && isset($details['record_counts'])) {
            $totalRecords = array_sum($details['record_counts']);
            echo "   - {$name}: {$totalRecords} total records\n";
        }
    }

    $perfManager->cleanupAll();
    echo "✓ Performance fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in performance setup: " . $e->getMessage() . "\n\n";
}

// Example 5: Validation and Error Handling
echo "5. Validation and Error Handling\n";
echo "---------------------------------\n";

try {
    $validationManager = new FixtureManager();
    $validationManager->loadDataSet('standard');

    // Validate fixture integrity
    $issues = $validationManager->validateIntegrity();

    if (empty($issues)) {
        echo "✓ All fixtures passed integrity validation\n";
    } else {
        echo "⚠ Issues found during validation:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }

    // Generate and save report
    $report = $validationManager->generateReport();
    $reportFile = __DIR__ . '/validation_report.md';
    file_put_contents($reportFile, $report);
    echo "✓ Validation report saved to: {$reportFile}\n";

    $validationManager->cleanupAll();
    echo "✓ Validation fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in validation: " . $e->getMessage() . "\n\n";
}

// Example 6: Working with Specific Data
echo "6. Working with Specific Fixture Data\n";
echo "--------------------------------------\n";

try {
    $dataManager = FixtureManager::quickSetup('standard');

    // Get specific clients
    $clientsFixture = $dataManager->getFixtureInstance('ClientsFixture');
    $activeClients = $clientsFixture->getActiveClients();
    $suspendedClients = $clientsFixture->getSuspendedClients();
    $businessClients = $clientsFixture->getBusinessClients();

    echo "✓ Client data analysis:\n";
    echo "   - Active clients: " . count($activeClients) . "\n";
    echo "   - Suspended clients: " . count($suspendedClients) . "\n";
    echo "   - Business clients: " . count($businessClients) . "\n";

    // Get billing statistics
    $billingFixture = $dataManager->getFixtureInstance('BillingFixture');
    $billingStats = $billingFixture->getBillingStats();

    echo "✓ Billing statistics:\n";
    echo "   - Total bills: " . $billingStats['total_bills'] . "\n";
    echo "   - Total payments: " . $billingStats['total_payments'] . "\n";
    echo "   - Total billed: $" . number_format($billingStats['total_billed'], 2) . "\n";
    echo "   - Total paid: $" . number_format($billingStats['total_paid'], 2) . "\n";

    // Get router statistics
    $routerFixture = $dataManager->getFixtureInstance('RouterFixture');
    $routerStats = $routerFixture->getRouterStats();

    echo "✓ Router statistics:\n";
    echo "   - Total routers: " . $routerStats['total'] . "\n";
    echo "   - By status:\n";
    foreach ($routerStats['by_status'] as $status => $count) {
        echo "     • {$status}: {$count}\n";
    }

    $dataManager->cleanupAll();
    echo "✓ Data analysis fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in data analysis: " . $e->getMessage() . "\n\n";
}

// Example 7: Export and Backup
echo "7. Data Export and Backup\n";
echo "--------------------------\n";

try {
    $exportManager = FixtureManager::quickSetup('standard');

    // Export fixture data to JSON
    $exportFile = __DIR__ . '/fixture_export_' . date('Y-m-d_H-i-s') . '.json';
    $exported = $exportManager->exportToJson($exportFile);

    if ($exported) {
        echo "✓ Fixture data exported to: {$exportFile}\n";
        echo "   File size: " . round(filesize($exportFile) / 1024, 2) . " KB\n";
    } else {
        echo "✗ Failed to export fixture data\n";
    }

    // Create database snapshot (placeholder)
    $snapshotCreated = $exportManager->createSnapshot('example_usage');
    if ($snapshotCreated) {
        echo "✓ Database snapshot created\n";
    }

    $exportManager->cleanupAll();
    echo "✓ Export fixtures cleaned up\n\n";

} catch (Exception $e) {
    echo "✗ Error in export: " . $e->getMessage() . "\n\n";
}

echo "=== All Examples Completed ===\n\n";

echo "Usage Summary:\n";
echo "- Use FixtureManager::quickSetup() for common scenarios\n";
echo "- Use specific loadFixture() or loadDataSet() for custom needs\n";
echo "- Always call cleanupAll() when finished\n";
echo "- Enable bulk data for performance testing\n";
echo "- Use validation to catch issues early\n";
echo "- Export data for analysis or backup\n\n";

echo "For more information, see README.md in this directory.\n";