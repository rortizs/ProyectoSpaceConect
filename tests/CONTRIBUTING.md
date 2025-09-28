# Contributing to the ISP Management System Testing Framework

This guide outlines the standards, practices, and procedures for contributing to the testing framework. Whether you're a developer, QA engineer, or team lead, this document will help you contribute effectively to maintaining high-quality tests.

## 📋 Table of Contents

1. [Getting Started](#getting-started)
2. [Development Workflow](#development-workflow)
3. [Writing Tests](#writing-tests)
4. [Code Standards](#code-standards)
5. [Pull Request Process](#pull-request-process)
6. [Review Guidelines](#review-guidelines)
7. [Quality Gates](#quality-gates)
8. [Documentation Standards](#documentation-standards)
9. [Maintenance Responsibilities](#maintenance-responsibilities)
10. [Team Collaboration](#team-collaboration)

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have:

```bash
# Required tools
- PHP 7.4+ with required extensions
- Composer for dependency management
- PHPUnit 9.5+ for testing
- MySQL 5.7+ for database testing
- Git for version control

# Development environment
- IDE with PHP support (PhpStorm, VS Code)
- Xdebug for debugging (optional)
- Docker for containerized testing (optional)
```

### Environment Setup

```bash
# Clone the repository
git clone [repository-url]
cd internet_online

# Install dependencies
composer install --dev

# Set up test database
mysql -u root -p -e "CREATE DATABASE test_isp_management;"
mysql -u root -p test_isp_management < base_de_datos.sql

# Configure test environment
cp tests/config/test_config.example.php tests/config/test_config.php
# Edit configuration with your settings

# Verify setup
cd tests
phpunit --testsuite Unit
```

### First-Time Contributors

1. **Read the Documentation**: Start with [README.md](README.md) and [TESTING_GUIDE.md](TESTING_GUIDE.md)
2. **Run Existing Tests**: Ensure all tests pass in your environment
3. **Choose a Task**: Look for issues labeled `good-first-issue` or `testing`
4. **Ask Questions**: Don't hesitate to ask for clarification

## 🔄 Development Workflow

### Branch Strategy

```bash
# Main branches
main                 # Production-ready code
develop             # Integration branch
feature/testing-*   # New test features
bugfix/test-*      # Test fixes
hotfix/test-*      # Urgent test fixes

# Working with branches
git checkout develop
git pull origin develop
git checkout -b feature/testing-new-payment-tests
# Make changes
git commit -m "feat: add comprehensive payment processing tests"
git push origin feature/testing-new-payment-tests
# Create pull request
```

### Commit Message Format

Use conventional commit format:

```bash
# Format: <type>(<scope>): <description>

# Types:
feat:     # New test feature
fix:      # Bug fix in tests
docs:     # Documentation changes
style:    # Code style changes
refactor: # Test refactoring
test:     # Adding or modifying tests
chore:    # Maintenance tasks

# Examples:
feat(unit): add comprehensive customer validation tests
fix(integration): resolve MikroTik connection timeout issues
docs(testing): update performance testing guidelines
refactor(models): extract common test data creation methods
test(security): add SQL injection prevention tests
chore(ci): update PHPUnit configuration for better reporting
```

### Development Process

1. **Plan Your Work**
   - Identify what needs testing
   - Check existing test coverage
   - Design test scenarios
   - Estimate effort required

2. **Write Tests First** (TDD Approach)
   ```php
   // 1. Write failing test
   public function testNewFeatureCalculation()
   {
       $result = $this->service->calculateNewFeature($input);
       $this->assertEquals($expected, $result);
   }

   // 2. Run test (should fail)
   phpunit --filter testNewFeatureCalculation

   // 3. Implement feature
   // 4. Run test (should pass)
   // 5. Refactor if needed
   ```

3. **Test Your Tests**
   ```bash
   # Run new tests
   phpunit tests/Unit/Models/NewFeatureTest.php

   # Run full test suite
   phpunit

   # Check coverage
   phpunit --coverage-html coverage/
   ```

4. **Document Changes**
   - Update test documentation
   - Add inline comments
   - Update README if needed

## ✅ Writing Tests

### Test Categories

When writing tests, categorize them appropriately:

```php
/**
 * @group critical          # Essential business functionality
 * @group business-logic    # Complex business rules
 * @group integration      # Component interaction
 * @group performance      # Performance validation
 * @group security         # Security measures
 * @group database         # Database operations
 * @group mikrotik         # Router functionality
 * @group slow             # Long-running tests
 */
```

### Test Structure Standards

**Follow the AAA Pattern:**
```php
public function testMethodName()
{
    // Arrange - Set up test conditions
    $testData = $this->createTestData();
    $expectedResult = 'expected_value';

    // Act - Execute the operation
    $actualResult = $this->objectUnderTest->methodToTest($testData);

    // Assert - Verify the results
    $this->assertEquals($expectedResult, $actualResult);
    $this->assertDatabaseHas('table', ['field' => 'value']);
}
```

**Use Descriptive Names:**
```php
// Good - Describes what is being tested
public function testClientActivationUpdatesContractStatus()
public function testBillCalculationWithDiscountApplied()
public function testRouterConnectionFailureHandling()

// Bad - Vague or unclear
public function testClient()
public function testCalculation()
public function testConnection()
```

### Required Test Scenarios

For each new feature or bug fix, include tests for:

1. **Happy Path** - Normal, expected usage
2. **Edge Cases** - Boundary conditions and limits
3. **Error Handling** - Invalid input and failure scenarios
4. **Security** - Input validation and injection prevention
5. **Performance** - Response time and resource usage (if applicable)

### Test Data Management

**Use Builders for Complex Data:**
```php
class ClientTestDataBuilder
{
    private array $data;

    public function __construct()
    {
        $this->data = $this->getDefaultClientData();
    }

    public function withName(string $name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->data['status'] = $status;
        return $this;
    }

    public function build(): array
    {
        return $this->data;
    }
}

// Usage
$client = (new ClientTestDataBuilder())
    ->withName('Premium Client')
    ->withStatus('active')
    ->build();
```

**Create Minimal Test Data:**
```php
// Good - Only include necessary fields
private function getMinimalValidBillData(): array
{
    return [
        'client_id' => 1,
        'amount' => 50.00,
        'due_date' => date('Y-m-d', strtotime('+30 days'))
    ];
}

// Avoid - Unnecessary fields that don't affect the test
private function getCompleteClientData(): array
{
    return [
        'id' => 1,
        'name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '555-0123',
        'address' => '123 Main St',
        'city' => 'Test City',
        'state' => 'Test State',
        'zip' => '12345',
        'country' => 'Test Country',
        'plan_id' => 1,
        'status' => 'active',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00'
        // ... many more fields not relevant to the test
    ];
}
```

## 📏 Code Standards

### Naming Conventions

**Test Classes:**
```php
// Model tests
CustomersModelTest.php
BillsModelTest.php

// Service tests
PaymentBillServiceTest.php
ClientActivedServiceTest.php

// Controller tests
BillsControllerTest.php
CustomersControllerTest.php

// Integration tests
MikroTikIntegrationTest.php
DatabaseIntegrationTest.php
```

**Test Methods:**
```php
// Format: test[MethodName][Scenario]
public function testCreateBillWithValidData()
public function testCreateBillWithInvalidAmount()
public function testCreateBillWhenDatabaseUnavailable()

// For data providers
public function testValidateInputWithVariousScenarios()
public function scenarioDataProvider() // Corresponding data provider
```

### Code Quality Standards

**Use Type Hints:**
```php
// Good
public function testClientCreation(): void
{
    $client = $this->createTestClient();
    $this->assertInstanceOf(array::class, $client);
}

private function createTestClient(): array
{
    return ['id' => 1, 'name' => 'Test'];
}

// Avoid
public function testClientCreation()
{
    $client = $this->createTestClient();
    $this->assertTrue(is_array($client));
}

private function createTestClient()
{
    return ['id' => 1, 'name' => 'Test'];
}
```

**Use Meaningful Assertions:**
```php
// Good - Specific and descriptive
$this->assertCount(5, $results, 'Should return exactly 5 client records');
$this->assertEquals(150.00, $bill['amount'], 'Bill amount should include tax');

// Avoid - Generic and unclear
$this->assertTrue(count($results) == 5);
$this->assertTrue($bill['amount'] > 0);
```

**Handle Test Dependencies:**
```php
// Good - Self-contained test
public function testBillCalculation(): void
{
    $client = $this->createTestClient();
    $contract = $this->createTestContract($client['id']);

    $bill = $this->model->calculateBill($contract);

    $this->assertEquals(50.00, $bill['amount']);
}

// Avoid - Depends on other tests or external state
public function testBillCalculation(): void
{
    // Assumes client with ID 1 exists from previous test
    $bill = $this->model->calculateBill(['client_id' => 1]);
    $this->assertEquals(50.00, $bill['amount']);
}
```

### Documentation Standards

**Document Complex Tests:**
```php
/**
 * Test complex billing calculation with multiple scenarios
 *
 * This test verifies that the billing system correctly calculates
 * amounts for various scenarios including:
 * - Regular monthly billing
 * - Prorated billing for partial months
 * - Discount applications
 * - Tax calculations based on location
 *
 * @group critical
 * @group business-logic
 * @group billing
 *
 * @dataProvider billingScenarioProvider
 *
 * @param array $scenario Test scenario data
 * @param float $expectedAmount Expected bill amount
 * @param string $description Scenario description for debugging
 */
public function testComplexBillingCalculation(
    array $scenario,
    float $expectedAmount,
    string $description
): void {
    // Test implementation
}
```

**Add Inline Comments for Complex Logic:**
```php
public function testClientSuspensionWorkflow(): void
{
    // Create active client with current service
    $client = $this->createActiveClient();

    // Simulate payment failure scenario
    $this->simulatePaymentFailure($client['id']);

    // Execute suspension workflow
    $result = $this->service->suspendClient($client['id']);

    // Verify client status changed
    $this->assertTrue($result);

    // Verify router configuration updated
    $this->assertRouterHasDisabledUser($client['username']);

    // Verify notification sent
    $this->assertNotificationSent($client['email'], 'suspension');
}
```

## 📥 Pull Request Process

### Before Creating PR

1. **Run Complete Test Suite:**
   ```bash
   # Run all tests
   phpunit

   # Check specific test types
   phpunit --group critical
   phpunit --group integration

   # Generate coverage report
   phpunit --coverage-html coverage/
   ```

2. **Review Your Changes:**
   ```bash
   # Check what files changed
   git diff --name-only

   # Review specific changes
   git diff tests/Unit/Models/NewFeatureTest.php

   # Verify no debug code left
   grep -r "var_dump\|print_r\|echo" tests/
   ```

3. **Update Documentation:**
   - Add test descriptions
   - Update coverage metrics
   - Document new test patterns

### PR Template

Use this template for test-related pull requests:

```markdown
## Test Changes Summary

### Type of Changes
- [ ] New test coverage for existing feature
- [ ] Test coverage for new feature
- [ ] Bug fix in existing tests
- [ ] Test framework improvement
- [ ] Performance test additions
- [ ] Security test additions

### Test Coverage
- **Files Added/Modified**: List test files
- **Coverage Before**: X%
- **Coverage After**: Y%
- **New Test Methods**: Number of new tests

### Test Categories Added
- [ ] Unit tests
- [ ] Integration tests
- [ ] Performance tests
- [ ] Security tests
- [ ] End-to-end tests

### Quality Checklist
- [ ] All tests pass locally
- [ ] Tests follow naming conventions
- [ ] Tests are properly documented
- [ ] Test data is properly isolated
- [ ] No hardcoded values or magic numbers
- [ ] Proper use of test groups/tags
- [ ] Coverage targets met

### Testing
- [ ] Tests run in isolation
- [ ] Tests are deterministic
- [ ] No flaky test behavior observed
- [ ] Performance is acceptable

### Documentation
- [ ] Test documentation updated
- [ ] Inline comments added where needed
- [ ] README updated if necessary

## Description
Provide detailed description of what tests were added and why.

## Related Issues
Link to relevant issues or feature requests.

## Screenshots/Logs
Include test output or coverage reports if helpful.
```

### Review Process

1. **Self-Review First**
   - Check code formatting
   - Verify test coverage
   - Test in clean environment

2. **Automated Checks**
   - CI/CD pipeline execution
   - Code quality analysis
   - Security scanning

3. **Peer Review**
   - Code review by team member
   - Test strategy validation
   - Documentation review

## 👀 Review Guidelines

### For Reviewers

**Code Review Checklist:**

1. **Test Quality**
   - [ ] Tests follow AAA pattern
   - [ ] Descriptive test names
   - [ ] Appropriate test grouping
   - [ ] Proper assertions used

2. **Coverage**
   - [ ] Critical paths tested
   - [ ] Edge cases covered
   - [ ] Error scenarios included
   - [ ] Performance considerations

3. **Maintainability**
   - [ ] Clear and readable code
   - [ ] Proper documentation
   - [ ] No code duplication
   - [ ] Good test data management

4. **Best Practices**
   - [ ] Test isolation maintained
   - [ ] No hardcoded values
   - [ ] Appropriate mocking
   - [ ] Database transactions used

**Review Comments Examples:**

```markdown
# Good Review Comments

## Constructive Feedback
"Consider extracting this test data creation into a helper method to avoid duplication across tests."

## Code Quality
"The test name could be more descriptive. Instead of `testUpdate()`, consider `testUpdateClientStatusChangesContractState()`."

## Suggestions
"Have you considered testing the error case when the database connection fails?"

## Approval
"Great test coverage! The use of data providers makes this very thorough. LGTM! ✅"

# Avoid These Comments

## Too Vague
"This looks wrong." (Better: "This assertion checks for null but the method should return an array")

## Not Constructive
"Bad code." (Better: "Consider refactoring this to improve readability")

## Nitpicky
"Use single quotes instead of double quotes" (Unless it's a team standard)
```

### Review Response Guidelines

**For Authors:**

1. **Respond Promptly**
   - Address feedback within 24-48 hours
   - Ask for clarification if needed
   - Explain your reasoning when appropriate

2. **Be Open to Feedback**
   ```markdown
   # Good Responses
   "Good point! I'll extract that into a helper method."
   "I see what you mean. Let me add a test for that error case."
   "Thanks for the suggestion. I'll update the test name to be more descriptive."

   # Avoid
   "This is fine as it is."
   "I don't think that's necessary."
   "The existing tests are good enough."
   ```

3. **Update and Re-request Review**
   - Make requested changes
   - Test changes thoroughly
   - Comment on what was updated

## 🚨 Quality Gates

### Minimum Requirements

Before merging, ensure:

1. **Test Execution**
   - [ ] All tests pass
   - [ ] No test failures or errors
   - [ ] No skipped tests without justification

2. **Coverage Requirements**
   ```bash
   # Minimum coverage by component
   Models: 85%
   Services: 80%
   Controllers: 75%
   Libraries: 70%
   Overall: 80%
   ```

3. **Performance Requirements**
   - [ ] Unit tests complete in < 30 seconds
   - [ ] Integration tests complete in < 5 minutes
   - [ ] No memory leaks detected
   - [ ] Performance benchmarks met

4. **Code Quality**
   - [ ] No syntax errors
   - [ ] No undefined variables
   - [ ] Proper error handling
   - [ ] Consistent code style

### Automated Quality Checks

```bash
# Code style check
phpcs --standard=PSR12 tests/

# Static analysis
phpstan analyze tests/ --level=8

# Security scan
psalm --show-info=true tests/

# Performance profiling
phpunit --group performance --log-junit performance.xml
```

### Manual Quality Checks

1. **Test Strategy Review**
   - Are the right things being tested?
   - Is the test approach appropriate?
   - Are there any missing scenarios?

2. **Documentation Review**
   - Are tests well documented?
   - Is the purpose clear?
   - Are complex scenarios explained?

3. **Maintainability Review**
   - Will these tests be easy to maintain?
   - Are they resistant to unnecessary changes?
   - Do they follow team conventions?

## 📚 Documentation Standards

### Test Documentation

**File-Level Documentation:**
```php
<?php
/**
 * Unit tests for CustomersModel
 *
 * This test suite covers the core customer management functionality
 * including client creation, contract management, and billing operations.
 *
 * Test Categories:
 * - Customer CRUD operations
 * - Contract lifecycle management
 * - Payment processing
 * - Data validation and security
 *
 * @package Tests\Unit\Models
 * @group models
 * @group customers
 * @covers CustomersModel
 */
class CustomersModelTest extends DatabaseTestCase
{
    // Test implementation
}
```

**Method Documentation:**
```php
/**
 * Test client activation workflow with router provisioning
 *
 * Verifies that when a client is activated:
 * 1. Contract status changes to 'active'
 * 2. PPPoE user is created on router
 * 3. Notification is sent to client
 * 4. Audit log entry is created
 *
 * @group critical
 * @group integration
 * @group mikrotik
 *
 * @covers ClientActivedService::activateClient
 * @covers Router::addPppoeUser
 */
public function testClientActivationWithRouterProvisioning(): void
```

### README Updates

When adding new test categories or patterns:

```markdown
## New Test Pattern: Service Integration Tests

### Purpose
Integration tests for service layer interactions with external systems.

### Usage
```php
class ExampleServiceIntegrationTest extends BaseTestCase
{
    use MocksExternalServices;

    public function testServiceIntegration(): void
    {
        // Test implementation
    }
}
```

### Commands
```bash
# Run service integration tests
phpunit --group service-integration
```
```

## 🔧 Maintenance Responsibilities

### Individual Responsibilities

**Test Authors:**
- Maintain tests they write
- Fix failing tests promptly
- Update tests when related code changes
- Monitor test performance

**Team Members:**
- Report flaky or problematic tests
- Suggest improvements to test infrastructure
- Share testing knowledge and patterns
- Review test-related pull requests

### Team Responsibilities

**Weekly:**
- Review test execution metrics
- Identify and fix flaky tests
- Update test documentation
- Clean up obsolete tests

**Monthly:**
- Analyze test coverage trends
- Review test performance metrics
- Update test infrastructure
- Evaluate new testing tools

**Quarterly:**
- Comprehensive test strategy review
- Test framework upgrades
- Training on new testing patterns
- Quality metrics analysis

### Test Hygiene

**Regular Cleanup:**
```bash
# Find obsolete tests
grep -r "markTestSkipped\|TODO\|FIXME" tests/

# Identify slow tests
phpunit --group slow --log-junit slow_tests.xml

# Check for unused test data
grep -r "createTest.*" tests/ | grep -v "function"
```

**Performance Monitoring:**
```bash
# Track test execution time
phpunit --log-junit execution_time.xml

# Monitor memory usage
phpunit --debug | grep "Memory:"

# Identify resource-heavy tests
phpunit --group performance --coverage-text
```

## 👥 Team Collaboration

### Communication Channels

**Test-Related Discussions:**
- Use specific channels for test discussions
- Tag relevant team members
- Provide context and examples
- Share test results and metrics

**Knowledge Sharing:**
- Regular testing workshops
- Code review sessions
- Test pattern documentation
- Best practice sharing

### Conflict Resolution

**When Tests Disagree:**
1. Discuss the approach openly
2. Consider multiple perspectives
3. Test both approaches if needed
4. Document the decision rationale
5. Update guidelines if necessary

**When Reviews Are Difficult:**
1. Focus on code quality and standards
2. Provide specific, actionable feedback
3. Be open to different approaches
4. Escalate to team lead if needed
5. Learn from the discussion

### Onboarding New Contributors

**New Team Member Checklist:**
- [ ] Environment setup completed
- [ ] Test framework overview session
- [ ] Pair programming on test writing
- [ ] First test contribution merged
- [ ] Familiar with review process

**Mentoring Guidelines:**
1. Start with simple test additions
2. Gradually introduce complex patterns
3. Provide regular feedback
4. Encourage questions and discussion
5. Review their contributions thoroughly

## 📈 Continuous Improvement

### Feedback Mechanisms

**Regular Retrospectives:**
- What's working well with our testing?
- What challenges are we facing?
- What improvements can we make?
- What new tools or techniques should we try?

**Metrics Collection:**
- Test execution time trends
- Coverage improvement over time
- Bug detection effectiveness
- Developer productivity impact

### Innovation and Experimentation

**Trying New Approaches:**
- Test new testing tools in isolated branches
- Experiment with different patterns
- Gather feedback from the team
- Document learnings and decisions

**Staying Current:**
- Follow testing best practices
- Attend testing conferences/webinars
- Read testing blogs and articles
- Share interesting findings with the team

---

## 🎯 Getting Help

### Internal Resources
- Team testing channel
- Testing framework documentation
- Code review feedback
- Pair programming sessions

### External Resources
- PHPUnit documentation
- Testing best practices articles
- Community forums and discussions
- Testing tools documentation

### Escalation Process
1. Check existing documentation
2. Ask team members
3. Consult with team lead
4. Escalate to architecture team
5. External consultation if needed

---

Thank you for contributing to the ISP Management System testing framework! Your efforts help ensure the reliability and quality of our software. Remember: good tests today prevent bugs tomorrow.

**Happy Testing! 🧪✅**