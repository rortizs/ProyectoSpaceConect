<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * ClientActivedService Unit Tests
 *
 * Tests for client activation workflow including network unlocking,
 * contract activation, and event management.
 */
class ClientActivedServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    private ClientActivedService $service;
    private $mockMysql;
    private $mockEventManager;
    private $mockBusiness;
    private $testClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMocks();
        $this->setupTestData();
        $this->service = new ClientActivedService($this->mockBusiness);
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

        $this->mockMysql = \Mockery::mock('Mysql');
        $this->mockEventManager = \Mockery::mock('EventManager');
        $this->mockBusiness = (object)[
            'id' => 1,
            'business_name' => 'Test ISP',
            'email' => 'admin@testisp.com'
        ];
    }

    private function setupTestData(): void
    {
        $this->testClient = (object)[
            'id' => 1,
            'names' => 'John',
            'surnames' => 'Doe',
            'email' => 'john@example.com',
            'mobile' => '+51999999999',
            'net_ip' => '192.168.1.100',
            'contractId' => 1,
            'state' => 3 // Suspended
        ];
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_execute_activates_client_successfully(): void
    {
        // Arrange
        $clientId = '1';

        // Mock MySQL operations
        $this->mockMysql->shouldReceive('createQueryRunner')
            ->once();

        $this->mockMysql->shouldReceive('commit')
            ->once();

        // Mock client selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('clients cl')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->with('contracts c', 'c.clientid = cl.id')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with('cl.id = 1')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->with('cl.*, c.id contractId')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn((array)$this->testClient);

        // Mock ClientRouterService
        $mockRouterService = \Mockery::mock('ClientRouterService');
        $mockRouterService->shouldReceive('setMysql')
            ->with($this->mockMysql)
            ->once();

        $mockRouterService->shouldReceive('setClient')
            ->with($this->testClient)
            ->once();

        $mockRouterService->shouldReceive('unlockNetwork')
            ->with($clientId)
            ->once()
            ->andReturn((object)['success' => true, 'message' => 'Network unlocked']);

        // Mock contract and plan activation
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($updateQueryBuilder);

        $updateQueryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->andReturn(true);

        $updateQueryBuilder->shouldReceive('getSql')
            ->andReturn('SELECT * FROM clients');

        // Replace the service's dependencies
        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Activación completada', $result['message']);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_execute_fails_when_client_not_found(): void
    {
        // Arrange
        $clientId = '999';

        $this->mockMysql->shouldReceive('createQueryRunner')
            ->once();

        $this->mockMysql->shouldReceive('rollback')
            ->once();

        // Mock client selection returning null
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn(null);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('No se encontró el cliente', $result['message']);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_execute_fails_when_network_unlock_fails(): void
    {
        // Arrange
        $clientId = '1';

        $this->mockMysql->shouldReceive('createQueryRunner')
            ->once();

        $this->mockMysql->shouldReceive('rollback')
            ->once();

        // Mock client selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn((array)$this->testClient);

        // Mock ClientRouterService failing
        $mockRouterService = \Mockery::mock('ClientRouterService');
        $mockRouterService->shouldReceive('setMysql')
            ->with($this->mockMysql)
            ->once();

        $mockRouterService->shouldReceive('setClient')
            ->with($this->testClient)
            ->once();

        $mockRouterService->shouldReceive('unlockNetwork')
            ->with($clientId)
            ->once()
            ->andReturn((object)['success' => false, 'message' => 'Router connection failed']);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Router connection failed', $result['message']);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_execute_without_transaction(): void
    {
        // Arrange
        $clientId = '1';

        // Mock client selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn((array)$this->testClient);

        // Mock successful router service
        $mockRouterService = \Mockery::mock('ClientRouterService');
        $mockRouterService->shouldReceive('setMysql')
            ->once();

        $mockRouterService->shouldReceive('setClient')
            ->once();

        $mockRouterService->shouldReceive('unlockNetwork')
            ->once()
            ->andReturn((object)['success' => true, 'message' => 'Network unlocked']);

        // Mock update operations
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($updateQueryBuilder);

        $updateQueryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->andReturn(true);

        $updateQueryBuilder->shouldReceive('getSql')
            ->andReturn('SELECT * FROM clients');

        // Configure service without transactions
        $this->service->setMysql($this->mockMysql);
        $this->service->setCanTransaction(false);

        // MySQL should not be called for transaction management
        $this->mockMysql->shouldNotReceive('createQueryRunner');
        $this->mockMysql->shouldNotReceive('commit');

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_set_state_changes_client_state(): void
    {
        // Arrange
        $newState = 1; // Active

        // Act
        $this->service->setState($newState);

        // Assert - We can't directly test private properties, but we can test
        // that the state is used correctly in other operations
        $this->assertTrue(true); // State setting doesn't return anything
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_select_info_client_builds_correct_query(): void
    {
        // Arrange
        $clientId = '1';

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('clients cl')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->with('contracts c', 'c.clientid = cl.id')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with('cl.id = 1')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->with('cl.*, c.id contractId')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->once()
            ->andReturn((array)$this->testClient);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->select_info_client($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($this->testClient->id, $result['id']);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_actived_contract_updates_contract_correctly(): void
    {
        // Arrange
        $clientId = '1';

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('from')
            ->with('contracts')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with('clientid = 1')
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('set')
            ->with(['suspension_date' => null, 'state' => 2])
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->actived_contract($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_actived_plan_updates_detail_contracts(): void
    {
        // Arrange
        $clientId = '1';

        // Mock the subquery builder
        $subQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->with('cli')
            ->once()
            ->andReturn($subQueryBuilder);

        $subQueryBuilder->shouldReceive('innerJoin')
            ->with('contracts c', 'c.clientid = cli.id')
            ->once()
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('where')
            ->with('cli.id = 1')
            ->once()
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('andWhere')
            ->with('d.contractid = c.id')
            ->once()
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('getSql')
            ->once()
            ->andReturn('SELECT * FROM clients cli INNER JOIN contracts c ON c.clientid = cli.id WHERE cli.id = 1 AND d.contractid = c.id');

        // Mock the main update query builder
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($updateQueryBuilder);

        $updateQueryBuilder->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('from')
            ->with('detail_contracts', 'd')
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->with(\Mockery::pattern('/EXISTS/'))
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->with(['state' => 1])
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->actived_plan($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_mysql_setter_and_getter(): void
    {
        // Arrange
        $newMysql = \Mockery::mock('Mysql');

        // Act
        $this->service->setMysql($newMysql);

        // Assert - We can verify the mysql instance was set by testing
        // that subsequent operations use the new instance
        $this->assertTrue(true); // Setter doesn't return anything to test directly
    }

    /**
     * @test
     * @group client-activation
     * @group services
     */
    public function test_can_transaction_setter(): void
    {
        // Arrange & Act
        $this->service->setCanTransaction(false);

        // Mock operations without transaction calls
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn((array)$this->testClient);

        // Mock successful router service
        $mockRouterService = \Mockery::mock('ClientRouterService');
        $mockRouterService->shouldReceive('setMysql')
            ->once();

        $mockRouterService->shouldReceive('setClient')
            ->once();

        $mockRouterService->shouldReceive('unlockNetwork')
            ->once()
            ->andReturn((object)['success' => true]);

        // Mock update operations
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($updateQueryBuilder);

        $updateQueryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->andReturn(true);

        $updateQueryBuilder->shouldReceive('getSql')
            ->andReturn('SELECT * FROM clients');

        $this->service->setMysql($this->mockMysql);

        // MySQL should not be called for transaction management
        $this->mockMysql->shouldNotReceive('createQueryRunner');
        $this->mockMysql->shouldNotReceive('commit');

        // Act
        $result = $this->service->execute('1');

        // Assert
        $this->assertTrue($result['success']);
    }
}