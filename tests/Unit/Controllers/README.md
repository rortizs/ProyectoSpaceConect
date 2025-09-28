# Controllers Unit Tests

This directory contains comprehensive unit tests for the Controllers layer of the ISP Management System.

## Test Structure

### Base Test Case
- **BaseControllerTest.php** - Foundation class providing common testing utilities, mocking capabilities, and assertion helpers for all controller tests.

### Controller Test Files
- **CustomersControllerTest.php** - Tests for client management operations
- **NetworkControllerTest.php** - Tests for router management and MikroTik integration
- **LoginControllerTest.php** - Tests for authentication and session management
- **DashboardControllerTest.php** - Tests for analytics and dashboard functionality
- **BillsControllerTest.php** - Tests for billing operations and invoice generation
- **SettingsControllerTest.php** - Tests for system configuration and administrative functions

## Testing Coverage

### Security Testing
- Authentication requirements
- Permission-based access control
- Session management
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF protection (where applicable)

### Business Logic Testing
- CRUD operations
- Data validation
- File upload handling
- PDF/Excel generation
- Email functionality
- Payment processing
- Router connectivity
- Content filtering

### Integration Testing
- Database operations
- External service integration (MikroTik, email, WhatsApp)
- File system operations
- Network connectivity

### Error Handling
- Database connection failures
- Network timeouts
- Invalid input handling
- Exception management
- Graceful degradation

## Test Patterns

### Authentication Testing
```php
public function testRequiresAuthentication(): void
{
    $this->assertRequiresAuthentication(function() {
        $this->controller->protectedMethod();
    });
}
```

### Permission Testing
```php
public function testRequiresPermission(): void
{
    $this->mockAuthenticatedSession();
    $this->mockPermissionDeniedSession();

    $this->assertRequiresPermission(function() {
        $this->controller->adminMethod();
    });
}
```

### Input Validation Testing
```php
public function testInputValidation(): void
{
    $invalidInputs = [
        'empty_field' => ['field' => ''],
        'malicious_input' => ['field' => '<script>alert("xss")</script>']
    ];

    $this->assertValidatesInput($validationMethod, $invalidInputs);
}
```

### JSON Response Testing
```php
public function testJsonResponse(): void
{
    ob_start();
    $this->controller->ajaxMethod();
    $output = ob_get_clean();

    $this->assertSuccessfulJsonResponse($output);

    $response = json_decode($output, true);
    $this->assertArrayHasKey('data', $response);
}
```

## Mock Objects and Stubs

### Session Mocking
```php
$this->mockAuthenticatedSession([
    'idUser' => 1,
    'userData' => ['profileid' => 1],
    'permits_module' => ['v' => true, 'a' => true]
]);
```

### HTTP Request Mocking
```php
$this->mockPostRequest([
    'field1' => 'value1',
    'field2' => 'value2'
]);
```

### Database Mocking
```php
$mockModel = $this->createMock(stdClass::class);
$mockModel->method('getData')->willReturn($mockData);
$this->controller->setMockModel($mockModel);
```

## Running Tests

### Run All Controller Tests
```bash
phpunit tests/Unit/Controllers/
```

### Run Specific Test File
```bash
phpunit tests/Unit/Controllers/CustomersControllerTest.php
```

### Run Specific Test Method
```bash
phpunit tests/Unit/Controllers/CustomersControllerTest.php::testCustomersRequiresAuthentication
```

### Generate Coverage Report
```bash
phpunit --coverage-html coverage/ tests/Unit/Controllers/
```

## Test Configuration

### Required Constants
- `CLIENTS = 2` - Client management module
- `INSTALLATIONS = 3` - Network installations module
- `BILLS = 4` - Billing module
- `DASHBOARD = 1` - Dashboard module
- `ADMINISTRATOR = 1` - Administrator role

### Global Functions to Mock
- `base_url()` - Base URL generation
- `consent_permission()` - Permission checking
- `sql()` - Database queries
- `sqlObject()` - Single database object retrieval
- `encrypt()` / `decrypt()` - Data encryption
- `strClean()` - Input sanitization

## Best Practices

### Test Isolation
- Each test should be independent
- Use setUp() and tearDown() for test preparation and cleanup
- Mock external dependencies
- Reset global state between tests

### Comprehensive Coverage
- Test both success and failure scenarios
- Test edge cases and boundary conditions
- Test with various user roles and permissions
- Test with malicious inputs

### Performance Testing
- Test with large datasets
- Verify response times for critical operations
- Test concurrent access scenarios
- Monitor memory usage

### Documentation
- Use descriptive test method names
- Add comments for complex test scenarios
- Document assumptions and dependencies
- Maintain test data fixtures

## Test Data Management

### Mock Data Patterns
```php
protected function setupMockData(): void
{
    $this->mockData = [
        'valid_client' => [
            'id' => 1,
            'names' => 'John',
            'surnames' => 'Doe',
            'email' => 'john@example.com'
        ],
        'invalid_client' => [
            'names' => '',
            'email' => 'invalid-email'
        ]
    ];
}
```

### Test Factories
Use the TestDataFactory helper to create consistent test data:
```php
$client = TestDataFactory::createClient([
    'email' => 'specific@example.com'
]);
```

## Debugging Tests

### Enable Test Logging
Set `ENABLE_TEST_LOGGING = true` to enable detailed test logging.

### Debug Output
Use `$this->logTestInfo()` to log test-specific information.

### Mock Verification
Verify mock object interactions:
```php
$mockModel->expects($this->once())
          ->method('getData')
          ->with($this->equalTo('expected_parameter'));
```

## Continuous Integration

These tests are designed to run in CI/CD pipelines with:
- Automated test execution
- Coverage reporting
- Performance monitoring
- Security scanning

## Contributing

When adding new controller tests:
1. Follow the established patterns
2. Ensure comprehensive coverage
3. Test security aspects
4. Add appropriate documentation
5. Update this README if needed