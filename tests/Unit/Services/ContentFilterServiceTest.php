<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * ContentFilterService Unit Tests
 *
 * Tests for content filtering functionality including policy management,
 * client assignments, and MikroTik router integration.
 */
class ContentFilterServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    private ContentFilterService $service;
    private $mockMysql;
    private $mockRouter;
    private $testCategories;
    private $testPolicies;
    private $testClient;
    private $testRouter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContentFilterService();
        $this->setupMocks();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        $this->resetServiceMocks();
        parent::tearDown();
    }

    private function setupMocks(): void
    {
        if (!class_exists('Mockery')) {
            $this->markTestSkipped('Mockery not available for mocking');
        }

        // Mock MySQL functions
        $this->mockMysql = \Mockery::mock('alias:SqlGlobals');

        // Mock MikroTik router
        $this->mockRouter = \Mockery::mock('RouterApi');
        $this->mockRouter->connected = true;
    }

    private function setupTestData(): void
    {
        $this->testCategories = [
            [
                'id' => 1,
                'name' => 'Adult Content',
                'is_active' => 1,
                'created_at' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Social Networks',
                'is_active' => 1,
                'created_at' => '2024-01-01 00:00:00'
            ]
        ];

        $this->testPolicies = [
            [
                'id' => 1,
                'name' => 'Family Safe',
                'description' => 'Blocks adult content',
                'is_active' => 1,
                'is_default' => 0,
                'created_at' => '2024-01-01 00:00:00'
            ]
        ];

        $this->testClient = (object)[
            'id' => 1,
            'names' => 'John',
            'surnames' => 'Doe',
            'net_ip' => '192.168.1.100',
            'email' => 'john@example.com'
        ];

        $this->testRouter = (object)[
            'id' => 1,
            'name' => 'Main Router',
            'ip' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'encrypted_password',
            'api_type' => 'auto'
        ];
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_categories_returns_active_categories(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM content_filter_categories WHERE is_active = 1 ORDER BY name";

        $this->mockMysql->shouldReceive('sql')
            ->with($expectedSql)
            ->once()
            ->andReturn($this->createMockResult($this->testCategories));

        // Act
        $result = $this->service->getCategories(true);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Adult Content', $result[0]['name']);
        $this->assertEquals('Social Networks', $result[1]['name']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_categories_returns_all_categories_when_active_only_false(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM content_filter_categories  ORDER BY name";

        $this->mockMysql->shouldReceive('sql')
            ->with($expectedSql)
            ->once()
            ->andReturn($this->createMockResult($this->testCategories));

        // Act
        $result = $this->service->getCategories(false);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_domains_by_categories_returns_domains_for_valid_categories(): void
    {
        // Arrange
        $categoryIds = [1, 2];
        $mockDomains = [
            ['domain' => 'adult.com', 'category_name' => 'Adult Content'],
            ['domain' => 'facebook.com', 'category_name' => 'Social Networks']
        ];

        $expectedSql = "SELECT d.domain, c.name as category_name
                FROM content_filter_domains d
                JOIN content_filter_categories c ON c.id = d.category_id
                WHERE d.category_id IN (1,2) AND d.is_active = 1 AND c.is_active = 1
                ORDER BY c.name, d.domain";

        $this->mockMysql->shouldReceive('sql')
            ->with($expectedSql)
            ->once()
            ->andReturn($this->createMockResult($mockDomains));

        // Act
        $result = $this->service->getDomainsByCategories($categoryIds);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains('adult.com', $result);
        $this->assertContains('facebook.com', $result);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_domains_by_categories_returns_empty_array_for_empty_categories(): void
    {
        // Act
        $result = $this->service->getDomainsByCategories([]);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_policies_returns_policies_with_categories(): void
    {
        // Arrange
        $expectedSql = "SELECT * FROM content_filter_policies WHERE is_active = 1 ORDER BY name";

        $this->mockMysql->shouldReceive('sql')
            ->with($expectedSql)
            ->once()
            ->andReturn($this->createMockResult($this->testPolicies));

        // Mock getPolicyCategories call
        $mockCategories = [
            ['id' => 1, 'name' => 'Adult Content', 'action' => 'block']
        ];

        $categorySql = "SELECT c.*, pc.action
                FROM content_filter_policy_categories pc
                JOIN content_filter_categories c ON c.id = pc.category_id
                WHERE pc.policy_id = 1 AND c.is_active = 1
                ORDER BY c.name";

        $this->mockMysql->shouldReceive('sql')
            ->with($categorySql)
            ->once()
            ->andReturn($this->createMockResult($mockCategories));

        // Act
        $result = $this->service->getPolicies(true);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Family Safe', $result[0]['name']);
        $this->assertArrayHasKey('categories', $result[0]);
        $this->assertIsArray($result[0]['categories']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     * @group integration
     */
    public function test_apply_policy_to_client_success(): void
    {
        // Arrange
        $clientId = 1;
        $policyId = 1;
        $routerId = 1;

        // Mock client lookup
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM clients WHERE id = {$clientId}")
            ->once()
            ->andReturn($this->testClient);

        // Mock router lookup
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM network_routers WHERE id = {$routerId}")
            ->once()
            ->andReturn($this->testRouter);

        // Mock policy categories
        $mockCategories = [
            ['id' => 1, 'name' => 'Adult Content', 'action' => 'block']
        ];

        $this->mockMysql->shouldReceive('sql')
            ->andReturn($this->createMockResult($mockCategories));

        // Mock domains
        $mockDomains = [
            ['domain' => 'adult.com', 'category_name' => 'Adult Content']
        ];

        $this->mockMysql->shouldReceive('sql')
            ->andReturn($this->createMockResult($mockDomains));

        // Mock router factory and API calls
        $mockRouterFactory = \Mockery::mock('alias:RouterFactory');
        $mockRouterFactory->shouldReceive('create')
            ->once()
            ->andReturn($this->mockRouter);

        $this->mockRouter->shouldReceive('APIApplyContentFilter')
            ->with($this->testClient->net_ip, ['adult.com'])
            ->once()
            ->andReturn(['success' => true, 'rules_added' => 1]);

        // Mock database operations
        $this->mockMysql->shouldReceive('sql')
            ->with(\Mockery::pattern('/UPDATE content_filter_client_policies/'))
            ->once();

        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_client_policies', \Mockery::type('object'))
            ->once()
            ->andReturn(1);

        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_logs', \Mockery::type('object'))
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->applyPolicyToClient($clientId, $policyId, $routerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Política de filtrado aplicada correctamente', $result['message']);
        $this->assertEquals(1, $result['domains_blocked']);
        $this->assertArrayHasKey('results', $result);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_apply_policy_to_client_fails_when_client_not_found(): void
    {
        // Arrange
        $clientId = 999;
        $policyId = 1;
        $routerId = 1;

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM clients WHERE id = {$clientId}")
            ->once()
            ->andReturn(null);

        // Mock logging
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_logs', \Mockery::type('object'))
            ->once();

        // Act
        $result = $this->service->applyPolicyToClient($clientId, $policyId, $routerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Cliente no encontrado', $result['message']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_apply_policy_to_client_fails_when_router_not_found(): void
    {
        // Arrange
        $clientId = 1;
        $policyId = 1;
        $routerId = 999;

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM clients WHERE id = {$clientId}")
            ->once()
            ->andReturn($this->testClient);

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM network_routers WHERE id = {$routerId}")
            ->once()
            ->andReturn(null);

        // Mock logging
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_logs', \Mockery::type('object'))
            ->once();

        // Act
        $result = $this->service->applyPolicyToClient($clientId, $policyId, $routerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Router no encontrado', $result['message']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_apply_policy_to_client_fails_when_no_domains_to_block(): void
    {
        // Arrange
        $clientId = 1;
        $policyId = 1;
        $routerId = 1;

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM clients WHERE id = {$clientId}")
            ->once()
            ->andReturn($this->testClient);

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM network_routers WHERE id = {$routerId}")
            ->once()
            ->andReturn($this->testRouter);

        // Mock empty policy categories
        $this->mockMysql->shouldReceive('sql')
            ->andReturn($this->createMockResult([]));

        // Mock logging
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_logs', \Mockery::type('object'))
            ->once();

        // Act
        $result = $this->service->applyPolicyToClient($clientId, $policyId, $routerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('No hay dominios para bloquear en esta política', $result['message']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_remove_policy_from_client_success(): void
    {
        // Arrange
        $clientId = 1;
        $routerId = 1;

        // Mock client lookup
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM clients WHERE id = {$clientId}")
            ->once()
            ->andReturn($this->testClient);

        // Mock client policy lookup
        $clientPolicy = (object)[
            'id' => 1,
            'client_id' => $clientId,
            'policy_id' => 1,
            'router_id' => $routerId,
            'is_active' => 1
        ];

        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM content_filter_client_policies WHERE client_id = {$clientId} AND router_id = {$routerId} AND is_active = 1")
            ->once()
            ->andReturn($clientPolicy);

        // Mock router lookup
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT * FROM network_routers WHERE id = {$routerId}")
            ->once()
            ->andReturn($this->testRouter);

        // Mock policy categories and domains
        $this->mockMysql->shouldReceive('sql')
            ->andReturn($this->createMockResult([
                ['id' => 1, 'name' => 'Adult Content', 'action' => 'block']
            ]));

        $this->mockMysql->shouldReceive('sql')
            ->andReturn($this->createMockResult([
                ['domain' => 'adult.com', 'category_name' => 'Adult Content']
            ]));

        // Mock router operations
        $mockRouterFactory = \Mockery::mock('alias:RouterFactory');
        $mockRouterFactory->shouldReceive('create')
            ->once()
            ->andReturn($this->mockRouter);

        $this->mockRouter->shouldReceive('APIRemoveContentFilter')
            ->with($this->testClient->net_ip, ['adult.com'])
            ->once()
            ->andReturn(['success' => true, 'rules_removed' => 1]);

        // Mock database update
        $this->mockMysql->shouldReceive('sql')
            ->with("UPDATE content_filter_client_policies SET is_active = 0 WHERE id = 1")
            ->once();

        // Mock logging
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_logs', \Mockery::type('object'))
            ->once();

        // Act
        $result = $this->service->removePolicyFromClient($clientId, $routerId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Política de filtrado removida correctamente', $result['message']);
        $this->assertEquals(1, $result['domains_unblocked']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_create_policy_success(): void
    {
        // Arrange
        $name = 'Test Policy';
        $description = 'Test policy description';
        $categoryIds = [1, 2];
        $isDefault = false;

        // Mock check for existing policy
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT id FROM content_filter_policies WHERE name = '{$name}'")
            ->once()
            ->andReturn(null);

        // Mock policy creation
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_policies', \Mockery::type('object'))
            ->once()
            ->andReturn(1);

        // Mock category assignments
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_policy_categories', \Mockery::type('object'))
            ->twice()
            ->andReturn(1);

        // Act
        $result = $this->service->createPolicy($name, $description, $categoryIds, $isDefault);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Política creada correctamente', $result['message']);
        $this->assertEquals(1, $result['policy_id']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_create_policy_fails_when_name_exists(): void
    {
        // Arrange
        $name = 'Existing Policy';
        $description = 'Test description';
        $categoryIds = [1];

        // Mock existing policy
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT id FROM content_filter_policies WHERE name = '{$name}'")
            ->once()
            ->andReturn((object)['id' => 1]);

        // Act
        $result = $this->service->createPolicy($name, $description, $categoryIds);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Ya existe una política con ese nombre', $result['message']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_create_default_policy_updates_existing_defaults(): void
    {
        // Arrange
        $name = 'New Default Policy';
        $description = 'Default policy description';
        $categoryIds = [1];
        $isDefault = true;

        // Mock policy name check
        $this->mockMysql->shouldReceive('sqlObject')
            ->with("SELECT id FROM content_filter_policies WHERE name = '{$name}'")
            ->once()
            ->andReturn(null);

        // Mock removing default flag from other policies
        $this->mockMysql->shouldReceive('sql')
            ->with("UPDATE content_filter_policies SET is_default = 0")
            ->once();

        // Mock policy creation
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_policies', \Mockery::type('object'))
            ->once()
            ->andReturn(1);

        // Mock category assignment
        $this->mockMysql->shouldReceive('sqlInsert')
            ->with('content_filter_policy_categories', \Mockery::type('object'))
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->createPolicy($name, $description, $categoryIds, $isDefault);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_client_policy_returns_current_policy(): void
    {
        // Arrange
        $clientId = 1;
        $routerId = 1;

        $expectedSql = "SELECT cp.*, p.name as policy_name, p.description as policy_description
                FROM content_filter_client_policies cp
                JOIN content_filter_policies p ON p.id = cp.policy_id
                WHERE cp.client_id = {$clientId} AND cp.router_id = {$routerId} AND cp.is_active = 1";

        $mockPolicy = (object)[
            'id' => 1,
            'client_id' => $clientId,
            'policy_id' => 1,
            'router_id' => $routerId,
            'policy_name' => 'Family Safe',
            'policy_description' => 'Blocks adult content'
        ];

        $this->mockMysql->shouldReceive('sqlObject')
            ->with($expectedSql)
            ->once()
            ->andReturn($mockPolicy);

        // Act
        $result = $this->service->getClientPolicy($clientId, $routerId);

        // Assert
        $this->assertIsObject($result);
        $this->assertEquals('Family Safe', $result->policy_name);
        $this->assertEquals($clientId, $result->client_id);
    }

    /**
     * @test
     * @group content-filter
     * @group services
     */
    public function test_get_filtering_logs_returns_formatted_logs(): void
    {
        // Arrange
        $clientId = 1;
        $limit = 50;

        $expectedSql = "SELECT l.*, c.names as client_name, r.name as router_name, p.name as policy_name
                FROM content_filter_logs l
                JOIN clients c ON c.id = l.client_id
                JOIN network_routers r ON r.id = l.router_id
                LEFT JOIN content_filter_policies p ON p.id = l.policy_id
                WHERE l.client_id = {$clientId}
                ORDER BY l.created_at DESC
                LIMIT {$limit}";

        $mockLogs = [
            [
                'id' => 1,
                'client_id' => $clientId,
                'router_id' => 1,
                'action' => 'apply',
                'status' => 'success',
                'client_name' => 'John Doe',
                'router_name' => 'Main Router',
                'policy_name' => 'Family Safe',
                'created_at' => '2024-01-01 12:00:00'
            ]
        ];

        $this->mockMysql->shouldReceive('sql')
            ->with($expectedSql)
            ->once()
            ->andReturn($this->createMockResult($mockLogs));

        // Act
        $result = $this->service->getFilteringLogs($clientId, $limit);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('apply', $result[0]['action']);
        $this->assertEquals('John Doe', $result[0]['client_name']);
    }

    /**
     * Helper method to create mock database result
     */
    private function createMockResult(array $data)
    {
        $mockResult = \Mockery::mock('mysqli_result');

        $callCount = 0;
        $mockResult->shouldReceive('mysqli_fetch_array')
            ->with(\Mockery::any(), MYSQLI_ASSOC)
            ->andReturnUsing(function() use ($data, &$callCount) {
                if ($callCount < count($data)) {
                    return $data[$callCount++];
                }
                return false;
            });

        return $mockResult;
    }
}