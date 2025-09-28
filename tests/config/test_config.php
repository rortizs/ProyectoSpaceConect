<?php
/**
 * Test Configuration File
 * Override main configuration for testing environment
 */

// Load main configuration first
if (file_exists(__DIR__ . '/../../Config/Config.php')) {
    require_once __DIR__ . '/../../Config/Config.php';
}

// Test Database Configuration
if (!defined('DB_HOST_TEST')) {
    define('DB_HOST_TEST', getenv('DB_HOST_TEST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost'));
}
if (!defined('DB_NAME_TEST')) {
    define('DB_NAME_TEST', getenv('DB_NAME_TEST') ?: 'test_' . (defined('DB_NAME') ? DB_NAME : 'isp_management'));
}
if (!defined('DB_USER_TEST')) {
    define('DB_USER_TEST', getenv('DB_USER_TEST') ?: (defined('DB_USER') ? DB_USER : 'root'));
}
if (!defined('DB_PASSWORD_TEST')) {
    define('DB_PASSWORD_TEST', getenv('DB_PASSWORD_TEST') ?: (defined('DB_PASSWORD') ? DB_PASSWORD : ''));
}
if (!defined('DB_CHARSET_TEST')) {
    define('DB_CHARSET_TEST', 'utf8mb4');
}

// Test MikroTik Configuration
if (!defined('MIKROTIK_TEST_HOST')) {
    define('MIKROTIK_TEST_HOST', '192.168.88.1');
}
if (!defined('MIKROTIK_TEST_PORT')) {
    define('MIKROTIK_TEST_PORT', 8728);
}
if (!defined('MIKROTIK_TEST_USER')) {
    define('MIKROTIK_TEST_USER', 'admin');
}
if (!defined('MIKROTIK_TEST_PASSWORD')) {
    define('MIKROTIK_TEST_PASSWORD', 'test123');
}

// Test Email Configuration
if (!defined('MAIL_TEST_HOST')) {
    define('MAIL_TEST_HOST', 'smtp.gmail.com');
}
if (!defined('MAIL_TEST_PORT')) {
    define('MAIL_TEST_PORT', 587);
}
if (!defined('MAIL_TEST_USER')) {
    define('MAIL_TEST_USER', 'test@example.com');
}
if (!defined('MAIL_TEST_PASSWORD')) {
    define('MAIL_TEST_PASSWORD', 'testpassword');
}

// Test WhatsApp Configuration
if (!defined('WHATSAPP_TEST_TOKEN')) {
    define('WHATSAPP_TEST_TOKEN', 'test_token_123');
}
if (!defined('WHATSAPP_TEST_PHONE')) {
    define('WHATSAPP_TEST_PHONE', '+51999999999');
}

// Test Upload Paths
if (!defined('UPLOADS_TEST_PATH')) {
    define('UPLOADS_TEST_PATH', __DIR__ . '/../../Assets/uploads/test/');
}

// Test Logging
if (!defined('LOG_TEST_PATH')) {
    define('LOG_TEST_PATH', __DIR__ . '/../logs/');
}

// Test Security Keys
if (!defined('ENCRYPTION_KEY_TEST')) {
    define('ENCRYPTION_KEY_TEST', 'test_encryption_key_12345');
}

// Test Mode Flags
if (!defined('MOCK_EXTERNAL_SERVICES')) {
    define('MOCK_EXTERNAL_SERVICES', true);
}
if (!defined('USE_TEST_DATABASE')) {
    define('USE_TEST_DATABASE', true);
}
if (!defined('ENABLE_TEST_LOGGING')) {
    define('ENABLE_TEST_LOGGING', false);
}

// Create test directories if they don't exist
$testDirs = [
    UPLOADS_TEST_PATH,
    LOG_TEST_PATH,
    __DIR__ . '/../cache/',
    __DIR__ . '/../coverage/',
];

foreach ($testDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}