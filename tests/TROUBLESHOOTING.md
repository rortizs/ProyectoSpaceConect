# Testing Framework Troubleshooting Guide

This comprehensive troubleshooting guide helps diagnose and resolve common issues encountered when working with the ISP Management System testing framework. It covers setup problems, test execution issues, performance problems, and environment-specific challenges.

## 📋 Table of Contents

1. [Quick Diagnostics](#quick-diagnostics)
2. [Environment Setup Issues](#environment-setup-issues)
3. [Database Problems](#database-problems)
4. [Test Execution Issues](#test-execution-issues)
5. [Performance Problems](#performance-problems)
6. [MikroTik Integration Issues](#mikrotik-integration-issues)
7. [CI/CD Pipeline Problems](#cicd-pipeline-problems)
8. [Code Coverage Issues](#code-coverage-issues)
9. [Memory and Resource Problems](#memory-and-resource-problems)
10. [Security and Permissions](#security-and-permissions)
11. [Debug Techniques](#debug-techniques)
12. [Common Error Messages](#common-error-messages)

## 🔍 Quick Diagnostics

### Health Check Script

Create `tests/diagnose.php` for quick system diagnosis:

```php
<?php
/**
 * Testing Framework Diagnostic Tool
 *
 * Run this script to quickly identify common issues
 */

class TestingDiagnostics
{
    private array $results = [];
    private bool $hasErrors = false;

    public function runDiagnostics(): void
    {
        echo "🔍 ISP Management System - Testing Framework Diagnostics\n";
        echo "=" . str_repeat("=", 60) . "\n\n";

        $this->checkPHPVersion();
        $this->checkPHPExtensions();
        $this->checkComposerDependencies();
        $this->checkDatabaseConnection();
        $this->checkTestConfiguration();
        $this->checkFilePermissions();
        $this->checkMikroTikConnection();
        $this->checkTestDataIntegrity();
        $this->checkMemoryLimits();

        $this->generateReport();
    }

    private function checkPHPVersion(): void
    {
        $requiredVersion = '7.4.0';
        $currentVersion = PHP_VERSION;

        if (version_compare($currentVersion, $requiredVersion, '>=')) {
            $this->addResult('✅ PHP Version', "Current: {$currentVersion} (Required: {$requiredVersion}+)");
        } else {
            $this->addError('❌ PHP Version', "Current: {$currentVersion} (Required: {$requiredVersion}+)");
        }
    }

    private function checkPHPExtensions(): void
    {
        $requiredExtensions = ['mysqli', 'pdo_mysql', 'zip', 'json', 'mbstring'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if (empty($missingExtensions)) {
            $this->addResult('✅ PHP Extensions', 'All required extensions loaded');
        } else {
            $this->addError('❌ PHP Extensions', 'Missing: ' . implode(', ', $missingExtensions));
        }
    }

    private function checkComposerDependencies(): void
    {
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            $this->addError('❌ Composer Dependencies', 'Vendor directory not found. Run: composer install');
            return;
        }

        require_once __DIR__ . '/../vendor/autoload.php';

        // Check if PHPUnit is available
        if (class_exists('PHPUnit\Framework\TestCase')) {
            $this->addResult('✅ PHPUnit', 'PHPUnit framework loaded successfully');
        } else {
            $this->addError('❌ PHPUnit', 'PHPUnit not found. Run: composer install --dev');
        }
    }

    private function checkDatabaseConnection(): void
    {
        if (!file_exists(__DIR__ . '/config/test_config.php')) {
            $this->addError('❌ Database Config', 'Test configuration not found. Copy test_config.example.php');
            return;
        }

        require_once __DIR__ . '/config/test_config.php';

        $host = defined('DB_HOST_TEST') ? DB_HOST_TEST : 'localhost';
        $name = defined('DB_NAME_TEST') ? DB_NAME_TEST : 'test_db';
        $user = defined('DB_USER_TEST') ? DB_USER_TEST : 'test_user';
        $password = defined('DB_PASSWORD_TEST') ? DB_PASSWORD_TEST : 'test_pass';

        try {
            $pdo = new PDO("mysql:host={$host};dbname={$name}", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Test a simple query
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$name}'");
            $tableCount = $stmt->fetchColumn();

            $this->addResult('✅ Database Connection', "Connected to {$name} ({$tableCount} tables)");
        } catch (PDOException $e) {
            $this->addError('❌ Database Connection', $e->getMessage());
        }
    }

    private function checkTestConfiguration(): void
    {
        $configFile = __DIR__ . '/config/test_config.php';

        if (!file_exists($configFile)) {
            $this->addError('❌ Test Configuration', 'Configuration file not found');
            return;
        }

        require_once $configFile;

        $requiredConstants = [
            'DB_HOST_TEST',
            'DB_NAME_TEST',
            'DB_USER_TEST',
            'DB_PASSWORD_TEST'
        ];

        $missingConstants = [];
        foreach ($requiredConstants as $constant) {
            if (!defined($constant)) {
                $missingConstants[] = $constant;
            }
        }

        if (empty($missingConstants)) {
            $this->addResult('✅ Test Configuration', 'All required constants defined');
        } else {
            $this->addError('❌ Test Configuration', 'Missing: ' . implode(', ', $missingConstants));
        }
    }

    private function checkFilePermissions(): void
    {
        $directories = [
            __DIR__ . '/cache',
            __DIR__ . '/coverage',
            __DIR__ . '/test_data'
        ];

        $permissionIssues = [];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            if (!is_writable($dir)) {
                $permissionIssues[] = $dir;
            }
        }

        if (empty($permissionIssues)) {
            $this->addResult('✅ File Permissions', 'All test directories writable');
        } else {
            $this->addError('❌ File Permissions', 'Not writable: ' . implode(', ', $permissionIssues));
        }
    }

    private function checkMikroTikConnection(): void
    {
        if (!defined('MIKROTIK_TEST_HOST')) {
            $this->addResult('⚠️ MikroTik Config', 'MikroTik test configuration not set (optional)');
            return;
        }

        $host = MIKROTIK_TEST_HOST;
        $port = defined('MIKROTIK_TEST_PORT') ? MIKROTIK_TEST_PORT : 8728;

        $connection = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);
            $this->addResult('✅ MikroTik Connection', "Test router reachable at {$host}:{$port}");
        } else {
            $this->addResult('⚠️ MikroTik Connection', "Cannot reach {$host}:{$port} - integration tests may fail");
        }
    }

    private function checkTestDataIntegrity(): void
    {
        $fixtureDir = __DIR__ . '/Fixtures/DatabaseFixtures';

        if (!is_dir($fixtureDir)) {
            $this->addError('❌ Test Fixtures', 'Fixture directory not found');
            return;
        }

        $fixtureFiles = glob($fixtureDir . '/*.php');
        $fixtureCount = count($fixtureFiles);

        if ($fixtureCount > 0) {
            $this->addResult('✅ Test Fixtures', "{$fixtureCount} fixture files found");
        } else {
            $this->addError('❌ Test Fixtures', 'No fixture files found');
        }
    }

    private function checkMemoryLimits(): void
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->convertToBytes($memoryLimit);
        $recommendedBytes = 256 * 1024 * 1024; // 256MB

        if ($memoryBytes >= $recommendedBytes) {
            $this->addResult('✅ Memory Limit', "Current: {$memoryLimit} (Recommended: 256M+)");
        } else {
            $this->addResult('⚠️ Memory Limit', "Current: {$memoryLimit} (Recommended: 256M+)");
        }

        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime == 0 || $maxExecutionTime >= 300) {
            $this->addResult('✅ Execution Time', "Current: {$maxExecutionTime} (Recommended: 300+ or 0)");
        } else {
            $this->addResult('⚠️ Execution Time', "Current: {$maxExecutionTime} (Recommended: 300+ or 0)");
        }
    }

    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function addResult(string $check, string $message): void
    {
        $this->results[] = [
            'check' => $check,
            'message' => $message,
            'type' => 'info'
        ];
    }

    private function addError(string $check, string $message): void
    {
        $this->results[] = [
            'check' => $check,
            'message' => $message,
            'type' => 'error'
        ];
        $this->hasErrors = true;
    }

    private function generateReport(): void
    {
        echo "\n📊 Diagnostic Results:\n";
        echo str_repeat("-", 60) . "\n";

        foreach ($this->results as $result) {
            printf("%-30s %s\n", $result['check'], $result['message']);
        }

        echo "\n";

        if ($this->hasErrors) {
            echo "❌ Issues found! Please address the errors above before running tests.\n";
            echo "\n💡 Quick fixes:\n";
            echo "   - Run: composer install --dev\n";
            echo "   - Copy: tests/config/test_config.example.php to tests/config/test_config.php\n";
            echo "   - Create test database and update configuration\n";
            echo "   - Check file permissions: chmod -R 755 tests/\n";
        } else {
            echo "✅ All checks passed! Your testing environment is ready.\n";
            echo "\n🚀 You can now run tests:\n";
            echo "   phpunit tests/Unit/\n";
            echo "   phpunit --coverage-html coverage/\n";
        }

        echo "\n";
    }
}

// Run diagnostics
$diagnostics = new TestingDiagnostics();
$diagnostics->runDiagnostics();
```

### Quick Test Run

```bash
# Run diagnostics
php tests/diagnose.php

# Quick test execution
cd tests && ../vendor/bin/phpunit --testsuite Unit --stop-on-failure

# Check specific component
cd tests && ../vendor/bin/phpunit Unit/Models/CustomersModelTest.php -v
```

## 🛠️ Environment Setup Issues

### Problem: Composer Dependencies Not Installed

**Symptoms:**
- "Class 'PHPUnit\Framework\TestCase' not found"
- "vendor/autoload.php not found"

**Solutions:**
```bash
# Install dependencies
composer install --dev

# If composer is not found
curl -sS https://getcomposer.org/installer | php
php composer.phar install --dev

# For permission issues
sudo chown -R $USER:$USER ~/.composer

# Clear composer cache if needed
composer clear-cache
composer install --dev
```

### Problem: PHP Extensions Missing

**Symptoms:**
- "Extension 'mysqli' not found"
- "Call to undefined function mysql_connect()"

**Solutions:**

**Ubuntu/Debian:**
```bash
# Install required PHP extensions
sudo apt-get update
sudo apt-get install php-mysql php-zip php-mbstring php-xml php-gd

# For specific PHP version
sudo apt-get install php8.1-mysql php8.1-zip php8.1-mbstring
```

**CentOS/RHEL:**
```bash
# Install extensions
sudo yum install php-mysqli php-zip php-mbstring php-xml

# Or with dnf
sudo dnf install php-mysqli php-zip php-mbstring php-xml
```

**macOS with Homebrew:**
```bash
# Install PHP with extensions
brew install php
brew install php@8.1

# Verify extensions
php -m | grep -E "(mysqli|zip|mbstring)"
```

### Problem: Wrong PHP Version

**Symptoms:**
- "Parse error: syntax error, unexpected ':'"
- "Fatal error: Call to undefined function"

**Solutions:**
```bash
# Check current PHP version
php -v

# Switch PHP version (Ubuntu with multiple versions)
sudo update-alternatives --config php

# Use specific PHP version
/usr/bin/php8.1 vendor/bin/phpunit

# Set environment variable
export PATH="/usr/local/php8.1/bin:$PATH"
```

## 🗄️ Database Problems

### Problem: Database Connection Failed

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- "Access denied for user 'test_user'@'localhost'"

**Diagnosis:**
```bash
# Test MySQL connection
mysql -h localhost -u test_user -p

# Check MySQL service status
sudo systemctl status mysql
# or
sudo service mysql status

# Check if MySQL is listening
netstat -tlnp | grep :3306
```

**Solutions:**

**Start MySQL Service:**
```bash
# Ubuntu/Debian
sudo systemctl start mysql
sudo systemctl enable mysql

# macOS
brew services start mysql

# Check logs
sudo tail -f /var/log/mysql/error.log
```

**Create Test Database and User:**
```sql
-- Connect as root
mysql -u root -p

-- Create database
CREATE DATABASE test_isp_management;

-- Create test user
CREATE USER 'test_user'@'localhost' IDENTIFIED BY 'test_password';
GRANT ALL PRIVILEGES ON test_isp_management.* TO 'test_user'@'localhost';
FLUSH PRIVILEGES;

-- Test connection
QUIT;
mysql -u test_user -p test_isp_management
```

**Fix Configuration:**
```php
// tests/config/test_config.php
<?php
define('DB_HOST_TEST', 'localhost');
define('DB_NAME_TEST', 'test_isp_management');
define('DB_USER_TEST', 'test_user');
define('DB_PASSWORD_TEST', 'test_password');
```

### Problem: Database Schema Missing

**Symptoms:**
- "Table 'clients' doesn't exist"
- "Unknown column 'status' in 'field list'"

**Solutions:**
```bash
# Import database schema
mysql -u test_user -p test_isp_management < base_de_datos.sql

# Verify tables exist
mysql -u test_user -p test_isp_management -e "SHOW TABLES;"

# Check specific table structure
mysql -u test_user -p test_isp_management -e "DESCRIBE clients;"
```

### Problem: Database Transaction Issues

**Symptoms:**
- Tests affect each other
- "Deadlock found when trying to get lock"

**Solutions:**

**Enable Transaction Isolation:**
```php
// In test class
class YourTest extends DatabaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollbackDatabaseTransaction();
        parent::tearDown();
    }
}
```

**Fix Deadlock Issues:**
```php
// Use shorter transactions
public function testWithShortTransaction(): void
{
    $this->beginDatabaseTransaction();

    try {
        // Test logic here
        $this->model->create($data);
        $this->commitDatabaseTransaction();
    } catch (Exception $e) {
        $this->rollbackDatabaseTransaction();
        throw $e;
    }
}
```

## 🧪 Test Execution Issues

### Problem: Tests Failing Randomly (Flaky Tests)

**Symptoms:**
- Tests pass sometimes, fail other times
- Different results on different runs

**Diagnosis:**
```bash
# Run same test multiple times
for i in {1..10}; do
    echo "Run $i:"
    phpunit tests/Unit/Models/CustomersModelTest.php::testSpecificMethod
done

# Run with verbose output
phpunit --debug tests/Unit/Models/CustomersModelTest.php
```

**Solutions:**

**Fix Data Dependencies:**
```php
// Bad - depends on external state
public function testClientUpdate(): void
{
    // Assumes client with ID 1 exists
    $result = $this->model->updateClient(1, ['status' => 'active']);
    $this->assertTrue($result);
}

// Good - creates own data
public function testClientUpdate(): void
{
    $client = $this->createTestClient();
    $result = $this->model->updateClient($client['id'], ['status' => 'active']);
    $this->assertTrue($result);
}
```

**Fix Timing Issues:**
```php
// Bad - race condition
public function testAsyncOperation(): void
{
    $this->service->startAsyncOperation();
    $this->assertTrue($this->service->isComplete()); // May not be complete yet
}

// Good - wait for completion
public function testAsyncOperation(): void
{
    $this->service->startAsyncOperation();

    // Wait up to 5 seconds
    $timeout = time() + 5;
    while (time() < $timeout && !$this->service->isComplete()) {
        usleep(100000); // 100ms
    }

    $this->assertTrue($this->service->isComplete());
}
```

### Problem: Test Discovery Issues

**Symptoms:**
- "No tests executed"
- Tests not found by PHPUnit

**Solutions:**

**Check Test Naming:**
```php
// Correct test method naming
public function testClientCreation(): void // ✅ Starts with 'test'
public function testValidateEmail(): void  // ✅ Starts with 'test'

// Incorrect naming
public function clientCreation(): void     // ❌ Doesn't start with 'test'
public function validateEmail(): void     // ❌ Doesn't start with 'test'
```

**Check File Structure:**
```bash
# Verify test file location
ls -la tests/Unit/Models/CustomersModelTest.php

# Check PHPUnit configuration
cat tests/phpunit.xml

# Run with explicit path
phpunit tests/Unit/Models/
```

**Fix Autoloading:**
```php
// In test file
<?php
require_once __DIR__ . '/../../Support/BaseTestCase.php';

class CustomersModelTest extends BaseTestCase
{
    // Test methods
}
```

### Problem: Assertion Failures

**Symptoms:**
- "Failed asserting that false is true"
- Unexpected test results

**Debugging:**
```php
// Add debug output
public function testClientCreation(): void
{
    $clientData = $this->getTestClientData();

    // Debug: show input data
    var_dump($clientData);

    $result = $this->model->saveClient($clientData);

    // Debug: show result
    var_dump($result);

    $this->assertTrue($result);
}

// Use more specific assertions
public function testClientCreation(): void
{
    $clientData = $this->getTestClientData();
    $result = $this->model->saveClient($clientData);

    // Better assertion with message
    $this->assertTrue(
        $result,
        'Client creation failed with data: ' . json_encode($clientData)
    );
}
```

## ⚡ Performance Problems

### Problem: Slow Test Execution

**Symptoms:**
- Tests take too long to run
- Timeout errors

**Diagnosis:**
```bash
# Profile test execution
phpunit --debug tests/Unit/ | grep -E "Test.*\s+[0-9]+\.[0-9]+\s+seconds"

# Find slow tests
phpunit --log-junit results.xml tests/
# Parse results.xml for time attribute
```

**Solutions:**

**Optimize Database Operations:**
```php
// Bad - Multiple database calls
public function testMultipleClients(): void
{
    for ($i = 0; $i < 100; $i++) {
        $client = $this->createTestClient();
        $this->assertNotNull($client);
    }
}

// Good - Batch operations
public function testMultipleClients(): void
{
    $clients = $this->createMultipleTestClients(100);
    $this->assertCount(100, $clients);
}
```

**Use Test Groups:**
```php
/**
 * @group slow
 */
public function testLargeDataProcessing(): void
{
    // Long-running test
}

// Run without slow tests
phpunit --exclude-group slow
```

**Parallelize Tests:**
```bash
# Install paratest
composer require --dev brianium/paratest

# Run tests in parallel
vendor/bin/paratest --processes=4 tests/Unit/
```

### Problem: Memory Exhaustion

**Symptoms:**
- "Fatal error: Allowed memory size exhausted"
- Tests failing with memory errors

**Solutions:**

**Increase Memory Limit:**
```bash
# Temporary increase
php -d memory_limit=512M vendor/bin/phpunit

# Permanent increase in php.ini
memory_limit = 512M
```

**Fix Memory Leaks:**
```php
// Bad - keeping references to large objects
class TestCase extends BaseTestCase
{
    private array $allTestData = [];

    public function testSomething(): void
    {
        $largeData = $this->generateLargeDataSet();
        $this->allTestData[] = $largeData; // Memory leak!
        // Test logic
    }
}

// Good - clean up after test
class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        // Clean up large objects
        if (isset($this->largeData)) {
            unset($this->largeData);
        }

        parent::tearDown();
    }
}
```

## 🔌 MikroTik Integration Issues

### Problem: Router Connection Timeout

**Symptoms:**
- "Connection timeout"
- MikroTik integration tests failing

**Diagnosis:**
```bash
# Test router connectivity
ping 192.168.88.1

# Test API port
telnet 192.168.88.1 8728

# Check firewall rules
nmap -p 8728 192.168.88.1
```

**Solutions:**

**Mock Router for Testing:**
```php
class MikroTikTestCase extends BaseTestCase
{
    protected function getMockRouter(): MockObject
    {
        $mock = $this->createMock(Router::class);
        $mock->method('connect')->willReturn(true);
        $mock->method('addPppoeUser')->willReturn(true);
        return $mock;
    }

    public function testClientProvisioning(): void
    {
        $router = $this->getMockRouter();
        // Test logic with mock
    }
}
```

**Configure Test Router:**
```bash
# In MikroTik RouterOS
/ip service
set api address=0.0.0.0/0
set api disabled=no

# Create test user
/user add name=test_user group=full password=test123
```

### Problem: API Authentication Failed

**Symptoms:**
- "Invalid username or password"
- Authentication errors

**Solutions:**

**Verify Credentials:**
```php
// tests/config/test_config.php
define('MIKROTIK_TEST_HOST', '192.168.88.1');
define('MIKROTIK_TEST_PORT', 8728);
define('MIKROTIK_TEST_USER', 'test_user');
define('MIKROTIK_TEST_PASSWORD', 'test123');
```

**Test Connection Manually:**
```php
// Test script
<?php
require_once 'Libraries/MikroTik/Router.php';

$router = new Router();
$connected = $router->connect('192.168.88.1', 'test_user', 'test123');

if ($connected) {
    echo "Connection successful\n";
} else {
    echo "Connection failed: " . $router->getLastError() . "\n";
}
```

## 🚀 CI/CD Pipeline Problems

### Problem: Pipeline Failing on Database Setup

**Symptoms:**
- CI tests pass locally but fail in pipeline
- Database connection errors in CI

**Solutions:**

**Fix Service Dependencies:**
```yaml
# GitLab CI
test:
  services:
    - mysql:8.0
  variables:
    MYSQL_ROOT_PASSWORD: root
    MYSQL_DATABASE: test_isp_management
  before_script:
    - until mysqladmin ping -h mysql; do sleep 1; done
  script:
    - phpunit
```

**Environment Variables:**
```yaml
# GitHub Actions
env:
  DB_HOST_TEST: 127.0.0.1
  DB_NAME_TEST: test_isp_management
  DB_USER_TEST: root
  DB_PASSWORD_TEST: root
```

### Problem: Tests Timing Out in CI

**Solutions:**

**Increase Timeouts:**
```yaml
# GitLab CI
test:
  timeout: 30m
  script:
    - phpunit --stop-on-failure
```

**Optimize for CI:**
```php
// Skip slow tests in CI
public function testLongRunningOperation(): void
{
    if (getenv('CI')) {
        $this->markTestSkipped('Skipping slow test in CI');
    }

    // Test logic
}
```

## 📊 Code Coverage Issues

### Problem: Low Coverage Reports

**Symptoms:**
- Coverage percentage lower than expected
- Missing coverage for existing tests

**Solutions:**

**Install Xdebug:**
```bash
# Ubuntu/Debian
sudo apt-get install php-xdebug

# Configure for coverage
echo "xdebug.mode=coverage" | sudo tee -a /etc/php/8.1/cli/conf.d/20-xdebug.ini
```

**Verify Xdebug:**
```bash
# Check if Xdebug is loaded
php -m | grep xdebug

# Check configuration
php -r "echo ini_get('xdebug.mode');"
```

**Generate Coverage:**
```bash
# With HTML report
phpunit --coverage-html coverage/

# With text report
phpunit --coverage-text

# Check specific file coverage
phpunit --coverage-filter Models/CustomersModel.php tests/
```

### Problem: Coverage Generation Fails

**Solutions:**

**Alternative Coverage Drivers:**
```bash
# Use PCOV instead of Xdebug (faster)
sudo apt-get install php-pcov

# Configure phpunit.xml
<coverage>
    <include>
        <directory suffix=".php">../Models</directory>
    </include>
</coverage>
```

## 💾 Memory and Resource Problems

### Problem: Memory Limit Exceeded

**Immediate Fix:**
```bash
# Increase memory limit for single run
php -d memory_limit=1G vendor/bin/phpunit

# Set in php.ini permanently
memory_limit = 1024M
```

**Long-term Solutions:**
```php
// Clean up in tearDown
protected function tearDown(): void
{
    // Explicitly clean up large objects
    unset($this->largeTestData);

    // Force garbage collection
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }

    parent::tearDown();
}
```

### Problem: File Descriptor Limits

**Symptoms:**
- "Too many open files"
- Connection failures

**Solutions:**
```bash
# Check current limits
ulimit -n

# Increase limit temporarily
ulimit -n 4096

# Permanent fix in /etc/security/limits.conf
* soft nofile 4096
* hard nofile 8192
```

## 🔒 Security and Permissions

### Problem: Permission Denied Errors

**Solutions:**
```bash
# Fix test directory permissions
chmod -R 755 tests/
chown -R $USER:$USER tests/

# Create necessary directories
mkdir -p tests/cache tests/coverage tests/logs
chmod 755 tests/cache tests/coverage tests/logs
```

### Problem: SELinux Issues (CentOS/RHEL)

**Solutions:**
```bash
# Check SELinux status
sestatus

# Temporarily disable (for testing)
sudo setenforce 0

# Set proper context
sudo setsebool -P httpd_can_network_connect 1
```

## 🔍 Debug Techniques

### Enable Debug Mode

**Environment Variable:**
```bash
export TEST_DEBUG=1
phpunit tests/Unit/Models/CustomersModelTest.php
```

**In Test Code:**
```php
public function testSomething(): void
{
    if (getenv('TEST_DEBUG')) {
        echo "Debug: Input data - " . json_encode($inputData) . "\n";
    }

    $result = $this->model->processData($inputData);

    if (getenv('TEST_DEBUG')) {
        echo "Debug: Result - " . json_encode($result) . "\n";
    }

    $this->assertTrue($result);
}
```

### Database Query Debugging

```php
// Enable query logging in test
public function testWithQueryLogging(): void
{
    // Enable MySQL general log
    $this->getDatabase()->query("SET GLOBAL general_log = 'ON'");
    $this->getDatabase()->query("SET GLOBAL log_output = 'FILE'");

    // Run test
    $result = $this->model->complexQuery();

    // Check logs
    // tail -f /var/log/mysql/mysql.log

    $this->assertNotEmpty($result);
}
```

### Stack Trace Analysis

```php
// Get detailed stack trace
public function testWithStackTrace(): void
{
    try {
        $this->model->riskyOperation();
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        throw $e;
    }
}
```

## ⚠️ Common Error Messages

### "Class not found" Errors

**Error:** `Fatal error: Class 'CustomersModel' not found`

**Solutions:**
```php
// Check autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Check path to class file
require_once __DIR__ . '/../../Models/CustomersModel.php';

// Verify namespace
use Models\CustomersModel;
```

### "Table doesn't exist" Errors

**Error:** `Table 'test_db.clients' doesn't exist`

**Solutions:**
```bash
# Import schema
mysql -u test_user -p test_isp_management < base_de_datos.sql

# Verify tables
mysql -u test_user -p test_isp_management -e "SHOW TABLES;"

# Check database name in config
grep DB_NAME_TEST tests/config/test_config.php
```

### "Connection refused" Errors

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solutions:**
```bash
# Start MySQL
sudo systemctl start mysql

# Check if running
sudo systemctl status mysql

# Check port
netstat -tlnp | grep :3306

# Test connection
mysql -h localhost -u test_user -p
```

### "Permission denied" Errors

**Error:** `Permission denied: /var/log/test.log`

**Solutions:**
```bash
# Fix permissions
sudo chown $USER:$USER /var/log/test.log
chmod 644 /var/log/test.log

# Use alternative location
mkdir -p tests/logs
# Update config to use tests/logs/
```

### "Memory exhausted" Errors

**Error:** `Fatal error: Allowed memory size of 134217728 bytes exhausted`

**Solutions:**
```bash
# Increase memory limit
php -d memory_limit=512M vendor/bin/phpunit

# Or in php.ini
memory_limit = 512M

# Optimize test data
# Use smaller datasets in tests
```

## 📞 Getting Additional Help

### Log Files to Check

1. **PHP Error Log**: `/var/log/php/error.log`
2. **MySQL Error Log**: `/var/log/mysql/error.log`
3. **Apache/Nginx Error Log**: `/var/log/apache2/error.log`
4. **Test Framework Log**: `tests/logs/test.log`

### Useful Commands for Diagnosis

```bash
# System information
uname -a
php -v
mysql --version

# Service status
sudo systemctl status mysql
sudo systemctl status apache2

# Resource usage
free -h
df -h
ps aux | grep mysql

# Network connectivity
netstat -tlnp | grep :3306
nmap -p 3306 localhost
```

### Environment Information Script

```bash
#!/bin/bash
# save as tests/env-info.sh

echo "=== Environment Information ==="
echo "OS: $(uname -a)"
echo "PHP Version: $(php -v | head -1)"
echo "MySQL Version: $(mysql --version)"
echo "Memory Limit: $(php -r "echo ini_get('memory_limit');")"
echo "Max Execution Time: $(php -r "echo ini_get('max_execution_time');")"
echo ""

echo "=== PHP Extensions ==="
php -m | grep -E "(mysqli|pdo|zip|json|mbstring|xdebug)"
echo ""

echo "=== Database Connection ==="
mysql -h localhost -u test_user -p test_isp_management -e "SELECT 1 as test;" 2>&1
echo ""

echo "=== File Permissions ==="
ls -la tests/
echo ""

echo "=== Available Memory ==="
free -h
echo ""
```

---

This troubleshooting guide covers the most common issues encountered when working with the testing framework. For additional support, consult the [Testing Guide](TESTING_GUIDE.md) or reach out to the development team with specific error messages and environment details.

**Remember**: Always test your fixes in a safe environment before applying them to production systems.