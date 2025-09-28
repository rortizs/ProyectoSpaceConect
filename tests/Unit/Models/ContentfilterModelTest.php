<?php

require_once __DIR__ . '/../../Support/DatabaseTestCase.php';

/**
 * ContentfilterModel Unit Tests
 *
 * Tests for ContentfilterModel functionality including:
 * - Content filtering statistics
 * - Client filtering policy management
 * - Category management
 * - Domain blocking operations
 * - Activity logging
 */
class ContentfilterModelTest extends DatabaseTestCase
{
    private ContentfilterModel $model;
    private array $testClient;
    private array $testRouter;
    private array $testPolicy;
    private array $testCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ContentfilterModel();
        $this->seedEssentialData();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create test router
        $this->testRouter = $this->createTestRouter([
            'nombre' => 'Content Filter Router',
            'host' => '192.168.1.1',
            'port' => 8728,
            'user' => 'admin',
            'password' => 'test123'
        ]);

        // Create test client with network configuration
        $this->testClient = $this->createTestClient([
            'names' => 'Content Filter',
            'surnames' => 'Test Client',
            'document' => '12345678',
            'net_ip' => '192.168.1.100',
            'net_router' => $this->testRouter['idrouter']
        ]);

        // Create content filter tables and test data
        $this->createContentFilterTables();
        $this->createContentFilterTestData();
    }

    private function createContentFilterTables(): void
    {
        // Create content filter related tables for testing
        $this->executeQuery("
            CREATE TABLE IF NOT EXISTS content_filter_policies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->executeQuery("
            CREATE TABLE IF NOT EXISTS content_filter_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->executeQuery("
            CREATE TABLE IF NOT EXISTS content_filter_domains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                domain VARCHAR(255) NOT NULL,
                category_id INT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_category (category_id)
            )
        ");

        $this->executeQuery("
            CREATE TABLE IF NOT EXISTS content_filter_client_policies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                router_id INT NOT NULL,
                policy_id INT NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_client_router (client_id, router_id, is_active)
            )
        ");

        $this->executeQuery("
            CREATE TABLE IF NOT EXISTS content_filter_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT,
                domain VARCHAR(255),
                action VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_client (client_id),
                INDEX idx_created (created_at)
            )
        ");
    }

    private function createContentFilterTestData(): void
    {
        // Create test category
        $this->testCategory = [
            'id' => $this->insertTestData('content_filter_categories', [
                'name' => 'Social Media',
                'description' => 'Social networking websites',
                'is_active' => 1
            ])
        ];

        // Create test policy
        $this->testPolicy = [
            'id' => $this->insertTestData('content_filter_policies', [
                'name' => 'Standard Filtering',
                'description' => 'Standard content filtering policy',
                'is_active' => 1
            ])
        ];

        // Create test domains
        $this->insertTestData('content_filter_domains', [
            'domain' => 'facebook.com',
            'category_id' => $this->testCategory['id'],
            'is_active' => 1
        ]);

        $this->insertTestData('content_filter_domains', [
            'domain' => 'twitter.com',
            'category_id' => $this->testCategory['id'],
            'is_active' => 1
        ]);

        $this->insertTestData('content_filter_domains', [
            'domain' => 'instagram.com',
            'category_id' => $this->testCategory['id'],
            'is_active' => 0 // Inactive domain
        ]);

        // Create test client policy
        $this->insertTestData('content_filter_client_policies', [
            'client_id' => $this->testClient['idcliente'],
            'router_id' => $this->testRouter['idrouter'],
            'policy_id' => $this->testPolicy['id'],
            'is_active' => 1
        ]);

        // Create test logs
        $this->insertTestData('content_filter_logs', [
            'client_id' => $this->testClient['idcliente'],
            'domain' => 'facebook.com',
            'action' => 'blocked',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->insertTestData('content_filter_logs', [
            'client_id' => $this->testClient['idcliente'],
            'domain' => 'twitter.com',
            'action' => 'blocked',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);
    }

    /**
     * @group critical
     */
    public function testGetFilteringStatsReturnsCorrectStructure(): void
    {
        $stats = $this->model->getFilteringStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_policies', $stats);
        $this->assertArrayHasKey('filtered_clients', $stats);
        $this->assertArrayHasKey('total_categories', $stats);
        $this->assertArrayHasKey('blocked_domains', $stats);
        $this->assertArrayHasKey('recent_activities', $stats);

        // Verify data types
        $this->assertIsNumeric($stats['total_policies']);
        $this->assertIsNumeric($stats['filtered_clients']);
        $this->assertIsNumeric($stats['total_categories']);
        $this->assertIsNumeric($stats['blocked_domains']);
        $this->assertIsNumeric($stats['recent_activities']);
    }

    /**
     * @group critical
     */
    public function testGetFilteringStatsReturnsCorrectCounts(): void
    {
        $stats = $this->model->getFilteringStats();

        // We have 1 active policy
        $this->assertEquals(1, $stats['total_policies']);

        // We have 1 client with active filtering
        $this->assertEquals(1, $stats['filtered_clients']);

        // We have 1 active category
        $this->assertEquals(1, $stats['total_categories']);

        // We have 2 active domains (facebook.com and twitter.com)
        $this->assertEquals(2, $stats['blocked_domains']);

        // We have 2 logs within last 24 hours
        $this->assertEquals(2, $stats['recent_activities']);
    }

    /**
     * @group critical
     */
    public function testGetClientsWithoutFilteringReturnsCorrectClients(): void
    {
        // Create a client without filtering
        $clientWithoutFilter = $this->createTestClient([
            'names' => 'No Filter',
            'surnames' => 'Client',
            'document' => '87654321',
            'net_ip' => '192.168.1.101',
            'net_router' => $this->testRouter['idrouter']
        ]);

        $clients = $this->model->getClientsWithoutFiltering();

        $this->assertIsArray($clients);
        $this->assertGreaterThan(0, count($clients));

        // Find our test client in the results
        $foundClient = null;
        foreach ($clients as $client) {
            if ($client['id'] == $clientWithoutFilter['idcliente']) {
                $foundClient = $client;
                break;
            }
        }

        $this->assertNotNull($foundClient);
        $this->assertEquals('No Filter', $foundClient['names']);
        $this->assertEquals('Client', $foundClient['surnames']);
        $this->assertArrayHasKey('router_name', $foundClient);

        // Verify that client with existing filtering is not in the list
        $clientWithFilter = null;
        foreach ($clients as $client) {
            if ($client['id'] == $this->testClient['idcliente']) {
                $clientWithFilter = $client;
                break;
            }
        }
        $this->assertNull($clientWithFilter);
    }

    /**
     * @group critical
     */
    public function testGetClientsWithoutFilteringWithRouterFilter(): void
    {
        // Create another router
        $anotherRouter = $this->createTestRouter([
            'nombre' => 'Another Router',
            'host' => '192.168.2.1'
        ]);

        // Create client on another router
        $clientOnAnotherRouter = $this->createTestClient([
            'names' => 'Another Router',
            'surnames' => 'Client',
            'document' => '11111111',
            'net_ip' => '192.168.2.100',
            'net_router' => $anotherRouter['idrouter']
        ]);

        // Get clients without filtering for specific router
        $clients = $this->model->getClientsWithoutFiltering($this->testRouter['idrouter']);

        $this->assertIsArray($clients);

        // Should not include client from another router
        $foundWrongRouterClient = false;
        foreach ($clients as $client) {
            if ($client['id'] == $clientOnAnotherRouter['idcliente']) {
                $foundWrongRouterClient = true;
                break;
            }
        }
        $this->assertFalse($foundWrongRouterClient);
    }

    /**
     * @group critical
     */
    public function testGetClientPolicyReturnsCorrectPolicy(): void
    {
        $policy = $this->model->getClientPolicy(
            $this->testClient['idcliente'],
            $this->testRouter['idrouter']
        );

        $this->assertIsObject($policy);
        $this->assertEquals($this->testClient['idcliente'], $policy->client_id);
        $this->assertEquals($this->testRouter['idrouter'], $policy->router_id);
        $this->assertEquals($this->testPolicy['id'], $policy->policy_id);
        $this->assertEquals('Standard Filtering', $policy->policy_name);
        $this->assertEquals(1, $policy->is_active);
    }

    /**
     * @group critical
     */
    public function testGetClientPolicyReturnsNullForNonExistentPolicy(): void
    {
        $policy = $this->model->getClientPolicy(99999, $this->testRouter['idrouter']);
        $this->assertNull($policy);
    }

    /**
     * @group critical
     */
    public function testGetCategoriesReturnsActiveCategories(): void
    {
        $categories = $this->model->getCategories();

        $this->assertIsArray($categories);
        $this->assertGreaterThan(0, count($categories));

        // Verify structure
        $firstCategory = $categories[0];
        $this->assertArrayHasKey('id', $firstCategory);
        $this->assertArrayHasKey('name', $firstCategory);
        $this->assertArrayHasKey('description', $firstCategory);
        $this->assertArrayHasKey('is_active', $firstCategory);

        // Verify all returned categories are active
        foreach ($categories as $category) {
            $this->assertEquals(1, $category['is_active']);
        }

        // Find our test category
        $foundTestCategory = false;
        foreach ($categories as $category) {
            if ($category['id'] == $this->testCategory['id']) {
                $foundTestCategory = true;
                $this->assertEquals('Social Media', $category['name']);
                break;
            }
        }
        $this->assertTrue($foundTestCategory);
    }

    /**
     * @group critical
     */
    public function testGetCategoriesReturnsAllCategoriesWhenActiveOnlyFalse(): void
    {
        // Create an inactive category
        $inactiveCategory = $this->insertTestData('content_filter_categories', [
            'name' => 'Inactive Category',
            'description' => 'This category is inactive',
            'is_active' => 0
        ]);

        $allCategories = $this->model->getCategories(false);
        $activeCategories = $this->model->getCategories(true);

        $this->assertGreaterThan(count($activeCategories), count($allCategories));

        // Find inactive category in all categories
        $foundInactiveInAll = false;
        foreach ($allCategories as $category) {
            if ($category['id'] == $inactiveCategory) {
                $foundInactiveInAll = true;
                $this->assertEquals(0, $category['is_active']);
                break;
            }
        }
        $this->assertTrue($foundInactiveInAll);

        // Verify inactive category is not in active-only list
        $foundInactiveInActive = false;
        foreach ($activeCategories as $category) {
            if ($category['id'] == $inactiveCategory) {
                $foundInactiveInActive = true;
                break;
            }
        }
        $this->assertFalse($foundInactiveInActive);
    }

    /**
     * @group business-logic
     */
    public function testGetFilteringStatsWithNoData(): void
    {
        // Clear all test data
        $this->executeQuery("DELETE FROM content_filter_policies");
        $this->executeQuery("DELETE FROM content_filter_categories");
        $this->executeQuery("DELETE FROM content_filter_domains");
        $this->executeQuery("DELETE FROM content_filter_client_policies");
        $this->executeQuery("DELETE FROM content_filter_logs");

        $stats = $this->model->getFilteringStats();

        $this->assertEquals(0, $stats['total_policies']);
        $this->assertEquals(0, $stats['filtered_clients']);
        $this->assertEquals(0, $stats['total_categories']);
        $this->assertEquals(0, $stats['blocked_domains']);
        $this->assertEquals(0, $stats['recent_activities']);
    }

    /**
     * @group business-logic
     */
    public function testGetClientsWithoutFilteringWithNoClients(): void
    {
        // Remove all clients with net_ip
        $this->executeQuery("UPDATE clients SET net_ip = NULL");

        $clients = $this->model->getClientsWithoutFiltering();
        $this->assertIsArray($clients);
        $this->assertEquals(0, count($clients));
    }

    /**
     * @group edge-cases
     */
    public function testGetFilteringStatsWithOnlyInactiveData(): void
    {
        // Deactivate all test data
        $this->executeQuery("UPDATE content_filter_policies SET is_active = 0");
        $this->executeQuery("UPDATE content_filter_categories SET is_active = 0");
        $this->executeQuery("UPDATE content_filter_domains SET is_active = 0");
        $this->executeQuery("UPDATE content_filter_client_policies SET is_active = 0");

        $stats = $this->model->getFilteringStats();

        $this->assertEquals(0, $stats['total_policies']);
        $this->assertEquals(0, $stats['filtered_clients']);
        $this->assertEquals(0, $stats['total_categories']);
        $this->assertEquals(0, $stats['blocked_domains']);

        // Recent activities should still count (no is_active filter on logs)
        $this->assertEquals(2, $stats['recent_activities']);
    }

    /**
     * @group edge-cases
     */
    public function testGetClientPolicyWithInactivePolicy(): void
    {
        // Deactivate the client policy
        $this->executeQuery("UPDATE content_filter_client_policies SET is_active = 0 WHERE client_id = {$this->testClient['idcliente']}");

        $policy = $this->model->getClientPolicy(
            $this->testClient['idcliente'],
            $this->testRouter['idrouter']
        );

        $this->assertNull($policy);
    }

    /**
     * @group performance
     */
    public function testGetFilteringStatsPerformance(): void
    {
        $startTime = microtime(true);

        $stats = $this->model->getFilteringStats();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time
        $this->assertLessThan(1.0, $executionTime);
        $this->assertIsArray($stats);
    }

    /**
     * @group performance
     */
    public function testGetClientsWithoutFilteringPerformance(): void
    {
        $startTime = microtime(true);

        $clients = $this->model->getClientsWithoutFiltering();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time
        $this->assertLessThan(1.0, $executionTime);
        $this->assertIsArray($clients);
    }

    /**
     * @group boundary-conditions
     */
    public function testGetFilteringStatsWithVeryOldLogs(): void
    {
        // Create very old log entries
        $this->insertTestData('content_filter_logs', [
            'client_id' => $this->testClient['idcliente'],
            'domain' => 'oldsite.com',
            'action' => 'blocked',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ]);

        $stats = $this->model->getFilteringStats();

        // Recent activities should only count logs from last 24 hours
        $this->assertEquals(2, $stats['recent_activities']); // Only our original 2 logs
    }

    /**
     * @group data-integrity
     */
    public function testGetClientPolicyWithMultipleRouterAssignments(): void
    {
        // Create another router
        $anotherRouter = $this->createTestRouter([
            'nombre' => 'Second Router',
            'host' => '192.168.3.1'
        ]);

        // Create another policy for the same client on different router
        $anotherPolicy = $this->insertTestData('content_filter_policies', [
            'name' => 'Another Policy',
            'description' => 'Different policy for different router',
            'is_active' => 1
        ]);

        $this->insertTestData('content_filter_client_policies', [
            'client_id' => $this->testClient['idcliente'],
            'router_id' => $anotherRouter['idrouter'],
            'policy_id' => $anotherPolicy,
            'is_active' => 1
        ]);

        // Should get the correct policy for the specific router
        $policy1 = $this->model->getClientPolicy(
            $this->testClient['idcliente'],
            $this->testRouter['idrouter']
        );

        $policy2 = $this->model->getClientPolicy(
            $this->testClient['idcliente'],
            $anotherRouter['idrouter']
        );

        $this->assertNotNull($policy1);
        $this->assertNotNull($policy2);
        $this->assertEquals($this->testPolicy['id'], $policy1->policy_id);
        $this->assertEquals($anotherPolicy, $policy2->policy_id);
        $this->assertNotEquals($policy1->policy_id, $policy2->policy_id);
    }

    /**
     * @group sql-injection
     */
    public function testGetClientsWithoutFilteringWithSQLInjection(): void
    {
        // Try SQL injection through router_id parameter
        $maliciousRouterId = "1; DROP TABLE clients; --";

        // Should not cause SQL injection (parameter should be sanitized)
        $clients = $this->model->getClientsWithoutFiltering($maliciousRouterId);

        // Should return empty array or handle gracefully
        $this->assertIsArray($clients);

        // Verify clients table still exists
        $this->assertDatabaseHas('clients', [
            'id' => $this->testClient['idcliente']
        ]);
    }

    /**
     * @group integration
     */
    public function testCompleteContentFilteringWorkflow(): void
    {
        // 1. Get initial stats
        $initialStats = $this->model->getFilteringStats();
        $this->assertGreaterThan(0, $initialStats['total_policies']);

        // 2. Get clients without filtering
        $clientsWithoutFilter = $this->model->getClientsWithoutFiltering();
        $initialUnfilteredCount = count($clientsWithoutFilter);

        // 3. Create a new client
        $newClient = $this->createTestClient([
            'names' => 'Workflow Test',
            'surnames' => 'Client',
            'document' => '99999999',
            'net_ip' => '192.168.1.200',
            'net_router' => $this->testRouter['idrouter']
        ]);

        // 4. Verify new client appears in unfiltered list
        $clientsAfterAdd = $this->model->getClientsWithoutFiltering();
        $this->assertEquals($initialUnfilteredCount + 1, count($clientsAfterAdd));

        // 5. Assign filtering policy to new client
        $this->insertTestData('content_filter_client_policies', [
            'client_id' => $newClient['idcliente'],
            'router_id' => $this->testRouter['idrouter'],
            'policy_id' => $this->testPolicy['id'],
            'is_active' => 1
        ]);

        // 6. Verify client no longer appears in unfiltered list
        $clientsAfterFilter = $this->model->getClientsWithoutFiltering();
        $this->assertEquals($initialUnfilteredCount, count($clientsAfterFilter));

        // 7. Verify client policy can be retrieved
        $assignedPolicy = $this->model->getClientPolicy(
            $newClient['idcliente'],
            $this->testRouter['idrouter']
        );
        $this->assertNotNull($assignedPolicy);
        $this->assertEquals($this->testPolicy['id'], $assignedPolicy->policy_id);

        // 8. Verify updated stats show increased filtered clients
        $finalStats = $this->model->getFilteringStats();
        $this->assertEquals($initialStats['filtered_clients'] + 1, $finalStats['filtered_clients']);
    }
}