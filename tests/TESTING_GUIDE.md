# ISP Management System - Comprehensive Testing Guide

This guide provides detailed instructions for writing, executing, and maintaining tests for the PHP ISP management system. It covers testing strategies, best practices, and implementation guidelines for QA teams and developers.

## 📋 Table of Contents

1. [Testing Philosophy](#testing-philosophy)
2. [Testing Strategy](#testing-strategy)
3. [Test Types and Levels](#test-types-and-levels)
4. [Writing Effective Tests](#writing-effective-tests)
5. [Test Organization](#test-organization)
6. [Data Management](#data-management)
7. [Mocking and Isolation](#mocking-and-isolation)
8. [Performance Testing](#performance-testing)
9. [Security Testing](#security-testing)
10. [Quality Metrics](#quality-metrics)
11. [Debugging Tests](#debugging-tests)
12. [Maintenance Guidelines](#maintenance-guidelines)

## 🎯 Testing Philosophy

### Core Principles

Our testing approach is built on these fundamental principles:

1. **Quality over Quantity**: Better to have fewer, high-quality tests than many shallow ones
2. **Fast Feedback**: Tests should run quickly to provide immediate feedback
3. **Reliability**: Tests should be deterministic and not flaky
4. **Maintainability**: Tests should be easy to understand and modify
5. **Business Value**: Tests should validate actual business requirements

### Testing Pyramid

We follow the testing pyramid strategy:

```
        /\
       /  \     E2E Tests (2%)
      /____\    - Full workflow validation
     /      \   - Browser automation
    /        \  - Critical user journeys
   /  Func   \
  /  Tests   \ Functional Tests (8%)
 /   (8%)     \ - API testing
/______________\ - Service integration
\              /
 \    Integ   / Integration Tests (20%)
  \  Tests   /  - Component interaction
   \ (20%)  /   - Database testing
    \______/    - External service testing
     \    /
      \  /     Unit Tests (70%)
       \/      - Individual method testing
               - Business logic validation
               - Fast execution
```

### Test Categories

| Category | Purpose | Scope | Speed | Maintenance |
|----------|---------|-------|-------|-------------|
| **Unit** | Validate individual components | Method/Class | Very Fast | Low |
| **Integration** | Test component interaction | Multiple Components | Fast | Medium |
| **Functional** | Validate features end-to-end | Feature/Service | Medium | Medium |
| **E2E** | Simulate real user scenarios | Full Application | Slow | High |
| **Performance** | Validate system performance | System/Component | Variable | Low |
| **Security** | Validate security measures | System/Component | Fast | Low |

## 🧪 Testing Strategy

### 1. Risk-Based Testing

Focus testing efforts on high-risk areas:

**Critical Business Functions** (Priority 1):
- Payment processing
- Client billing
- Network provisioning
- Security authentication

**High-Impact Features** (Priority 2):
- Client management
- Router configuration
- Content filtering
- Reporting systems

**Supporting Features** (Priority 3):
- User interface
- Logging systems
- Administrative tools

### 2. Test Coverage Strategy

**Coverage Targets by Component:**

```php
// Models (Data Layer) - 90%+ coverage
class CustomersModelTest extends DatabaseTestCase
{
    /**
     * @group critical
     * @group business-logic
     */
    public function testPaymentProcessingWithValidData()
    {
        // High-value business logic testing
    }
}

// Services (Business Layer) - 85%+ coverage
class PaymentBillServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    /**
     * @group critical
     * @group integration
     */
    public function testBillPaymentWorkflow()
    {
        // End-to-end service testing
    }
}

// Controllers (Presentation Layer) - 80%+ coverage
class BillsControllerTest extends BaseTestCase
{
    /**
     * @group functional
     */
    public function testBillListingWithFilters()
    {
        // HTTP interface testing
    }
}
```

### 3. Test Environment Strategy

**Environment Separation:**

1. **Development Testing**
   - Local developer machines
   - Quick feedback loops
   - Subset of test suite

2. **Staging Testing**
   - Full test suite execution
   - Production-like environment
   - Integration testing focus

3. **Production Testing**
   - Smoke tests only
   - Health checks
   - Monitoring validation

## 🧩 Test Types and Levels

### Unit Testing

**Purpose**: Validate individual methods and classes in isolation

**Characteristics:**
- Fast execution (< 1 second per test)
- No external dependencies
- High code coverage
- Deterministic results

**Example Structure:**
```php
class BillsModelTest extends DatabaseTestCase
{
    use DatabaseTransactions, CreatesTestData;

    private BillsModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BillsModel();
    }

    /**
     * @group critical
     * @group business-logic
     */
    public function testCreateBillWithValidData()
    {
        // Arrange
        $billData = $this->getValidBillData();

        // Act
        $result = $this->model->create($billData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('bills', [
            'client_id' => $billData['client_id'],
            'amount' => $billData['amount']
        ]);
    }

    /**
     * @group validation
     * @group error-handling
     */
    public function testCreateBillWithInvalidAmount()
    {
        // Arrange
        $billData = $this->getValidBillData();
        $billData['amount'] = -100; // Invalid negative amount

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->model->create($billData);
    }

    /**
     * @group edge-cases
     */
    public function testCreateBillWithZeroAmount()
    {
        // Test boundary conditions
        $billData = $this->getValidBillData();
        $billData['amount'] = 0;

        $result = $this->model->create($billData);
        $this->assertTrue($result);
    }

    /**
     * @group performance
     */
    public function testBillCreationPerformance()
    {
        // Performance benchmarking
        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $billData = $this->getValidBillData();
            $billData['client_id'] = $i + 1;
            $this->model->create($billData);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should create 100 bills in under 5 seconds
        $this->assertLessThan(5.0, $executionTime);
    }

    private function getValidBillData(): array
    {
        return [
            'client_id' => $this->createTestClient()['id'],
            'amount' => 50.00,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'description' => 'Monthly Internet Service',
            'currency' => 'USD'
        ];
    }
}
```

### Integration Testing

**Purpose**: Test interactions between components

**Focus Areas:**
- Database operations
- External API calls
- Service dependencies
- File system operations

**Example Structure:**
```php
class ClientProvisioningTest extends MikroTikTestCase
{
    use MocksExternalServices;

    /**
     * @group integration
     * @group mikrotik
     */
    public function testClientActivationWorkflow()
    {
        // Arrange
        $client = $this->createTestClient();
        $contract = $this->createTestContract($client['id']);
        $router = $this->getMockRouter();

        // Act
        $service = new ClientActivedService();
        $result = $service->activateClient($contract['id']);

        // Assert
        $this->assertTrue($result);
        $this->assertRouterHasPPPoEUser($router, $client['username']);
        $this->assertDatabaseHas('contracts', [
            'id' => $contract['id'],
            'status' => 'active'
        ]);
    }

    /**
     * @group integration
     * @group error-handling
     */
    public function testClientActivationWithRouterFailure()
    {
        // Test error handling when router is unavailable
        $client = $this->createTestClient();
        $contract = $this->createTestContract($client['id']);

        $this->mockRouterFailure();

        $service = new ClientActivedService();
        $result = $service->activateClient($contract['id']);

        $this->assertFalse($result);
        $this->assertDatabaseHas('contracts', [
            'id' => $contract['id'],
            'status' => 'pending' // Should remain pending on router failure
        ]);
    }
}
```

### Functional Testing

**Purpose**: Test complete features from user perspective

**Characteristics:**
- Business scenario validation
- Multiple component interaction
- Real-world workflow testing

**Example Structure:**
```php
class BillPaymentWorkflowTest extends DatabaseTestCase
{
    use CreatesTestData, MocksExternalServices;

    /**
     * @group functional
     * @group critical
     */
    public function testCompleteBillPaymentWorkflow()
    {
        // Arrange - Set up complete scenario
        $client = $this->createTestClient();
        $contract = $this->createTestContract($client['id']);
        $bill = $this->createTestBill($client['id']);

        // Mock payment gateway
        $this->mockPaymentGateway();

        // Act - Execute complete workflow
        $paymentService = new PaymentBillService();
        $result = $paymentService->processPayment([
            'bill_id' => $bill['id'],
            'amount' => $bill['amount'],
            'payment_method' => 'credit_card',
            'card_token' => 'test_token_123'
        ]);

        // Assert - Verify complete state changes
        $this->assertTrue($result);

        // Verify bill is marked as paid
        $this->assertDatabaseHas('bills', [
            'id' => $bill['id'],
            'status' => 'paid'
        ]);

        // Verify payment record created
        $this->assertDatabaseHas('payments', [
            'bill_id' => $bill['id'],
            'amount' => $bill['amount'],
            'status' => 'completed'
        ]);

        // Verify client status updated if needed
        $this->assertDatabaseHas('contracts', [
            'id' => $contract['id'],
            'status' => 'active'
        ]);
    }
}
```

### Performance Testing

**Purpose**: Validate system performance under load

**Key Metrics:**
- Response time
- Memory usage
- Concurrent operations
- Database query performance

**Example Structure:**
```php
class SystemPerformanceTest extends BaseTestCase
{
    /**
     * @group performance
     * @group slow
     */
    public function testBulkClientCreationPerformance()
    {
        $this->markTestSkipped('Only run during performance testing cycles');

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Create 1000 clients
        for ($i = 0; $i < 1000; $i++) {
            $clientData = $this->generateClientData($i);
            $model = new CustomersModel();
            $model->saveClient($clientData);
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        // Performance assertions
        $this->assertLessThan(30.0, $executionTime, 'Bulk creation should complete in under 30 seconds');
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Should use less than 50MB memory');

        // Log performance metrics
        error_log(sprintf(
            'Performance Test - Time: %.2fs, Memory: %.2fMB',
            $executionTime,
            $memoryUsed / 1024 / 1024
        ));
    }

    /**
     * @group performance
     * @group database
     */
    public function testDatabaseQueryPerformance()
    {
        // Create test data
        $this->createTestClients(100);

        $startTime = microtime(true);

        // Execute complex query
        $model = new CustomersModel();
        $results = $model->list_records([
            'status' => 'active',
            'plan_type' => 'premium',
            'created_from' => date('Y-m-d', strtotime('-1 year')),
            'limit' => 50
        ]);

        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;

        // Query should complete in under 1 second
        $this->assertLessThan(1.0, $queryTime);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(50, count($results));
    }
}
```

### Security Testing

**Purpose**: Validate security measures and protection mechanisms

**Focus Areas:**
- SQL injection prevention
- Input validation
- Authentication testing
- Authorization checks

**Example Structure:**
```php
class SecurityValidationTest extends DatabaseTestCase
{
    /**
     * @group security
     * @group sql-injection
     */
    public function testSqlInjectionPrevention()
    {
        $model = new CustomersModel();

        // Test malicious SQL injection attempts
        $maliciousInputs = [
            "'; DROP TABLE clients; --",
            "1' OR '1'='1",
            "1; DELETE FROM clients WHERE 1=1; --",
            "<script>alert('xss')</script>",
            "1' UNION SELECT * FROM users --"
        ];

        foreach ($maliciousInputs as $maliciousInput) {
            // Attempt to use malicious input in search
            $result = $model->list_records([
                'search' => $maliciousInput
            ]);

            // Should return empty or safe results, not cause SQL errors
            $this->assertIsArray($result);

            // Verify database integrity
            $this->assertDatabaseTableExists('clients');
        }
    }

    /**
     * @group security
     * @group authentication
     */
    public function testUnauthorizedAccessPrevention()
    {
        // Test accessing protected functionality without authentication
        $_SESSION = []; // Clear session

        $controller = new BillsController();

        // Should redirect to login or return error
        $this->expectExceptionMessage('Authentication required');
        $controller->index();
    }

    /**
     * @group security
     * @group input-validation
     */
    public function testInputSanitization()
    {
        $model = new CustomersModel();

        $clientData = [
            'name' => '<script>alert("xss")</script>John Doe',
            'email' => 'test@example.com<script>',
            'phone' => '555-0123; DROP TABLE clients;',
            'address' => 'Main St<img src=x onerror=alert(1)>'
        ];

        $result = $model->saveClient($clientData);

        // Should sanitize input and save safely
        $this->assertTrue($result);

        // Verify malicious code was sanitized
        $savedClient = $model->getById($result);
        $this->assertStringNotContains('<script>', $savedClient['name']);
        $this->assertStringNotContains('DROP TABLE', $savedClient['phone']);
    }
}
```

## 📊 Writing Effective Tests

### Test Structure (AAA Pattern)

Every test should follow the Arrange-Act-Assert pattern:

```php
public function testMethodName()
{
    // Arrange - Set up test data and conditions
    $testData = $this->createTestData();
    $expectedResult = 'expected_value';

    // Act - Execute the method being tested
    $actualResult = $this->objectUnderTest->methodToTest($testData);

    // Assert - Verify the results
    $this->assertEquals($expectedResult, $actualResult);
}
```

### Test Naming Conventions

**Test Method Naming:**
- `test[MethodName][Scenario]`
- `testCreateBillWithValidData()`
- `testCreateBillWithInvalidAmount()`
- `testCreateBillWhenDatabaseUnavailable()`

**Test Class Naming:**
- `[ClassName]Test`
- `CustomersModelTest`
- `PaymentBillServiceTest`

### Test Data Management

**Create Minimal Test Data:**
```php
private function getMinimalValidClientData(): array
{
    return [
        'name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '555-0123'
    ];
}

private function getCompleteClientData(): array
{
    return array_merge($this->getMinimalValidClientData(), [
        'address' => '123 Main St',
        'city' => 'Test City',
        'plan_id' => 1,
        'status' => 'active'
    ]);
}
```

**Use Test Data Builders:**
```php
class ClientTestDataBuilder
{
    private array $data = [];

    public function __construct()
    {
        $this->data = [
            'name' => 'Default Test Client',
            'email' => 'test@example.com',
            'status' => 'active'
        ];
    }

    public function withName(string $name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->data['email'] = $email;
        return $this;
    }

    public function inactive(): self
    {
        $this->data['status'] = 'inactive';
        return $this;
    }

    public function build(): array
    {
        return $this->data;
    }
}

// Usage in tests
public function testInactiveClientBilling()
{
    $clientData = (new ClientTestDataBuilder())
        ->withName('Inactive Client')
        ->inactive()
        ->build();

    // Test logic here
}
```

### Assertion Best Practices

**Use Specific Assertions:**
```php
// Good - Specific assertion
$this->assertCount(5, $results);

// Bad - Generic assertion
$this->assertTrue(count($results) === 5);
```

**Provide Meaningful Messages:**
```php
$this->assertEquals(
    $expectedAmount,
    $actualAmount,
    "Bill amount calculation failed for client {$client['id']}"
);
```

**Group Related Assertions:**
```php
public function testClientCreation()
{
    $result = $this->model->saveClient($clientData);

    // Verify operation success
    $this->assertTrue($result);

    // Verify database state
    $this->assertDatabaseHas('clients', [
        'email' => $clientData['email'],
        'status' => 'active'
    ]);

    // Verify return value
    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
}
```

## 🗂️ Test Organization

### Directory Structure

```
tests/
├── Unit/                    # Unit tests
│   ├── Models/
│   │   ├── CustomersModelTest.php
│   │   ├── BillsModelTest.php
│   │   └── BusinessModelTest.php
│   ├── Services/
│   │   ├── PaymentBillServiceTest.php
│   │   └── ClientActivedServiceTest.php
│   └── Controllers/
│       ├── BillsControllerTest.php
│       └── CustomersControllerTest.php
├── Integration/            # Integration tests
│   ├── MikroTik/
│   │   ├── RouterConnectionTest.php
│   │   └── ClientProvisioningTest.php
│   └── Database/
│       └── TransactionTest.php
├── Functional/            # Functional tests
│   ├── BillingWorkflowTest.php
│   └── ClientLifecycleTest.php
├── EndToEnd/             # E2E tests
│   └── UserJourneyTest.php
├── Performance/          # Performance tests
│   ├── LoadTest.php
│   └── StressTest.php
└── Security/            # Security tests
    ├── SqlInjectionTest.php
    └── AuthenticationTest.php
```

### Test Grouping

**Use PHPUnit Groups:**
```php
/**
 * @group critical
 * @group business-logic
 * @group billing
 */
public function testBillCalculation()
{
    // Test implementation
}
```

**Available Groups:**
- `critical` - Essential functionality
- `business-logic` - Complex business rules
- `integration` - Component integration
- `performance` - Performance testing
- `security` - Security validation
- `slow` - Long-running tests
- `database` - Database operations
- `mikrotik` - Router functionality

### Test Suites Configuration

Configure test suites in `phpunit.xml`:

```xml
<testsuites>
    <testsuite name="Critical">
        <directory>Unit</directory>
        <directory>Integration</directory>
    </testsuite>
    <testsuite name="Fast">
        <directory>Unit</directory>
    </testsuite>
    <testsuite name="Slow">
        <directory>Integration</directory>
        <directory>EndToEnd</directory>
    </testsuite>
</testsuites>
```

## 💾 Data Management

### Database Testing Strategy

**Transaction Isolation:**
```php
class DatabaseTestCase extends BaseTestCase
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

**Test Data Fixtures:**
```php
class EssentialDataFixture extends BaseFixture
{
    public function load(): void
    {
        // Load minimal required data
        $this->loadUserTypes();
        $this->loadPaymentMethods();
        $this->loadServicePlans();
    }

    private function loadServicePlans(): void
    {
        $plans = [
            ['name' => 'Basic', 'speed' => '10M/10M', 'price' => 25.00],
            ['name' => 'Premium', 'speed' => '50M/50M', 'price' => 50.00],
        ];

        foreach ($plans as $plan) {
            $this->insert('plans', $plan);
        }
    }
}
```

**Using Fixtures in Tests:**
```php
public function setUp(): void
{
    parent::setUp();

    $fixtureManager = new FixtureManager($this->getDatabase());
    $fixtureManager->load([
        EssentialDataFixture::class,
        PlansFixture::class
    ]);
}
```

### Test Data Creation Helpers

**Trait for Test Data:**
```php
trait CreatesTestData
{
    protected function createTestClient(array $overrides = []): array
    {
        $defaultData = [
            'name' => 'Test Client ' . $this->randomString(5),
            'email' => 'test' . $this->randomInt() . '@example.com',
            'phone' => '555-' . str_pad($this->randomInt(1000, 9999), 4, '0'),
            'status' => 'active'
        ];

        $clientData = array_merge($defaultData, $overrides);

        $model = new CustomersModel();
        $id = $model->saveClient($clientData);

        return array_merge($clientData, ['id' => $id]);
    }

    protected function createTestContract(int $clientId, array $overrides = []): array
    {
        $defaultData = [
            'client_id' => $clientId,
            'plan_id' => 1,
            'status' => 'pending',
            'start_date' => date('Y-m-d'),
            'monthly_amount' => 50.00
        ];

        $contractData = array_merge($defaultData, $overrides);

        $model = new CustomersModel();
        $id = $model->create($contractData);

        return array_merge($contractData, ['id' => $id]);
    }
}
```

## 🎭 Mocking and Isolation

### External Service Mocking

**Mock External APIs:**
```php
trait MocksExternalServices
{
    protected function mockPaymentGateway(): void
    {
        $mock = $this->createMock(PaymentGateway::class);
        $mock->method('processPayment')
             ->willReturn(['success' => true, 'transaction_id' => 'mock_123']);

        // Replace the real service with mock
        $this->app->instance(PaymentGateway::class, $mock);
    }

    protected function mockWhatsAppService(): void
    {
        $mock = $this->createMock(WhatsAppService::class);
        $mock->method('sendMessage')
             ->willReturn(true);

        $this->app->instance(WhatsAppService::class, $mock);
    }

    protected function mockEmailService(): void
    {
        $mock = $this->createMock(EmailService::class);
        $mock->method('send')
             ->willReturn(['success' => true]);

        $this->app->instance(EmailService::class, $mock);
    }
}
```

**MikroTik Router Mocking:**
```php
class MikroTikTestCase extends BaseTestCase
{
    protected function getMockRouter(): MockObject
    {
        $mock = $this->createMock(Router::class);

        // Mock successful connection
        $mock->method('connect')->willReturn(true);
        $mock->method('isConnected')->willReturn(true);

        // Mock PPPoE operations
        $mock->method('addPppoeUser')->willReturn(true);
        $mock->method('removePppoeUser')->willReturn(true);
        $mock->method('getPppoeUsers')->willReturn([]);

        return $mock;
    }

    protected function mockRouterFailure(): void
    {
        $mock = $this->createMock(Router::class);
        $mock->method('connect')->willReturn(false);
        $mock->method('isConnected')->willReturn(false);

        $this->app->instance(Router::class, $mock);
    }
}
```

### Database Mocking

**Mock Database Operations:**
```php
public function testServiceWithDatabaseFailure()
{
    // Mock database failure
    $mockDb = $this->createMock(Mysql::class);
    $mockDb->method('query')->willThrowException(new DatabaseException('Connection failed'));

    $service = new BillGenerateService($mockDb);

    $result = $service->generateBill($clientId);

    $this->assertFalse($result);
}
```

## ⚡ Performance Testing

### Performance Test Categories

**Load Testing:**
```php
/**
 * @group performance
 * @group load
 */
public function testSystemUnderNormalLoad()
{
    $concurrentUsers = 10;
    $requestsPerUser = 20;

    $startTime = microtime(true);

    for ($user = 0; $user < $concurrentUsers; $user++) {
        for ($request = 0; $request < $requestsPerUser; $request++) {
            $this->simulateUserAction();
        }
    }

    $endTime = microtime(true);
    $totalTime = $endTime - $startTime;

    // Should handle normal load efficiently
    $this->assertLessThan(30.0, $totalTime);
}
```

**Stress Testing:**
```php
/**
 * @group performance
 * @group stress
 * @group slow
 */
public function testSystemUnderStress()
{
    $this->markTestSkipped('Only run during stress testing cycles');

    // Test with extreme load
    $concurrentUsers = 100;
    $requestsPerUser = 50;

    // Monitor system resources
    $startMemory = memory_get_usage();

    try {
        for ($user = 0; $user < $concurrentUsers; $user++) {
            for ($request = 0; $request < $requestsPerUser; $request++) {
                $this->simulateUserAction();
            }
        }

        $this->assertTrue(true, 'System survived stress test');

    } catch (Exception $e) {
        $this->fail('System failed under stress: ' . $e->getMessage());
    }

    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    // Should not use excessive memory
    $this->assertLessThan(200 * 1024 * 1024, $memoryUsed); // 200MB limit
}
```

**Memory Testing:**
```php
/**
 * @group performance
 * @group memory
 */
public function testMemoryUsageWithLargeDataset()
{
    $startMemory = memory_get_usage();

    // Process large dataset
    $model = new CustomersModel();
    $results = $model->list_records(['limit' => 10000]);

    $endMemory = memory_get_usage();
    $memoryUsed = $endMemory - $startMemory;

    // Should use reasonable amount of memory
    $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // 50MB limit
    $this->assertIsArray($results);
}
```

### Performance Benchmarking

**Create Performance Baselines:**
```php
class PerformanceBenchmark
{
    private static $benchmarks = [
        'client_creation' => 0.1,      // 100ms
        'bill_generation' => 0.5,      // 500ms
        'payment_processing' => 1.0,   // 1 second
        'router_provisioning' => 2.0   // 2 seconds
    ];

    public static function assertPerformance(string $operation, float $actualTime): void
    {
        $benchmark = self::$benchmarks[$operation] ?? null;

        if ($benchmark === null) {
            throw new InvalidArgumentException("No benchmark defined for operation: $operation");
        }

        if ($actualTime > $benchmark) {
            throw new AssertionFailedError(
                "Performance regression detected. Operation '$operation' took {$actualTime}s, " .
                "expected < {$benchmark}s"
            );
        }
    }
}

// Usage in tests
public function testClientCreationPerformance()
{
    $startTime = microtime(true);

    $model = new CustomersModel();
    $model->saveClient($this->getValidClientData());

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;

    PerformanceBenchmark::assertPerformance('client_creation', $executionTime);
}
```

## 🔒 Security Testing

### SQL Injection Testing

**Comprehensive SQL Injection Tests:**
```php
class SqlInjectionTest extends DatabaseTestCase
{
    private array $sqlInjectionPayloads = [
        // Basic SQL injection
        "'; DROP TABLE clients; --",
        "1' OR '1'='1",
        "1'; DELETE FROM clients WHERE 1=1; --",

        // Union-based injection
        "1' UNION SELECT * FROM users --",
        "1' UNION SELECT username, password FROM admins --",

        // Boolean-based injection
        "1' AND (SELECT SUBSTRING(password,1,1) FROM users WHERE id=1)='a",

        // Time-based injection
        "1'; WAITFOR DELAY '00:00:05'; --",
        "1' AND (SELECT SLEEP(5)) --",

        // Error-based injection
        "1' AND (SELECT * FROM (SELECT COUNT(*),CONCAT(version(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a) --"
    ];

    /**
     * @group security
     * @group sql-injection
     * @dataProvider sqlInjectionPayloadProvider
     */
    public function testSqlInjectionPrevention(string $payload)
    {
        $model = new CustomersModel();

        try {
            // Test various input points
            $model->list_records(['search' => $payload]);
            $model->getById($payload);

            // Should not cause SQL errors or security breaches
            $this->assertTrue(true, 'SQL injection payload was safely handled');

        } catch (DatabaseException $e) {
            $this->fail("SQL injection caused database error: " . $e->getMessage());
        }

        // Verify database integrity
        $this->assertDatabaseTableExists('clients');
        $this->assertDatabaseTableExists('users');
    }

    public function sqlInjectionPayloadProvider(): array
    {
        return array_map(function($payload) {
            return [$payload];
        }, $this->sqlInjectionPayloads);
    }
}
```

### Input Validation Testing

**Test Input Sanitization:**
```php
/**
 * @group security
 * @group input-validation
 */
public function testXssPreventionInUserInput()
{
    $xssPayloads = [
        '<script>alert("xss")</script>',
        '<img src=x onerror=alert(1)>',
        'javascript:alert("xss")',
        '<svg onload=alert(1)>',
        '<iframe src="javascript:alert(1)"></iframe>'
    ];

    $model = new CustomersModel();

    foreach ($xssPayloads as $payload) {
        $clientData = [
            'name' => "Test {$payload}",
            'email' => 'test@example.com',
            'address' => "123 Main St {$payload}"
        ];

        $result = $model->saveClient($clientData);
        $this->assertTrue($result);

        $savedClient = $model->getById($result);

        // Verify XSS payload was sanitized
        $this->assertStringNotContains('<script>', $savedClient['name']);
        $this->assertStringNotContains('<img', $savedClient['address']);
        $this->assertStringNotContains('javascript:', $savedClient['name']);
    }
}
```

### Authentication Testing

**Test Authentication Security:**
```php
/**
 * @group security
 * @group authentication
 */
public function testPasswordStrengthValidation()
{
    $weakPasswords = [
        '123456',
        'password',
        'qwerty',
        '12345678',
        'admin',
        'letmein'
    ];

    $authService = new AuthenticationService();

    foreach ($weakPasswords as $weakPassword) {
        $result = $authService->validatePasswordStrength($weakPassword);
        $this->assertFalse($result, "Weak password '$weakPassword' should be rejected");
    }

    $strongPasswords = [
        'MyStr0ng!Pass',
        'C0mplex#Password123',
        'Secure&2025!Pass'
    ];

    foreach ($strongPasswords as $strongPassword) {
        $result = $authService->validatePasswordStrength($strongPassword);
        $this->assertTrue($result, "Strong password '$strongPassword' should be accepted");
    }
}
```

## 📊 Quality Metrics

### Code Coverage Targets

**Coverage by Component:**
```php
// PHPUnit coverage configuration
'coverage' => [
    'include' => [
        'Models/' => 90,      // 90% minimum coverage
        'Services/' => 85,    // 85% minimum coverage
        'Controllers/' => 80, // 80% minimum coverage
        'Libraries/' => 75    // 75% minimum coverage
    ],
    'exclude' => [
        'Views/',
        'Assets/',
        'Config/'
    ]
]
```

**Coverage Reporting:**
```bash
# Generate coverage report
phpunit --coverage-html coverage/html/

# Check coverage percentage
phpunit --coverage-text --colors=never | grep "Lines:"

# Fail build if coverage is too low
phpunit --coverage-text --colors=never --coverage-clover coverage.xml
```

### Test Quality Metrics

**Metric Tracking:**
```php
class TestQualityMetrics
{
    public static function trackTestExecution(string $testName, float $executionTime): void
    {
        $metrics = [
            'test_name' => $testName,
            'execution_time' => $executionTime,
            'memory_usage' => memory_get_peak_usage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log metrics for analysis
        file_put_contents(
            'test_metrics.log',
            json_encode($metrics) . "\n",
            FILE_APPEND
        );
    }

    public static function getAverageExecutionTime(): float
    {
        // Calculate average test execution time
        $metrics = file('test_metrics.log');
        $times = array_map(function($line) {
            $data = json_decode($line, true);
            return $data['execution_time'];
        }, $metrics);

        return array_sum($times) / count($times);
    }
}
```

### Test Reliability

**Flaky Test Detection:**
```bash
# Run tests multiple times to detect flaky tests
for i in {1..10}; do
    phpunit tests/Unit/Models/CustomersModelTest.php --log-junit results_$i.xml
done

# Analyze results for inconsistencies
php analyze_test_results.php results_*.xml
```

**Test Stability Monitoring:**
```php
class TestStabilityMonitor
{
    public static function monitorTestStability(string $testClass): array
    {
        $results = [];

        for ($i = 0; $i < 10; $i++) {
            try {
                $test = new $testClass();
                $test->setUp();
                $test->runTest();
                $results[] = 'PASS';
            } catch (Exception $e) {
                $results[] = 'FAIL: ' . $e->getMessage();
            }
        }

        return $results;
    }
}
```

## 🐛 Debugging Tests

### Debug Strategies

**Add Debug Output:**
```php
public function testComplexBusinessLogic()
{
    $client = $this->createTestClient();

    // Add debug output
    if (getenv('TEST_DEBUG')) {
        echo "Testing with client ID: {$client['id']}\n";
        echo "Client data: " . json_encode($client) . "\n";
    }

    $result = $this->service->processClient($client);

    if (getenv('TEST_DEBUG')) {
        echo "Result: " . json_encode($result) . "\n";
    }

    $this->assertTrue($result);
}
```

**Use PHPUnit Debug Mode:**
```bash
# Run with debug output
phpunit --debug tests/Unit/Models/CustomersModelTest.php

# Run single test with verbose output
phpunit --verbose --filter testSpecificMethod tests/Unit/Models/CustomersModelTest.php
```

**Database State Inspection:**
```php
public function testDatabaseOperation()
{
    $client = $this->createTestClient();

    // Inspect database state before operation
    $this->debugDatabaseState('Before operation');

    $result = $this->model->updateClient($client['id'], ['status' => 'suspended']);

    // Inspect database state after operation
    $this->debugDatabaseState('After operation');

    $this->assertTrue($result);
}

private function debugDatabaseState(string $label): void
{
    if (!getenv('TEST_DEBUG')) {
        return;
    }

    echo "\n=== $label ===\n";

    $db = $this->getDatabase();
    $clients = $db->query("SELECT id, name, status FROM clients ORDER BY id");

    foreach ($clients as $client) {
        echo "Client {$client['id']}: {$client['name']} - {$client['status']}\n";
    }

    echo "==================\n";
}
```

### Common Debugging Scenarios

**Test Isolation Issues:**
```php
public function setUp(): void
{
    parent::setUp();

    // Ensure clean state
    $this->resetGlobalState();
    $this->clearTestData();

    if (getenv('TEST_DEBUG')) {
        echo "Test setup complete for: " . $this->getName() . "\n";
    }
}

private function resetGlobalState(): void
{
    $_SESSION = [];
    $_GET = [];
    $_POST = [];
    $_COOKIE = [];
}
```

**Mock Verification:**
```php
public function testServiceWithMocks()
{
    $mockService = $this->createMock(ExternalService::class);

    // Set up expectations
    $mockService->expects($this->once())
               ->method('processRequest')
               ->with($this->equalTo($expectedData))
               ->willReturn($expectedResult);

    // Inject mock
    $service = new BusinessService($mockService);

    // Execute test
    $result = $service->performOperation($inputData);

    // Verify expectations were met
    $this->assertTrue($result);
}
```

## 🔄 Maintenance Guidelines

### Test Maintenance Schedule

**Daily:**
- Monitor test execution results
- Check for flaky tests
- Review test performance metrics

**Weekly:**
- Update test data fixtures
- Review and update test documentation
- Analyze code coverage reports

**Monthly:**
- Review and refactor test code
- Update test dependencies
- Evaluate test strategy effectiveness

**Quarterly:**
- Comprehensive test suite review
- Performance baseline updates
- Test framework upgrades

### Test Code Quality

**Refactoring Guidelines:**
```php
// Before: Duplicated test setup
class BillsModelTest extends DatabaseTestCase
{
    public function testCreateBill()
    {
        $client = ['name' => 'Test', 'email' => 'test@example.com'];
        $clientId = $this->model->saveClient($client);
        // Test logic
    }

    public function testUpdateBill()
    {
        $client = ['name' => 'Test', 'email' => 'test@example.com'];
        $clientId = $this->model->saveClient($client);
        // Test logic
    }
}

// After: Extracted common setup
class BillsModelTest extends DatabaseTestCase
{
    private int $testClientId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testClientId = $this->createStandardTestClient();
    }

    public function testCreateBill()
    {
        // Use $this->testClientId
        // Test logic
    }

    public function testUpdateBill()
    {
        // Use $this->testClientId
        // Test logic
    }

    private function createStandardTestClient(): int
    {
        $client = ['name' => 'Test', 'email' => 'test@example.com'];
        return $this->model->saveClient($client);
    }
}
```

### Test Documentation Updates

**Keep Documentation Current:**
```php
/**
 * Test bill calculation with various scenarios
 *
 * This test verifies that the bill calculation logic correctly handles:
 * - Regular monthly billing
 * - Prorated billing for partial months
 * - Discount applications
 * - Tax calculations
 *
 * @group critical
 * @group business-logic
 * @group billing
 *
 * @dataProvider billCalculationDataProvider
 *
 * @covers BillsModel::calculateAmount
 * @covers BillsModel::applyDiscounts
 * @covers BillsModel::calculateTax
 */
public function testBillCalculationScenarios(array $scenario)
{
    // Test implementation
}
```

### Continuous Improvement

**Test Metrics Analysis:**
```php
class TestMetricsAnalyzer
{
    public function analyzeTestTrends(): array
    {
        $metrics = $this->loadTestMetrics();

        return [
            'execution_time_trend' => $this->calculateExecutionTimeTrend($metrics),
            'failure_rate_trend' => $this->calculateFailureRateTrend($metrics),
            'coverage_trend' => $this->calculateCoverageTrend($metrics),
            'flaky_tests' => $this->identifyFlakyTests($metrics)
        ];
    }

    public function generateRecommendations(array $trends): array
    {
        $recommendations = [];

        if ($trends['execution_time_trend'] > 0.1) {
            $recommendations[] = 'Test execution time is increasing. Consider optimizing slow tests.';
        }

        if ($trends['failure_rate_trend'] > 0.05) {
            $recommendations[] = 'Test failure rate is increasing. Review recent changes.';
        }

        return $recommendations;
    }
}
```

---

This comprehensive testing guide provides the foundation for maintaining high-quality, reliable tests for the ISP management system. Regular review and updates of these guidelines ensure the testing framework continues to provide value and catches issues effectively.

**Next Steps:**
1. Review the [Contributing Guide](CONTRIBUTING.md) for team collaboration
2. Set up [CI/CD Integration](CI_CD_INTEGRATION.md) for automated testing
3. Consult [Troubleshooting Guide](TROUBLESHOOTING.md) for common issues
4. Implement [API Testing](API_TESTING.md) for external integrations