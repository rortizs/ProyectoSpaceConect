<?php
/**
 * PHPUnit Bootstrap File
 * Initializes the testing environment for the ISP Management System
 */

// Check if running in test environment
if (!defined('TESTING_MODE')) {
    define('TESTING_MODE', true);
}

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load the application autoloader (if exists)
if (file_exists(__DIR__ . '/../Libraries/Core/Autoload.php')) {
    require_once __DIR__ . '/../Libraries/Core/Autoload.php';
}

// Load test configuration
if (file_exists(__DIR__ . '/config/test_config.php')) {
    require_once __DIR__ . '/config/test_config.php';
}

// Load base test classes
if (file_exists(__DIR__ . '/Support/BaseTestCase.php')) {
    require_once __DIR__ . '/Support/BaseTestCase.php';
}
if (file_exists(__DIR__ . '/Support/DatabaseTestCase.php')) {
    require_once __DIR__ . '/Support/DatabaseTestCase.php';
}
if (file_exists(__DIR__ . '/Support/MikroTikTestCase.php')) {
    require_once __DIR__ . '/Support/MikroTikTestCase.php';
}

// Load test traits (if they exist)
if (file_exists(__DIR__ . '/Support/Traits/DatabaseTransactions.php')) {
    require_once __DIR__ . '/Support/Traits/DatabaseTransactions.php';
}
if (file_exists(__DIR__ . '/Support/Traits/MocksExternalServices.php')) {
    require_once __DIR__ . '/Support/Traits/MocksExternalServices.php';
}
if (file_exists(__DIR__ . '/Support/Traits/CreatesTestData.php')) {
    require_once __DIR__ . '/Support/Traits/CreatesTestData.php';
}

// Load test helpers (if they exist)
if (file_exists(__DIR__ . '/Support/Helpers/TestDataFactory.php')) {
    require_once __DIR__ . '/Support/Helpers/TestDataFactory.php';
}
if (file_exists(__DIR__ . '/Support/Helpers/MockBuilder.php')) {
    require_once __DIR__ . '/Support/Helpers/MockBuilder.php';
}
if (file_exists(__DIR__ . '/Support/Helpers/AssertionHelpers.php')) {
    require_once __DIR__ . '/Support/Helpers/AssertionHelpers.php';
}

// Initialize Mockery
if (class_exists('Mockery')) {
    // Register Mockery close function for cleanup
    register_shutdown_function(function() {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
    });
}

// Set timezone for consistent testing
date_default_timezone_set('America/Lima');

// Disable error reporting for cleaner test output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Initialize test database connection (if Mysql class is available)
try {
    if (class_exists('Mysql')) {
        $testDb = new Mysql();
        if (!$testDb->conection()) {
            throw new Exception('Failed to connect to test database');
        }
    } else {
        // Database class not available - tests will run in mock mode
        if (!defined('MOCK_EXTERNAL_SERVICES')) {
            define('MOCK_EXTERNAL_SERVICES', true);
        }
    }
} catch (Exception $e) {
    echo "Warning: Could not establish test database connection: " . $e->getMessage() . "\n";
    if (!defined('MOCK_EXTERNAL_SERVICES')) {
        define('MOCK_EXTERNAL_SERVICES', true);
    }
}

echo "Test environment initialized successfully\n";