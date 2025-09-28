# Services Unit Tests

This directory contains comprehensive unit tests for the Services layer of the ISP Management System. These tests cover critical business logic, external service integration, and error handling scenarios.

## Test Coverage

### Critical Business Logic Services
- **ContentFilterService** - Content filtering policies and MikroTik integration
- **ClientActivedService** - Client activation workflow and network management
- **ClientSuspendService** - Client suspension and cancellation workflow
- **PaymentBillService** - Payment processing and automatic client activation

### Financial Operations
- **BillGenerate** - Automated bill generation for clients

### Communication Services
- **SendWhatsapp** - WhatsApp messaging integration
- **SendMail** - Email service with PDF attachments

### System Operations
- **BackupDBService** - Database backup and archive management

## Test Structure

Each test class follows a consistent pattern:

```php
class ServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    // Test setup and teardown
    // Mock configurations
    // Test methods organized by functionality
    // Helper methods for common operations
}
```

## Key Testing Strategies

### 1. External Service Mocking
All external dependencies are mocked using the `MocksExternalServices` trait:
- MikroTik router API calls
- WhatsApp API integration
- SMTP email servers
- File system operations
- Database connections

### 2. Business Logic Validation
Tests verify:
- Correct state transitions (active → suspended → cancelled)
- Proper calculation of bills, payments, and discounts
- Event triggering and handling
- Transaction management and rollback scenarios

### 3. Error Handling
Comprehensive error scenarios:
- Network connectivity failures
- Database transaction failures
- External API errors and timeouts
- Invalid input validation
- Resource unavailability

### 4. Edge Cases
- Empty datasets
- Boundary conditions
- Concurrent operations
- Resource limitations

## Test Categories

### @group Tags
Tests are organized with PHPUnit groups:
- `@group services` - All service tests
- `@group client-activation` - Client lifecycle tests
- `@group payment-processing` - Financial operation tests
- `@group content-filter` - Content filtering tests
- `@group external-api` - External service integration tests
- `@group database-backup` - Backup operation tests

## Running Tests

### Individual Test Files
```bash
# Run specific service tests
phpunit tests/Unit/Services/ContentFilterServiceTest.php
phpunit tests/Unit/Services/PaymentBillServiceTest.php
```

### Test Groups
```bash
# Run all service tests
phpunit --group services

# Run critical business logic tests
phpunit --group client-activation,payment-processing

# Run external integration tests
phpunit --group external-api
```

### All Services Tests
```bash
# Use the custom test runner
php tests/Unit/Services/run_service_tests.php

# Or with PHPUnit
phpunit tests/Unit/Services/
```

## Test Dependencies

### Required Libraries
- PHPUnit 9.0+
- Mockery 1.4+
- Base test infrastructure (BaseTestCase, MocksExternalServices)

### External Service Configuration
Tests use mocked services by default, but can be configured for integration testing:

```php
// In test configuration
define('MOCK_EXTERNAL_SERVICES', true);  // Use mocks (default)
define('MOCK_EXTERNAL_SERVICES', false); // Use real services (integration)
```

## Mock Service Behaviors

### MikroTik Router Mock
```php
// Successful connection and operations
$router->connected = true;
$router->APIApplyContentFilter() → ['success' => true, 'rules_added' => 1]
$router->APIRemoveContentFilter() → ['success' => true, 'rules_removed' => 1]

// Connection failures
$router->connected = false;
```

### WhatsApp API Mock
```php
// Successful message sending
sendMessage() → ['success' => true, 'message_id' => 'wamid_test_123']

// API failures
sendMessage() → ['success' => false, 'error' => 'Invalid phone number']
```

### Email Service Mock
```php
// Successful email sending
send() → true

// SMTP failures
send() → false (with ErrorInfo containing details)
```

## Test Data Factories

### Client Data
```php
$testClient = [
    'id' => 1,
    'names' => 'John',
    'surnames' => 'Doe',
    'email' => 'john@example.com',
    'net_ip' => '192.168.1.100'
];
```

### Business Data
```php
$testBusiness = [
    'id' => 1,
    'business_name' => 'Test ISP',
    'whatsapp_api' => 'https://api.whatsapp.test.com',
    'whatsapp_key' => 'test_api_key'
];
```

### Bill Data
```php
$testBill = [
    'id' => 1,
    'clientid' => 1,
    'subtotal' => 150.00,
    'total' => 140.00,
    'remaining_amount' => 140.00,
    'state' => 2 // Pending
];
```

## Continuous Integration

### Pre-commit Hooks
```bash
# Run service tests before commit
php tests/Unit/Services/run_service_tests.php
```

### CI Pipeline Configuration
```yaml
test_services:
  script:
    - composer install
    - php tests/Unit/Services/run_service_tests.php
  coverage: '/Lines:\s+(\d+\.\d+)%/'
```

## Performance Considerations

### Test Execution Times
- ContentFilterService: ~2-3 seconds
- PaymentBillService: ~1-2 seconds
- BillGenerate: ~2-3 seconds
- Communication Services: ~1 second each

### Memory Usage
- Each test class uses ~10-15MB
- Full suite requires ~100MB memory limit
- Mock objects are properly cleaned up in tearDown()

## Best Practices

### 1. Test Isolation
- Each test method is independent
- Database transactions are mocked/rolled back
- External service calls are mocked
- No shared state between tests

### 2. Meaningful Assertions
```php
// Verify business logic
$this->assertTrue($result['success']);
$this->assertEquals('Client activated', $result['message']);

// Verify side effects
$this->assertServiceMethodCalled('router', 'unlockNetwork');
$this->assertEventTriggered('ClientActivated');
```

### 3. Error Testing
```php
// Test both success and failure scenarios
public function test_success_scenario() { ... }
public function test_failure_when_invalid_input() { ... }
public function test_handles_network_timeout() { ... }
```

### 4. Mock Configuration
```php
// Configure mocks for specific scenarios
$this->configureMockError('whatsapp', 'send', 'Network timeout', 408);
$this->configureMockException('router', 'connect', 'Exception', 'Connection refused');
```

## Troubleshooting

### Common Issues

#### Mock Not Found Errors
```bash
Error: Mock for service 'router' not found
```
**Solution**: Ensure `setupAllServiceMocks()` is called in test setup.

#### Database Connection Errors
```bash
Error: Connection refused to test database
```
**Solution**: Use mocked database operations or configure test database.

#### Memory Limit Exceeded
```bash
Fatal error: Allowed memory size exhausted
```
**Solution**: Increase memory limit or optimize mock cleanup.

### Debug Mode
```php
// Enable test logging
define('ENABLE_TEST_LOGGING', true);

// Skip external service tests
define('SKIP_EXTERNAL_TESTS', true);
```

## Contributing

### Adding New Service Tests
1. Create test file: `{ServiceName}Test.php`
2. Extend `BaseTestCase` and use `MocksExternalServices`
3. Follow naming convention: `test_method_description_scenario()`
4. Add appropriate `@group` tags
5. Update `run_service_tests.php` configuration

### Test Coverage Goals
- Line Coverage: >90%
- Branch Coverage: >85%
- Method Coverage: 100%
- Critical Path Coverage: 100%

## Related Documentation
- [BaseTestCase Documentation](../Support/BaseTestCase.md)
- [MocksExternalServices Trait](../Support/Traits/MocksExternalServices.md)
- [Test Configuration](../../config/test_config.php)
- [Services Architecture](../../../docs/services.md)