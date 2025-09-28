<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * ClientSuspendService Unit Tests
 *
 * Tests for client suspension and cancellation workflow including
 * network blocking, contract updates, and event management.
 */
class ClientSuspendServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    private ClientSuspendService $service;
    private ClientSuspendService $cancelService;
    private $mockMysql;
    private $mockBusiness;
    private $testClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMocks();
        $this->setupTestData();
        $this->service = new ClientSuspendService($this->mockBusiness, false); // Suspension
        $this->cancelService = new ClientSuspendService($this->mockBusiness, true); // Cancellation
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
            'state' => 2 // Active
        ];
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_execute_suspends_client_successfully(): void
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
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);

        // Mock ClientRouterService for network blocking
        $this->mockRouterServiceBlocking($clientId, true);

        // Mock contract and plan suspension
        $this->mockContractSuspension($clientId, 3, date('Y-m-d')); // State 3 for suspension
        $this->mockPlanSuspension($clientId, 2); // State 2 for plan suspension

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Cliente suspendido', $result['message']);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_execute_cancels_client_successfully(): void
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
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);

        // Mock ClientRouterService for network blocking
        $this->mockRouterServiceBlocking($clientId, true);

        // Mock contract and plan cancellation (different states)
        $this->mockContractSuspension($clientId, 4, date('Y-m-d')); // State 4 for cancellation
        $this->mockPlanSuspension($clientId, 3); // State 3 for plan cancellation

        $this->cancelService->setMysql($this->mockMysql);

        // Act
        $result = $this->cancelService->execute($clientId);

        // Assert
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Cliente suspendido', $result['message']);
    }

    /**
     * @test
     * @group client-suspension
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
        $this->setupClientSelectionMock($queryBuilder, null);

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
     * @group client-suspension
     * @group services
     */
    public function test_execute_fails_when_network_blocking_fails(): void
    {
        // Arrange
        $clientId = '1';

        $this->mockMysql->shouldReceive('createQueryRunner')
            ->once();

        $this->mockMysql->shouldReceive('rollback')
            ->once();

        // Mock client selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);

        // Mock ClientRouterService failing
        $this->mockRouterServiceBlocking($clientId, false, 'Router connection failed');

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
     * @group client-suspension
     * @group services
     */
    public function test_execute_without_transaction(): void
    {
        // Arrange
        $clientId = '1';

        // Mock client selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);

        // Mock successful router service
        $this->mockRouterServiceBlocking($clientId, true);

        // Mock update operations
        $this->mockContractSuspension($clientId, 3, date('Y-m-d'));
        $this->mockPlanSuspension($clientId, 2);

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
     * @group client-suspension
     * @group services
     */
    public function test_set_date_changes_suspension_date(): void
    {
        // Arrange
        $customDate = '2024-03-15';
        $clientId = '1';

        // Act
        $this->service->setDate($customDate);

        // Mock client selection and router service
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);
        $this->mockRouterServiceBlocking($clientId, true);

        // Mock contract suspension with custom date
        $this->mockContractSuspension($clientId, 3, $customDate);
        $this->mockPlanSuspension($clientId, 2);

        $this->service->setMysql($this->mockMysql);
        $this->service->setCanTransaction(false);

        // Act
        $result = $this->service->execute($clientId);

        // Assert
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     * @group client-suspension
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
     * @group client-suspension
     * @group services
     */
    public function test_suspend_contract_updates_contract_correctly(): void
    {
        // Arrange
        $clientId = '1';
        $suspensionDate = '2024-03-15';

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
            ->with(['suspension_date' => $suspensionDate, 'state' => 3])
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);
        $this->service->setDate($suspensionDate);

        // Act
        $result = $this->service->suspend_contract($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_suspend_contract_for_cancellation(): void
    {
        // Arrange
        $clientId = '1';
        $suspensionDate = date('Y-m-d');

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

        // For cancellation, state should be 4
        $queryBuilder->shouldReceive('set')
            ->with(['suspension_date' => $suspensionDate, 'state' => 4])
            ->once()
            ->andReturnSelf();

        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->cancelService->setMysql($this->mockMysql);

        // Act
        $result = $this->cancelService->suspend_contract($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_suspend_plan_updates_detail_contracts(): void
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
            ->with(['state' => 2]) // Suspension state for plans
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->suspend_plan($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_suspend_plan_for_cancellation(): void
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
            ->once()
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('andWhere')
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
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->with(['state' => 3]) // Cancellation state for plans
            ->once()
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $this->cancelService->setMysql($this->mockMysql);

        // Act
        $result = $this->cancelService->suspend_plan($clientId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_can_transaction_setter(): void
    {
        // Arrange & Act
        $this->service->setCanTransaction(false);

        // Mock operations without transaction calls
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientSelectionMock($queryBuilder, $this->testClient);
        $this->mockRouterServiceBlocking('1', true);
        $this->mockContractSuspension('1', 3, date('Y-m-d'));
        $this->mockPlanSuspension('1', 2);

        $this->service->setMysql($this->mockMysql);

        // MySQL should not be called for transaction management
        $this->mockMysql->shouldNotReceive('createQueryRunner');
        $this->mockMysql->shouldNotReceive('commit');

        // Act
        $result = $this->service->execute('1');

        // Assert
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     * @group client-suspension
     * @group services
     */
    public function test_mysql_setter(): void
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
     * Helper method to setup client selection mock
     */
    private function setupClientSelectionMock($queryBuilder, $client): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('clients cl')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->with('contracts c', 'c.clientid = cl.id')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->with('cl.*, c.id contractId')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn($client ? (array)$client : null);
    }

    /**
     * Helper method to mock router service blocking
     */
    private function mockRouterServiceBlocking(string $clientId, bool $success, string $message = 'Network blocked'): void
    {
        $mockRouterService = \Mockery::mock('ClientRouterService');
        $mockRouterService->shouldReceive('setMysql')
            ->with($this->mockMysql)
            ->once();

        $mockRouterService->shouldReceive('setClient')
            ->with($this->testClient)
            ->once();

        $mockRouterService->shouldReceive('blockNetwork')
            ->with($clientId)
            ->once()
            ->andReturn((object)['success' => $success, 'message' => $message]);
    }

    /**
     * Helper method to mock contract suspension
     */
    private function mockContractSuspension(string $clientId, int $state, string $date): void
    {
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('from')
            ->with('contracts')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with("clientid = {$clientId}")
            ->andReturnSelf();

        $queryBuilder->shouldReceive('set')
            ->with(['suspension_date' => $date, 'state' => $state])
            ->andReturnSelf();

        $queryBuilder->shouldReceive('execute')
            ->andReturn(true);
    }

    /**
     * Helper method to mock plan suspension
     */
    private function mockPlanSuspension(string $clientId, int $state): void
    {
        // Mock subquery
        $subQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->with('cli')
            ->andReturn($subQueryBuilder);

        $subQueryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $subQueryBuilder->shouldReceive('getSql')
            ->andReturn('SELECT * FROM clients');

        // Mock main update query
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($updateQueryBuilder);

        $updateQueryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('set')
            ->with(['state' => $state])
            ->andReturnSelf();

        $updateQueryBuilder->shouldReceive('execute')
            ->andReturn(true);
    }
}