# MikroTik Integration Test Suite

Comprehensive integration tests for MikroTik router functionality within the ISP management system. These tests validate the complete workflow of router operations, client management, and network security.

## Overview

The MikroTik integration test suite covers critical business workflows that span multiple systems, ensuring reliable router integration and network management capabilities.

## Test Suites

### 1. RouterConnectionTest
**Purpose**: Router connection, authentication, and RouterFactory functionality
**Groups**: `connection`

Tests the core functionality of router connection management including:
- RouterFactory auto-detection between Legacy and REST API
- Connection establishment and authentication
- Timeout handling and retry mechanisms
- Router version detection and API type selection
- Connection pooling and resource management
- Failover scenarios and recovery procedures

**Key Test Scenarios**:
- Auto-detection of RouterOS versions (6.x vs 7.x)
- Connection failure handling with exponential backoff
- Concurrent connection management
- SSL/TLS security validation
- Performance metrics collection

### 2. ClientProvisioningTest
**Purpose**: Complete client provisioning workflow including PPPoE and Queue management
**Groups**: `provisioning`

Tests the end-to-end client onboarding process:
- PPPoE secret creation and management
- Simple Queue setup with bandwidth limits
- IP address assignment and conflict detection
- Bulk client provisioning operations
- Plan upgrades and modifications
- Provisioning rollback on failure

**Key Test Scenarios**:
- Complete client onboarding workflow
- Bulk provisioning with partial failure handling
- Plan changes and bandwidth modifications
- Validation of provisioning data
- Performance testing for bulk operations

### 3. ContentFilteringTest
**Purpose**: DNS blocking, firewall rules, and content filtering policies
**Groups**: `content-filtering`

Tests comprehensive content filtering capabilities:
- DNS-based domain blocking
- Firewall rule creation and management
- Web proxy access control
- Category-based filtering
- Policy application and removal
- HTTPS interception and filtering

**Key Test Scenarios**:
- Policy application to clients
- Bulk domain blocking operations
- Filtering rule conflict resolution
- Bypass mechanisms for trusted sources
- Performance with large domain lists

### 4. ClientLifecycleTest
**Purpose**: Client activation, suspension, restoration, and disconnection workflows
**Groups**: `lifecycle`

Tests complete client state management:
- Client activation processes
- Suspension and restoration workflows
- Service disconnection procedures
- Automated status transitions
- Bulk lifecycle operations
- Event logging and audit trails

**Key Test Scenarios**:
- Complete lifecycle state transitions
- Rollback on failure scenarios
- Concurrent lifecycle operations
- Integration with content filtering
- Performance metrics for lifecycle operations

### 5. BandwidthManagementTest
**Purpose**: Queue management, QoS, and bandwidth allocation
**Groups**: `bandwidth`

Tests comprehensive bandwidth management:
- Simple Queue operations with burst configurations
- Queue Tree hierarchical management
- Dynamic bandwidth allocation
- Fair share and priority management
- PCQ (Per Connection Queue) implementation
- Bandwidth monitoring and reporting

**Key Test Scenarios**:
- Hierarchical QoS implementation
- Dynamic bandwidth scaling
- Performance optimization
- Usage reporting and analytics
- Auto-scaling based on utilization

### 6. NetworkSecurityTest
**Purpose**: Firewall rules, access control, and network security policies
**Groups**: `security`

Tests comprehensive network security features:
- Firewall rule implementation
- Network segmentation and isolation
- MAC address filtering
- DDoS protection and rate limiting
- VPN security configurations
- Intrusion detection and prevention

**Key Test Scenarios**:
- Multi-layer security policy enforcement
- Network isolation between VLANs
- Automated threat detection
- Security compliance validation
- Wireless security configurations

### 7. ErrorHandlingAndFailoverTest
**Purpose**: Error handling, failover scenarios, and recovery mechanisms
**Groups**: `error-handling`

Tests robust error handling and recovery:
- Connection failure scenarios
- API error response handling
- Transaction rollback mechanisms
- Router health monitoring
- Circuit breaker pattern implementation
- Disaster recovery procedures

**Key Test Scenarios**:
- Automatic failover to backup routers
- Transaction rollback on partial failures
- Service degradation handling
- Concurrent error scenarios
- Alert and notification systems

### 8. PerformanceAndMonitoringTest
**Purpose**: Performance metrics, monitoring, and load testing
**Groups**: `performance`, `monitoring`

Tests system performance characteristics:
- Connection establishment performance
- API response time benchmarks
- Bulk operation performance
- Concurrent connection handling
- Resource monitoring and alerting
- Load testing scenarios

**Key Test Scenarios**:
- Performance under various load conditions
- Real-time monitoring data collection
- Resource utilization tracking
- Performance optimization
- Alerting threshold validation

## Usage

### Running All Tests

```bash
cd /path/to/tests
php run_mikrotik_tests.php
```

### Running Specific Test Suite

```bash
# Run only connection tests
php run_mikrotik_tests.php --suite=RouterConnection

# Run only provisioning tests
php run_mikrotik_tests.php --suite=ClientProvisioning
```

### Running Tests by Group

```bash
# Run all security-related tests
php run_mikrotik_tests.php --group=security

# Run all performance tests
php run_mikrotik_tests.php --group=performance
```

### Using PHPUnit Directly

```bash
# Run specific test file
vendor/bin/phpunit tests/Integration/MikroTik/RouterConnectionTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage tests/Integration/MikroTik/

# Run specific test method
vendor/bin/phpunit --filter testRouterFactoryAutoDetection tests/Integration/MikroTik/RouterConnectionTest.php
```

## Configuration

### Test Environment Setup

1. **Database Configuration**: Ensure test database is configured in `tests/config/test_config.php`
2. **Router Configuration**: Update test router settings for actual integration testing
3. **Mock Configuration**: Set `MOCK_EXTERNAL_SERVICES` to `true` for unit testing mode

### Environment Variables

```bash
# Test database configuration
DB_HOST_TEST=localhost
DB_NAME_TEST=test_isp_management
DB_USER_TEST=test_user
DB_PASSWORD_TEST=test_password

# Test router configuration
MIKROTIK_TEST_HOST=192.168.88.1
MIKROTIK_TEST_PORT=8728
MIKROTIK_TEST_USER=admin
MIKROTIK_TEST_PASSWORD=test123
```

## Test Data and Fixtures

The test suite uses comprehensive test data located in `tests/Fixtures/`:

- **Router configurations**: Different RouterOS versions and API types
- **Client data**: Various client profiles and plans
- **Network configurations**: VLAN setups and security policies
- **Mock responses**: Simulated router API responses

## Mocking and Simulation

### Router Mocking

The `MikroTikTestCase` base class provides comprehensive mocking capabilities:

- **Connection mocking**: Simulate successful/failed connections
- **API response mocking**: Mock router API calls and responses
- **Error simulation**: Simulate various error conditions
- **Performance simulation**: Control response times and behavior

### External Service Mocking

When `MOCK_EXTERNAL_SERVICES` is enabled:
- Router API calls are mocked
- Database operations use test database
- External dependencies are simulated
- Network operations are stubbed

## Performance Benchmarks

The test suite includes performance benchmarks for:

- **Connection Time**: < 2 seconds per connection
- **API Response Time**: < 1 second per API call
- **Bulk Operations**: > 3 operations per second
- **Memory Usage**: < 256MB peak usage
- **Concurrent Connections**: 50+ simultaneous connections

## Continuous Integration

### GitHub Actions Integration

```yaml
- name: Run MikroTik Integration Tests
  run: |
    php tests/run_mikrotik_tests.php

- name: Upload Test Reports
  uses: actions/upload-artifact@v3
  with:
    name: mikrotik-test-reports
    path: tests/logs/mikrotik_tests_*
```

### Jenkins Integration

```groovy
stage('MikroTik Integration Tests') {
    steps {
        sh 'php tests/run_mikrotik_tests.php'
        publishHTML([
            allowMissing: false,
            alwaysLinkToLastBuild: true,
            keepAll: true,
            reportDir: 'tests/logs',
            reportFiles: 'detailed_report.html',
            reportName: 'MikroTik Test Report'
        ])
    }
}
```

## Troubleshooting

### Common Issues

1. **PHPUnit Not Found**:
   - Install via Composer: `composer require --dev phpunit/phpunit`
   - Use global installation: `composer global require phpunit/phpunit`

2. **Database Connection Errors**:
   - Verify test database configuration
   - Ensure test database exists and is accessible
   - Check database permissions

3. **Router Connection Timeouts**:
   - Verify router accessibility
   - Check firewall settings
   - Validate credentials

4. **Memory Exhaustion**:
   - Increase PHP memory limit: `php -d memory_limit=512M`
   - Optimize test data usage
   - Enable garbage collection

### Debug Mode

Enable verbose output for debugging:

```bash
# Run with debug output
php -d display_errors=1 tests/run_mikrotik_tests.php --suite=RouterConnection

# Run specific test with verbose output
vendor/bin/phpunit --verbose tests/Integration/MikroTik/RouterConnectionTest.php
```

## Contributing

### Adding New Tests

1. Extend `MikroTikTestCase` for router-related tests
2. Use appropriate test groups for categorization
3. Include both success and failure scenarios
4. Add performance benchmarks where applicable
5. Update documentation and examples

### Test Naming Conventions

- Test methods: `test{FeatureBeingTested}()`
- Test groups: Use kebab-case (`@group feature-name`)
- File names: `{Feature}Test.php`
- Assertions: Use descriptive assertion messages

### Code Coverage

Aim for comprehensive coverage of:
- All router API operations
- Error handling paths
- Edge cases and boundary conditions
- Integration points between components

## Support

For issues or questions regarding the MikroTik integration tests:

1. Check the troubleshooting section above
2. Review test output and logs in detail
3. Verify router connectivity and configuration
4. Consult the main project documentation

## License

This test suite is part of the ISP Management System and follows the same licensing terms as the main project.