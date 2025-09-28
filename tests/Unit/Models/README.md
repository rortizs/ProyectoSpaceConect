# Models Unit Tests

## Overview

This directory contains comprehensive unit tests for the Models layer of the ISP management system. The tests cover critical business logic, data validation, SQL query building, and edge cases for the main Model classes.

## Test Coverage

### CustomersModelTest.php
**Target Coverage: 90%+**

#### Critical Methods Tested:
- `checkTicketNumber()` - Payment ticket validation
- `list_records()` - Customer/contract listing with filters
- `saveClient()` - Client creation with validation
- `editClient()` - Client updates
- `create()` - Contract creation
- `modify()` - Contract modifications
- `cancel()`, `layoff()`, `activate()` - Contract state management
- `create_bill()` - Bill generation
- `create_payment()` - Payment processing
- `create_ticket()` - Support ticket creation

#### Test Categories:
- **Critical Business Logic**: Payment validation, contract lifecycle
- **Data Validation**: Input sanitization, required fields
- **SQL Injection Protection**: Malicious input handling
- **Edge Cases**: Extreme values, boundary conditions
- **Error Handling**: Invalid IDs, missing dependencies
- **Performance**: Large dataset handling

### BillsModelTest.php
**Target Coverage: 85%+**

#### Critical Methods Tested:
- `checkTicketNumber()` - Ticket number validation
- `list_records()` - Bill listing with date/state filters
- `create()` - Bill creation
- `modify()` - Bill updates
- `modify_amounts()` - Payment amount adjustments
- `create_payment()` - Payment processing
- `cancel()` - Bill cancellation
- `import()` - Bulk bill import
- `select_invoice()` - Invoice data generation
- `view_bill()` - Complete bill view data
- Stock management methods
- Voucher series management

#### Test Categories:
- **Bill Lifecycle**: Creation, modification, cancellation
- **Payment Processing**: Amount calculations, state updates
- **Data Import/Export**: Mass operations, validation
- **Business Logic**: Invoice generation, proration
- **Stock Management**: Product inventory updates
- **Performance**: Query optimization

### BusinessModelTest.php
**Target Coverage: 85%+**

#### Critical Methods Tested:
- `show_business()` - Business configuration retrieval
- `update_general()` - General business information
- `update_basic()` - Basic configuration
- `update_invoice()` - Invoice settings
- Logo management methods
- `update_email()` - Email configuration
- `update_whatsapp()` - WhatsApp integration
- `create_backup()` - Database backup creation
- `remove()` - Backup deletion

#### Test Categories:
- **Configuration Management**: Business settings updates
- **File Operations**: Logo uploads, backup creation
- **Integration Settings**: Email, WhatsApp configuration
- **Data Validation**: Input sanitization
- **Error Handling**: Invalid configurations
- **Workflow Testing**: Complete configuration sequences

### ContentfilterModelTest.php
**Target Coverage: 80%+**

#### Critical Methods Tested:
- `getFilteringStats()` - Statistics compilation
- `getClientsWithoutFiltering()` - Unfiltered client identification
- `getClientPolicy()` - Policy retrieval
- `getCategories()` - Category management

#### Test Categories:
- **Statistics Generation**: Real-time data aggregation
- **Policy Management**: Client-policy associations
- **Data Filtering**: Active/inactive record handling
- **Performance**: Large dataset queries
- **Integration**: Router-client relationships

## Test Structure

### Base Classes
- **DatabaseTestCase**: Provides database transaction management, test data creation, and assertion helpers
- **BaseTestCase**: Core test functionality and utilities
- **DatabaseTransactions Trait**: Transaction isolation for tests

### Test Data Management
Each test class creates isolated test data:
- Test clients with various configurations
- Test contracts with different states
- Test bills and payments
- Test business configurations
- Test content filter policies

### Assertions Used
- **Database Assertions**: `assertDatabaseHas()`, `assertDatabaseMissing()`, `assertDatabaseCount()`
- **Standard Assertions**: Type checking, value comparisons, array structure validation
- **Business Logic Assertions**: State transitions, calculation accuracy
- **Performance Assertions**: Execution time limits

## Running Tests

### Prerequisites
```bash
# Install PHPUnit (if not already installed)
composer require --dev phpunit/phpunit

# Or using PHAR
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
```

### Execution Commands
```bash
# Run all Model tests
./vendor/bin/phpunit tests/Unit/Models/

# Run specific model tests
./vendor/bin/phpunit tests/Unit/Models/CustomersModelTest.php
./vendor/bin/phpunit tests/Unit/Models/BillsModelTest.php
./vendor/bin/phpunit tests/Unit/Models/BusinessModelTest.php
./vendor/bin/phpunit tests/Unit/Models/ContentfilterModelTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/ tests/Unit/Models/

# Run specific test groups
./vendor/bin/phpunit --group critical tests/Unit/Models/
./vendor/bin/phpunit --group business-logic tests/Unit/Models/
./vendor/bin/phpunit --group performance tests/Unit/Models/
```

## Test Groups

Tests are organized into groups for selective execution:

- **@group critical**: Essential business functionality
- **@group business-logic**: Complex business rule validation
- **@group validation**: Data input validation
- **@group edge-cases**: Boundary conditions and edge cases
- **@group error-handling**: Error scenario testing
- **@group performance**: Performance and scalability tests
- **@group sql-injection**: Security testing
- **@group boundary-conditions**: Extreme value testing
- **@group integration**: Multi-component workflows

## Test Data

### Database Schema Requirements
Tests require the following core tables:
- `clients`, `contracts`, `bills`, `payments`
- `business`, `backups`
- `content_filter_*` tables (created dynamically)
- Supporting tables: `zones`, `roles`, `document_type`, etc.

### Test Data Isolation
- Each test runs in a database transaction
- Transactions are rolled back after each test
- No persistent data pollution between tests
- Shared setup data is created in `setUp()` methods

## Mocking Strategy

### Database Operations
- Real database connections with transaction rollback
- Test data creation using helper methods
- No mocking of core MySQL operations

### External Services
- File operations mocked where appropriate
- API calls stubbed for testing
- Session handling mocked

### Configuration
- Test environment variables
- Isolated test database
- Mock file system operations for safety

## Coverage Goals

| Model | Target Coverage | Critical Methods |
|-------|----------------|------------------|
| CustomersModel | 90%+ | Payment processing, contract lifecycle |
| BillsModel | 85%+ | Bill creation, payment handling |
| BusinessModel | 85%+ | Configuration management |
| ContentfilterModel | 80%+ | Policy management, statistics |

## Quality Metrics

### Code Quality
- All public methods tested
- Critical business logic covered
- Error scenarios handled
- Edge cases included

### Test Quality
- Clear, descriptive test names
- AAA pattern (Arrange, Act, Assert)
- Independent test execution
- Proper test data cleanup

### Performance
- Query execution time monitoring
- Memory usage tracking
- Large dataset handling
- Concurrent operation testing

## Continuous Integration

### Pipeline Integration
Tests should be integrated into CI/CD pipeline:
```yaml
test:
  script:
    - composer install
    - php vendor/bin/phpunit tests/Unit/Models/
    - php vendor/bin/phpunit --coverage-text --colors=never
```

### Quality Gates
- Minimum 85% code coverage for Models
- Zero critical test failures
- Performance benchmarks met
- Security tests passing

## Debugging Failed Tests

### Common Issues
1. **Database Connection**: Verify test database configuration
2. **Missing Dependencies**: Check required tables and data
3. **Transaction Issues**: Ensure proper rollback
4. **Timing Issues**: Add appropriate waits for async operations

### Debug Commands
```bash
# Verbose output
./vendor/bin/phpunit --verbose tests/Unit/Models/

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure tests/Unit/Models/

# Filter specific test
./vendor/bin/phpunit --filter testMethodName tests/Unit/Models/
```

## Best Practices

### Test Writing
1. **Single Responsibility**: One concept per test
2. **Clear Naming**: Descriptive test method names
3. **Data Isolation**: Independent test data
4. **Assertion Clarity**: Specific, meaningful assertions

### Maintenance
1. **Regular Updates**: Keep tests current with code changes
2. **Refactoring**: Extract common test patterns
3. **Documentation**: Update test documentation
4. **Monitoring**: Track test execution metrics

## Security Testing

### SQL Injection
- Malicious input testing
- Parameter binding validation
- Query escaping verification

### Data Validation
- Input sanitization testing
- Type validation
- Length limit testing

### Access Control
- Authorization testing
- Permission validation
- Data isolation verification

This comprehensive test suite provides robust validation of the Models layer, ensuring reliable operation of the ISP management system's core business logic.