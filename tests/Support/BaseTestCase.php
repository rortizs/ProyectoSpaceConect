<?php

// Check if PHPUnit is available and load appropriate base class
if (class_exists('PHPUnit\Framework\TestCase')) {
    abstract class BaseTestCase extends PHPUnit\Framework\TestCase
    {
        use BaseTestCaseTrait;
    }
} elseif (class_exists('PHPUnit_Framework_TestCase')) {
    abstract class BaseTestCase extends PHPUnit_Framework_TestCase
    {
        use BaseTestCaseTrait;
    }
} else {
    // Fallback when PHPUnit is not available
    abstract class BaseTestCase
    {
        use BaseTestCaseTrait;

        // Mock PHPUnit assertion methods
        public function assertTrue($condition, $message = '')
        {
            if (!$condition) {
                throw new Exception($message ?: 'Assertion failed: condition is not true');
            }
        }

        public function assertFalse($condition, $message = '')
        {
            if ($condition) {
                throw new Exception($message ?: 'Assertion failed: condition is not false');
            }
        }

        public function assertEquals($expected, $actual, $message = '')
        {
            if ($expected !== $actual) {
                throw new Exception($message ?: "Assertion failed: expected '$expected', got '$actual'");
            }
        }

        public function assertNotEmpty($value, $message = '')
        {
            if (empty($value)) {
                throw new Exception($message ?: 'Assertion failed: value is empty');
            }
        }

        public function assertArrayHasKey($key, $array, $message = '')
        {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                throw new Exception($message ?: "Assertion failed: array does not have key '$key'");
            }
        }

        public function assertLessThan($expected, $actual, $message = '')
        {
            if ($actual >= $expected) {
                throw new Exception($message ?: "Assertion failed: $actual is not less than $expected");
            }
        }

        public function assertGreaterThan($expected, $actual, $message = '')
        {
            if ($actual <= $expected) {
                throw new Exception($message ?: "Assertion failed: $actual is not greater than $expected");
            }
        }

        public function assertGreaterThanOrEqual($expected, $actual, $message = '')
        {
            if ($actual < $expected) {
                throw new Exception($message ?: "Assertion failed: $actual is not greater than or equal to $expected");
            }
        }

        public function assertLessThanOrEqual($expected, $actual, $message = '')
        {
            if ($actual > $expected) {
                throw new Exception($message ?: "Assertion failed: $actual is not less than or equal to $expected");
            }
        }

        public function assertContains($needle, $haystack, $message = '')
        {
            if (is_string($haystack)) {
                if (strpos($haystack, $needle) === false) {
                    throw new Exception($message ?: "Assertion failed: '$haystack' does not contain '$needle'");
                }
            } elseif (is_array($haystack)) {
                if (!in_array($needle, $haystack)) {
                    throw new Exception($message ?: "Assertion failed: array does not contain '$needle'");
                }
            } else {
                throw new Exception($message ?: 'Assertion failed: invalid haystack type');
            }
        }

        public function assertInstanceOf($expected, $actual, $message = '')
        {
            if (!($actual instanceof $expected)) {
                $actualType = is_object($actual) ? get_class($actual) : gettype($actual);
                throw new Exception($message ?: "Assertion failed: $actualType is not an instance of $expected");
            }
        }

        public function assertCount($expectedCount, $haystack, $message = '')
        {
            if (is_array($haystack) || $haystack instanceof Countable) {
                $actualCount = count($haystack);
                if ($actualCount !== $expectedCount) {
                    throw new Exception($message ?: "Assertion failed: expected count $expectedCount, got $actualCount");
                }
            } else {
                throw new Exception($message ?: 'Assertion failed: value is not countable');
            }
        }

        public function markTestSkipped($message = '')
        {
            throw new Exception('Test skipped: ' . $message);
        }

        public function expectException($exception)
        {
            // Mock implementation - in real test this would be handled by PHPUnit
        }

        public function expectExceptionMessage($message)
        {
            // Mock implementation - in real test this would be handled by PHPUnit
        }
    }
}

/**
 * Trait containing common test functionality
 */
trait BaseTestCaseTrait
{
    /**
     * Test configuration data
     */
    protected array $testConfig = [];

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        if (method_exists(parent::class, 'setUp')) {
            parent::setUp();
        }
        $this->initializeTest();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        $this->cleanupTest();
        if (method_exists(parent::class, 'tearDown')) {
            parent::tearDown();
        }
    }

    /**
     * Initialize test environment
     */
    protected function initializeTest(): void
    {
        // Load test configuration
        $this->loadTestConfiguration();

        // Set up test environment
        $this->setupTestEnvironment();
    }

    /**
     * Clean up test environment
     */
    protected function cleanupTest(): void
    {
        // Clean up any test artifacts
        $this->cleanupTestArtifacts();
    }

    /**
     * Load test configuration
     */
    protected function loadTestConfiguration(): void
    {
        if (file_exists(__DIR__ . '/../config/test_config.php')) {
            require_once __DIR__ . '/../config/test_config.php';
        }

        $this->testConfig = [
            'database' => [
                'host' => defined('DB_HOST_TEST') ? DB_HOST_TEST : 'localhost',
                'name' => defined('DB_NAME_TEST') ? DB_NAME_TEST : 'test_db',
                'user' => defined('DB_USER_TEST') ? DB_USER_TEST : 'test_user',
                'password' => defined('DB_PASSWORD_TEST') ? DB_PASSWORD_TEST : 'test_pass'
            ],
            'mikrotik' => [
                'host' => defined('MIKROTIK_TEST_HOST') ? MIKROTIK_TEST_HOST : '192.168.88.1',
                'port' => defined('MIKROTIK_TEST_PORT') ? MIKROTIK_TEST_PORT : 8728,
                'user' => defined('MIKROTIK_TEST_USER') ? MIKROTIK_TEST_USER : 'admin',
                'password' => defined('MIKROTIK_TEST_PASSWORD') ? MIKROTIK_TEST_PASSWORD : 'test123'
            ]
        ];
    }

    /**
     * Set up test environment
     */
    protected function setupTestEnvironment(): void
    {
        // Set timezone
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('UTC');
        }

        // Set error reporting for testing
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

        // Initialize mock state if needed
        if (defined('MOCK_EXTERNAL_SERVICES') && MOCK_EXTERNAL_SERVICES) {
            $this->setupMockEnvironment();
        }
    }

    /**
     * Set up mock environment
     */
    protected function setupMockEnvironment(): void
    {
        // Override global functions or initialize mocks
        // This can be extended by specific test cases
    }

    /**
     * Clean up test artifacts
     */
    protected function cleanupTestArtifacts(): void
    {
        // Clean up temporary files, reset global state, etc.
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Get test configuration value
     */
    protected function getTestConfig(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->testConfig;

        foreach ($keys as $keyPart) {
            if (is_array($value) && isset($value[$keyPart])) {
                $value = $value[$keyPart];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Generate random string for testing
     */
    protected function randomString(int $length = 10): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Generate random integer within range
     */
    protected function randomInt(int $min = 1, int $max = 1000): int
    {
        return rand($min, $max);
    }

    /**
     * Assert string contains substring
     */
    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new Exception($message ?: "String '$haystack' does not contain '$needle'");
        }
    }

    /**
     * Assert value is numeric
     */
    protected function assertIsNumeric($value, string $message = ''): void
    {
        if (!is_numeric($value)) {
            throw new Exception($message ?: "Value is not numeric: " . gettype($value));
        }
    }

    /**
     * Assert value is string
     */
    protected function assertIsString($value, string $message = ''): void
    {
        if (!is_string($value)) {
            throw new Exception($message ?: "Value is not string: " . gettype($value));
        }
    }

    /**
     * Assert value is array
     */
    protected function assertIsArray($value, string $message = ''): void
    {
        if (!is_array($value)) {
            throw new Exception($message ?: "Value is not array: " . gettype($value));
        }
    }

    /**
     * Assert value is object
     */
    protected function assertIsObject($value, string $message = ''): void
    {
        if (!is_object($value)) {
            throw new Exception($message ?: "Value is not object: " . gettype($value));
        }
    }

    /**
     * Assert value is boolean
     */
    protected function assertIsBool($value, string $message = ''): void
    {
        if (!is_bool($value)) {
            throw new Exception($message ?: "Value is not boolean: " . gettype($value));
        }
    }

    /**
     * Assert value is float
     */
    protected function assertIsFloat($value, string $message = ''): void
    {
        if (!is_float($value)) {
            throw new Exception($message ?: "Value is not float: " . gettype($value));
        }
    }

    /**
     * Create test data directory
     */
    protected function createTestDataDirectory(string $name): string
    {
        $testDataDir = __DIR__ . '/../test_data/' . $name;

        if (!file_exists($testDataDir)) {
            mkdir($testDataDir, 0755, true);
        }

        return $testDataDir;
    }

    /**
     * Clean up test data directory
     */
    protected function cleanupTestDataDirectory(string $name): void
    {
        $testDataDir = __DIR__ . '/../test_data/' . $name;

        if (file_exists($testDataDir)) {
            $this->removeDirectory($testDataDir);
        }
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}