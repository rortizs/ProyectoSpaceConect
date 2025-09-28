# Database Fixtures System

A comprehensive test data fixtures system for the ISP Management System. This system provides realistic, varied test data for development, testing, and QA purposes.

## Overview

The fixture system includes:

- **BaseFixture.php** - Foundation class with common functionality
- **EssentialDataFixture.php** - System essential data (roles, business config, zones)
- **PlansFixture.php** - Internet service plans with various speeds and pricing
- **RouterFixture.php** - Network routers with different configurations
- **ClientsFixture.php** - Comprehensive client data with various lifecycle states
- **BillingFixture.php** - Bills and payments with different scenarios
- **FixtureManager.php** - Central manager for loading and managing fixtures

## Quick Start

### Basic Usage

```php
// Load all fixtures
require_once 'tests/Fixtures/DatabaseFixtures/FixtureManager.php';

$manager = FixtureManager::quickSetup('standard');
$data = $manager->getAllFixtureData();

// Access specific fixture data
$clients = $manager->getFixtureData('ClientsFixture');
$plans = $manager->getFixtureData('PlansFixture');
```

### Advanced Usage

```php
// Custom setup with bulk data
$manager = new FixtureManager(true); // Enable bulk data
$manager->loadAll();

// Load specific data sets
$manager->loadDataSet('minimal');    // Essential data only
$manager->loadDataSet('basic');      // Essential + Plans + Routers
$manager->loadDataSet('standard');   // + Clients
$manager->loadDataSet('complete');   // All fixtures

// Cleanup when done
$manager->cleanupAll();
```

## Available Data Sets

### Minimal
- Essential system data (roles, business config, zones)
- Basic voucher types and series
- Admin user account

### Basic
- Minimal data +
- Internet service plans (residential, business, premium)
- Network routers and access points

### Standard
- Basic data +
- Diverse client scenarios (active, suspended, cancelled)
- Client contracts and service assignments

### Complete
- Standard data +
- Comprehensive billing history
- Payment records with various methods
- Edge cases and test scenarios

## Fixture Details

### EssentialDataFixture
Creates foundational system data:
- System roles (Administrator, Técnico, Cobranza)
- Business configuration
- Network zones (Centro, Norte, Sur)
- Voucher types (Factura, Boleta, Recibo)
- Content filter categories
- Admin user account

### PlansFixture
Creates internet service plans:
- **Residential Plans**: Básico (2/5 Mbps), Estándar (5/10 Mbps), Avanzado (10/20 Mbps)
- **Business Plans**: Básico (10/20 Mbps), Estándar (20/40 Mbps), Profesional (50/100 Mbps)
- **Premium Plans**: Ultra (100/200 Mbps), Gigabit (500/1000 Mbps)
- **Special Plans**: Student discount, Legacy discontinued

### RouterFixture
Creates network infrastructure:
- Production routers (Centro, Norte, Sur)
- Edge routers for high-capacity connections
- Access points for client distribution
- Test/development routers
- Backup/redundant routers
- Various MikroTik models and configurations

### ClientsFixture
Creates diverse client scenarios:
- **Active Clients**: Residential and business customers
- **Suspended Clients**: Payment issues, technical problems
- **Cancelled Clients**: Service termination scenarios
- **Pending Clients**: New installations in progress
- **Premium Clients**: High-value customers
- **Student Clients**: Special pricing scenarios
- **International Clients**: Temporary contracts

### BillingFixture
Creates comprehensive billing data:
- **Historical Bills**: 6 months of billing history
- **Current Bills**: Current month invoicing
- **Payment Records**: Various payment methods and timing
- **Bill States**: Pending, paid, overdue, partial, cancelled
- **Payment Methods**: Cash, transfer, card, digital wallets

## Data Scenarios

### Client Lifecycle States
- **Active (State 1)**: Regular paying customers
- **Suspended (State 2)**: Service suspended due to non-payment or technical issues
- **Cancelled (State 3)**: Service terminated
- **Pending (State 4)**: New customers awaiting installation

### Billing Scenarios
- **Current Bills**: Recently issued, mostly pending
- **Paid Bills**: Historical bills with full payment
- **Overdue Bills**: Past due bills needing collection
- **Partial Payments**: Bills with incomplete payment
- **Promise Payments**: Overdue bills with payment commitments

### Network Configurations
- **Simple Queues**: Basic bandwidth management
- **PPPoE**: Username/password authentication
- **Multiple Zones**: Geographic service areas
- **Router Types**: Various MikroTik models and capabilities

## Performance Testing

Enable bulk data creation for performance testing:

```php
// Enable bulk data (creates 50+ additional records per fixture)
define('CREATE_BULK_DATA', true);
$manager = new FixtureManager(true);
$manager->loadAll();

// Or use performance scenario
$manager = FixtureManager::quickSetup('performance');
```

## Error Testing

The fixture system includes edge cases and error scenarios:
- Invalid data formats
- Missing required fields
- Constraint violations
- Connection failures
- Timeout scenarios

## Utilities and Tools

### Statistics and Reporting
```php
$stats = $manager->getStats();
$report = $manager->generateReport();
```

### Data Export
```php
$manager->exportToJson('fixtures_export.json');
```

### Validation
```php
$issues = $manager->validateIntegrity();
if (!empty($issues)) {
    foreach ($issues as $issue) {
        echo "Issue: {$issue}\n";
    }
}
```

### Cleanup
```php
// Clean specific fixture
$manager->cleanupFixture('ClientsFixture');

// Clean all fixtures
$manager->cleanupAll();
```

## Integration Examples

### Testing Framework Integration
```php
class DatabaseTestCase extends PHPUnit\Framework\TestCase
{
    protected $fixtureManager;

    protected function setUp(): void
    {
        $this->fixtureManager = FixtureManager::quickSetup('testing');
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->cleanupAll();
    }

    public function testClientCreation()
    {
        $clients = $this->fixtureManager->getFixtureData('ClientsFixture');
        $this->assertNotEmpty($clients['clients']);
    }
}
```

### Development Data Setup
```php
// Setup development environment
$manager = FixtureManager::quickSetup('development');

// Get specific data for forms/dropdowns
$plansFixture = $manager->getFixtureInstance('PlansFixture');
$activePlans = $plansFixture->getActivePlans();
$businessPlans = $plansFixture->getBusinessPlans();

// Get clients by state
$clientsFixture = $manager->getFixtureInstance('ClientsFixture');
$activeClients = $clientsFixture->getActiveClients();
$suspendedClients = $clientsFixture->getSuspendedClients();
```

### QA Environment Setup
```php
// Complete dataset for QA testing
$manager = FixtureManager::quickSetup('complete');

// Generate test report
$report = $manager->generateReport();
file_put_contents('qa_fixture_report.md', $report);

// Create database snapshot
$manager->createSnapshot('qa_baseline');
```

## Best Practices

1. **Always cleanup** after tests to avoid data contamination
2. **Use appropriate data sets** - don't load complete data for simple tests
3. **Check dependencies** before loading specific fixtures
4. **Monitor performance** when using bulk data options
5. **Validate integrity** after loading to catch issues early

## Troubleshooting

### Common Issues

**Dependency errors**: Ensure dependencies are loaded in correct order
```php
// FixtureManager handles this automatically
$manager->loadAll(); // Resolves dependencies
```

**Memory issues with bulk data**: Disable bulk data or increase memory
```php
ini_set('memory_limit', '256M');
$manager = new FixtureManager(false); // Disable bulk data
```

**Database connection errors**: Verify database configuration
```php
// Check database connection before loading
$db = new Mysql();
if (!$db->conection()) {
    throw new Exception('Database connection failed');
}
```

## Contributing

When adding new fixtures:

1. Extend `BaseFixture` class
2. Implement required methods: `getName()`, `load()`
3. Define dependencies in `$dependencies` array
4. Add to `FixtureManager::FIXTURE_CLASSES`
5. Include realistic and diverse test scenarios
6. Document data scenarios and relationships

## File Structure

```
tests/Fixtures/DatabaseFixtures/
├── BaseFixture.php              # Foundation class
├── EssentialDataFixture.php     # System essentials
├── PlansFixture.php             # Service plans
├── RouterFixture.php            # Network infrastructure
├── ClientsFixture.php           # Client data
├── BillingFixture.php           # Bills and payments
├── FixtureManager.php           # Central manager
├── README.md                    # This documentation
├── fixture_manager.log          # Execution logs
└── snapshots/                   # Database snapshots
```