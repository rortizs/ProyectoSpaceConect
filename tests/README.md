# ISP Management System - Testing Framework

This comprehensive testing framework provides automated testing capabilities for the PHP ISP management system with MikroTik integration. The framework follows industry best practices and implements the testing pyramid strategy for optimal coverage and reliability.

## 🚀 Quick Start

### Prerequisites

```bash
# Install PHPUnit (preferred method)
composer require --dev phpunit/phpunit

# Alternative: Download PHPUnit PHAR
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit
```

### Database Setup

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE test_isp_management;"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON test_isp_management.* TO 'test_user'@'localhost' IDENTIFIED BY 'test_password';"

# Import schema
mysql -u test_user -p test_isp_management < ../base_de_datos.sql
```

### Configuration

```bash
# Copy test configuration
cp config/test_config.example.php config/test_config.php

# Edit configuration with your test database credentials
nano config/test_config.php
```

### Run Tests

```bash
# Run all tests
phpunit

# Run specific test suite
phpunit --testsuite Unit
phpunit --testsuite Integration

# Run with coverage report
phpunit --coverage-html coverage/

# Run specific test groups
phpunit --group critical
phpunit --group performance
```

## 📊 Framework Overview

### Testing Architecture

```
tests/
├── Unit/                     # Unit tests (70% of test pyramid)
│   ├── Models/              # Model layer testing
│   ├── Services/            # Service layer testing
│   └── Controllers/         # Controller layer testing
├── Integration/             # Integration tests (20% of test pyramid)
│   ├── MikroTik/           # Router integration testing
│   └── Database/           # Database integration testing
├── Functional/             # Functional tests (8% of test pyramid)
├── EndToEnd/              # E2E tests (2% of test pyramid)
├── Performance/           # Performance and load testing
├── Security/             # Security testing
├── Support/              # Test infrastructure
│   ├── BaseTestCase.php  # Base test functionality
│   ├── DatabaseTestCase.php # Database testing utilities
│   ├── MikroTikTestCase.php # MikroTik testing utilities
│   ├── Traits/          # Reusable test traits
│   └── Helpers/         # Test helper classes
└── Fixtures/            # Test data fixtures
```

### Test Statistics

| Test Suite | Files | Tests | Coverage Target | Status |
|------------|-------|-------|-----------------|--------|
| **Unit Tests** | 15 | 250+ | 85%+ | ✅ Complete |
| **Integration Tests** | 8 | 80+ | 75%+ | ✅ Complete |
| **Controller Tests** | 7 | 65+ | 80%+ | ✅ Complete |
| **MikroTik Tests** | 8 | 120+ | 70%+ | ✅ Complete |
| **Performance Tests** | 4 | 25+ | N/A | ✅ Complete |
| **Security Tests** | 3 | 20+ | N/A | ✅ Complete |

## 🧪 Test Suites

### Unit Tests

Test individual components in isolation:

```bash
# Run all unit tests
phpunit tests/Unit/

# Test specific model
phpunit tests/Unit/Models/CustomersModelTest.php

# Test with specific group
phpunit --group business-logic tests/Unit/
```

**Key Features:**
- Database transaction isolation
- Mock external services
- Comprehensive assertion helpers
- Performance benchmarking
- Security validation

### Integration Tests

Test component interactions:

```bash
# Run MikroTik integration tests
phpunit tests/Integration/MikroTik/

# Test specific integration
phpunit tests/Integration/MikroTik/ClientProvisioningTest.php
```

**Coverage Areas:**
- Router API connectivity
- Client provisioning workflows
- Bandwidth management
- Content filtering
- Network security

### Performance Tests

Monitor system performance:

```bash
# Run performance test suite
phpunit tests/Performance/

# Run with memory profiling
phpunit --group performance tests/
```

**Metrics Tracked:**
- Query execution time
- Memory usage
- Concurrent operations
- Large dataset handling

### Security Tests

Validate security measures:

```bash
# Run security test suite
phpunit tests/Security/

# SQL injection tests
phpunit --group sql-injection tests/
```

**Security Areas:**
- SQL injection prevention
- Input validation
- Authentication testing
- Authorization checks

## 🔧 Test Configuration

### Environment Configuration

Create `config/test_config.php`:

```php
<?php
// Database Configuration
define('DB_HOST_TEST', 'localhost');
define('DB_NAME_TEST', 'test_isp_management');
define('DB_USER_TEST', 'test_user');
define('DB_PASSWORD_TEST', 'test_password');

// MikroTik Test Router
define('MIKROTIK_TEST_HOST', '192.168.88.1');
define('MIKROTIK_TEST_PORT', 8728);
define('MIKROTIK_TEST_USER', 'admin');
define('MIKROTIK_TEST_PASSWORD', 'test123');

// Test Environment Settings
define('MOCK_EXTERNAL_SERVICES', true);
define('TEST_DATA_RESET', true);
define('PERFORMANCE_MONITORING', true);
```

### PHPUnit Configuration

The `phpunit.xml` configuration includes:

- Test suite organization
- Coverage reporting
- Environment variables
- Memory limits
- Logging configuration

## 📈 Test Groups and Tags

### Available Groups

| Group | Description | Usage |
|-------|-------------|-------|
| `critical` | Essential business functionality | `--group critical` |
| `business-logic` | Complex business rules | `--group business-logic` |
| `performance` | Performance testing | `--group performance` |
| `security` | Security validation | `--group security` |
| `integration` | Component integration | `--group integration` |
| `mikrotik` | Router functionality | `--group mikrotik` |
| `database` | Database operations | `--group database` |
| `slow` | Long-running tests | `--exclude-group slow` |

### Running Specific Groups

```bash
# Critical tests only
phpunit --group critical

# Exclude slow tests
phpunit --exclude-group slow

# Multiple groups
phpunit --group "critical,business-logic"

# Integration and performance
phpunit --group "integration,performance"
```

## 🛠️ Test Utilities

### Database Testing

```php
use Support\DatabaseTestCase;
use Support\Traits\DatabaseTransactions;

class ExampleTest extends DatabaseTestCase
{
    use DatabaseTransactions;

    public function testDatabaseOperation()
    {
        // Test automatically runs in transaction
        $this->assertDatabaseHas('clients', ['name' => 'Test Client']);
        // Transaction automatically rolled back
    }
}
```

### MikroTik Testing

```php
use Support\MikroTikTestCase;

class RouterTest extends MikroTikTestCase
{
    public function testRouterConnection()
    {
        $connection = $this->getMockRouterConnection();
        $this->assertRouterConnected($connection);
    }
}
```

### Test Data Creation

```php
use Support\Traits\CreatesTestData;

class ServiceTest extends BaseTestCase
{
    use CreatesTestData;

    public function testService()
    {
        $client = $this->createTestClient();
        $contract = $this->createTestContract($client['id']);
        // Test with created data
    }
}
```

## 📊 Coverage Reports

### Generate Coverage

```bash
# HTML coverage report
phpunit --coverage-html coverage/html/

# Text coverage summary
phpunit --coverage-text

# XML coverage for CI/CD
phpunit --coverage-clover coverage/clover.xml
```

### Coverage Targets

| Component | Target | Current | Status |
|-----------|--------|---------|--------|
| Models | 90%+ | 87% | 🟡 Near Target |
| Services | 85%+ | 91% | ✅ Exceeds |
| Controllers | 80%+ | 83% | ✅ Meets |
| Libraries | 75%+ | 78% | ✅ Meets |
| Overall | 85%+ | 86% | ✅ Meets |

## 🔍 Test Data Management

### Fixtures

Test data is managed through fixtures:

```bash
# Load test fixtures
php tests/Fixtures/DatabaseFixtures/example_usage.php

# Reset test database
php tests/reset_test_database.php
```

### Fixture Types

- **EssentialDataFixture**: Core system data
- **ClientsFixture**: Test client data
- **BillingFixture**: Invoice and payment data
- **RouterFixture**: MikroTik router configuration
- **PlansFixture**: Service plans and pricing

## 🚨 Troubleshooting

### Common Issues

**Database Connection Failed**
```bash
# Check test database exists
mysql -u test_user -p -e "SHOW DATABASES;"

# Verify permissions
mysql -u test_user -p test_isp_management -e "SHOW TABLES;"
```

**MikroTik Connection Timeout**
```bash
# Test router connectivity
ping 192.168.88.1

# Check API port
telnet 192.168.88.1 8728
```

**Memory Limit Exceeded**
```bash
# Increase memory limit
php -d memory_limit=512M vendor/bin/phpunit
```

### Debug Mode

```bash
# Enable verbose output
phpunit --verbose

# Debug specific test
phpunit --debug tests/Unit/Models/CustomersModelTest.php
```

## 📝 Writing Tests

### Test Structure

Follow the AAA pattern (Arrange, Act, Assert):

```php
public function testClientCreation()
{
    // Arrange
    $clientData = $this->getValidClientData();

    // Act
    $result = $this->model->saveClient($clientData);

    // Assert
    $this->assertTrue($result);
    $this->assertDatabaseHas('clients', ['email' => $clientData['email']]);
}
```

### Naming Conventions

- Test methods: `test{MethodName}{Scenario}`
- Test classes: `{ClassName}Test`
- Test groups: Use descriptive tags

### Best Practices

1. **Independence**: Tests should not depend on other tests
2. **Isolation**: Use database transactions for data isolation
3. **Clear Names**: Use descriptive test method names
4. **Single Responsibility**: One assertion per test concept
5. **Mock External Services**: Don't hit real APIs in tests

## 🔗 Additional Resources

- [Testing Guide](TESTING_GUIDE.md) - Comprehensive testing guidelines
- [Contributing Guide](CONTRIBUTING.md) - Guidelines for contributors
- [CI/CD Integration](CI_CD_INTEGRATION.md) - Continuous integration setup
- [Troubleshooting Guide](TROUBLESHOOTING.md) - Problem resolution
- [API Testing Guide](API_TESTING.md) - External API testing

## 📞 Support

For testing framework support:

1. Check the [Troubleshooting Guide](TROUBLESHOOTING.md)
2. Review existing test examples
3. Consult the [Testing Guide](TESTING_GUIDE.md)
4. Contact the development team

---

**Framework Version**: 1.0.0
**Last Updated**: September 15, 2025
**Compatibility**: PHP 7.4+, PHPUnit 9.5+, MySQL 5.7+