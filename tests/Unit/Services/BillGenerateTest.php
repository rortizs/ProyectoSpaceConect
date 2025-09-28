<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * BillGenerate Unit Tests
 *
 * Tests for automated bill generation including customer selection,
 * bill creation, and service detail management.
 */
class BillGenerateTest extends BaseTestCase
{
    use MocksExternalServices;

    private BillGenerate $service;
    private $mockMysql;
    private $testClients;
    private $testServices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BillGenerate();
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

        $this->mockMysql = \Mockery::mock('Mysql');
    }

    private function setupTestData(): void
    {
        $this->testClients = [
            [
                'id' => 1,
                'clientid' => 1,
                'payday' => 15,
                'names' => 'John',
                'surnames' => 'Doe'
            ],
            [
                'id' => 2,
                'clientid' => 2,
                'payday' => 20,
                'names' => 'Jane',
                'surnames' => 'Smith'
            ]
        ];

        $this->testServices = [
            'total' => 150.00,
            'details' => [
                [
                    'id' => 1,
                    'service' => 'Internet 100Mbps',
                    'price' => 100.00
                ],
                [
                    'id' => 2,
                    'service' => 'Cable TV',
                    'price' => 50.00
                ]
            ]
        ];
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_creates_bills_for_eligible_clients(): void
    {
        // Arrange
        $filters = [];

        // Mock customer selection
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('contracts c')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->with('clients cl', 'c.clientid = cl.id')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with('c.state NOT IN(4)')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->with('c.id, c.clientid, c.payday, cl.names, cl.surnames')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getMany')
            ->andReturn($this->testClients);

        // Mock code generation
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT COUNT(internal_code) AS code FROM bills')
            ->andReturn(['code' => 0]);

        // Mock correlative generation for each client
        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT MAX\(correlative\)/'))
            ->andReturn(['correlative' => null]);

        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT until - available/'))
            ->andReturn(['used' => 1]);

        // Mock BillInfoService
        $mockBillInfoService = \Mockery::mock('BillInfoService');
        $mockBillInfoService->shouldReceive('execute')
            ->andReturn($this->testServices);

        // Mock bill insertion
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::type('array'))
            ->twice()
            ->andReturn(1);

        // Mock get last bill ID
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT MAX(id) AS id FROM bills')
            ->twice()
            ->andReturn(['id' => 1], ['id' => 2]);

        // Mock voucher series update
        $this->mockMysql->shouldReceive('update')
            ->with(\Mockery::pattern('/UPDATE voucher_series/'), \Mockery::type('array'))
            ->twice()
            ->andReturn(true);

        // Mock detail insertion
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO detail_bills/'), \Mockery::type('array'))
            ->times(4) // 2 clients × 2 services each
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(2, $result); // 2 bills generated
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_throws_exception_when_no_clients(): void
    {
        // Arrange
        $filters = [];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getMany')
            ->andReturn([]); // No clients

        $this->service->setMysql($this->mockMysql);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se pudo generar');

        $this->service->generate($filters);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_with_year_month_filter(): void
    {
        // Arrange
        $filters = ['year' => 2024, 'month' => 3];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('contracts c')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->with('clients cl', 'c.clientid = cl.id')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->with('c.state NOT IN(4)')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('andWhere')
            ->with('c.clientid NOT IN(SELECT b.clientid FROM bills b WHERE MONTH(b.billed_month) = 3 AND YEAR(b.billed_month) = 2024 AND b.state != 4 AND b.type = 2)')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getMany')
            ->andReturn($this->testClients);

        // Mock other dependencies similar to previous test
        $this->setupBillGenerationMocks();

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(2, $result);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_with_client_filter(): void
    {
        // Arrange
        $filters = ['clientId' => 1];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('andWhere')
            ->with('c.clientid = 1')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getMany')
            ->andReturn([$this->testClients[0]]); // Only first client

        $this->setupBillGenerationMocks(1); // Expect 1 client

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(1, $result);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_creates_correct_bill_code_for_first_bill(): void
    {
        // Arrange
        $filters = ['clientId' => 1];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientQuery($queryBuilder, [$this->testClients[0]]);

        // Mock first bill (code count = 0)
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT COUNT(internal_code) AS code FROM bills')
            ->andReturn(['code' => 0]);

        // Mock correlative and other operations
        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT MAX\(correlative\)/'))
            ->andReturn(['correlative' => null]);

        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT until - available/'))
            ->andReturn(['used' => 1]);

        // Mock BillInfoService
        $mockBillInfoService = \Mockery::mock('BillInfoService');
        $mockBillInfoService->shouldReceive('execute')
            ->andReturn($this->testServices);

        // Expect bill insertion with code "V00001"
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::that(function($data) {
                return $data[4] === 'V00001'; // internal_code field
            }))
            ->once()
            ->andReturn(1);

        $this->mockMysql->shouldReceive('select')
            ->with('SELECT MAX(id) AS id FROM bills')
            ->andReturn(['id' => 1]);

        $this->mockMysql->shouldReceive('update')
            ->andReturn(true);

        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO detail_bills/'), \Mockery::type('array'))
            ->twice()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(1, $result);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_creates_sequential_bill_codes(): void
    {
        // Arrange
        $filters = ['clientId' => 1];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientQuery($queryBuilder, [$this->testClients[0]]);

        // Mock existing bills (code count > 0)
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT COUNT(internal_code) AS code FROM bills')
            ->andReturn(['code' => 5]);

        // Mock max code
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT MAX(internal_code) AS code FROM bills')
            ->andReturn(['code' => 'V00005']);

        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT MAX\(correlative\)/'))
            ->andReturn(['correlative' => null]);

        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT until - available/'))
            ->andReturn(['used' => 1]);

        // Mock BillInfoService
        $mockBillInfoService = \Mockery::mock('BillInfoService');
        $mockBillInfoService->shouldReceive('execute')
            ->andReturn($this->testServices);

        // Expect bill insertion with next sequential code "V00006"
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::that(function($data) {
                return $data[4] === 'V00006'; // internal_code field
            }))
            ->once()
            ->andReturn(1);

        $this->mockMysql->shouldReceive('select')
            ->with('SELECT MAX(id) AS id FROM bills')
            ->andReturn(['id' => 1]);

        $this->mockMysql->shouldReceive('update')
            ->andReturn(true);

        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO detail_bills/'), \Mockery::type('array'))
            ->twice()
            ->andReturn(true);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(1, $result);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_set_issue_changes_bill_date(): void
    {
        // Arrange
        $customDate = '2024-03-15';

        // Act
        $this->service->setIssue($customDate);

        // Assert - Test that the date affects bill generation
        $filters = ['clientId' => 1];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientQuery($queryBuilder, [$this->testClients[0]]);

        $this->setupBillGenerationMocks(1);

        // Expect bill with custom issue date
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::that(function($data) use ($customDate) {
                return $data[6] === $customDate; // date_issue field
            }))
            ->once()
            ->andReturn(1);

        $this->service->setMysql($this->mockMysql);

        $result = $this->service->generate($filters);
        $this->assertEquals(1, $result);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_mysql_getter_returns_mysql_instance(): void
    {
        // Act
        $mysql = $this->service->getMysql();

        // Assert
        $this->assertInstanceOf('Mysql', $mysql);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_mysql_setter_changes_mysql_instance(): void
    {
        // Arrange
        $newMysql = \Mockery::mock('Mysql');

        // Act
        $this->service->setMysql($newMysql);

        // Assert
        $retrievedMysql = $this->service->getMysql();
        $this->assertSame($newMysql, $retrievedMysql);
    }

    /**
     * @test
     * @group bill-generation
     * @group services
     */
    public function test_generate_handles_bill_insertion_failure(): void
    {
        // Arrange
        $filters = ['clientId' => 1];

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupClientQuery($queryBuilder, [$this->testClients[0]]);

        $this->mockMysql->shouldReceive('select')
            ->andReturn(['code' => 0], ['correlative' => null], ['used' => 1]);

        $mockBillInfoService = \Mockery::mock('BillInfoService');
        $mockBillInfoService->shouldReceive('execute')
            ->andReturn($this->testServices);

        // Mock bill insertion failure
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::type('array'))
            ->andReturn(0); // Failure

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->generate($filters);

        // Assert
        $this->assertEquals(0, $result); // No bills generated due to failure
    }

    /**
     * Helper method to setup client query expectations
     */
    private function setupClientQuery($queryBuilder, $clients): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getMany')
            ->andReturn($clients);
    }

    /**
     * Helper method to setup common bill generation mocks
     */
    private function setupBillGenerationMocks(int $clientCount = 2): void
    {
        // Mock code generation
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT COUNT(internal_code) AS code FROM bills')
            ->andReturn(['code' => 0]);

        // Mock correlative generation
        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT MAX\(correlative\)/'))
            ->andReturn(['correlative' => null]);

        $this->mockMysql->shouldReceive('select')
            ->with(\Mockery::pattern('/SELECT until - available/'))
            ->andReturn(['used' => 1]);

        // Mock BillInfoService
        $mockBillInfoService = \Mockery::mock('BillInfoService');
        $mockBillInfoService->shouldReceive('execute')
            ->andReturn($this->testServices);

        // Mock bill insertion
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO bills/'), \Mockery::type('array'))
            ->times($clientCount)
            ->andReturn(1);

        // Mock get last bill ID
        $this->mockMysql->shouldReceive('select')
            ->with('SELECT MAX(id) AS id FROM bills')
            ->times($clientCount)
            ->andReturn(['id' => 1]);

        // Mock voucher series update
        $this->mockMysql->shouldReceive('update')
            ->times($clientCount)
            ->andReturn(true);

        // Mock detail insertion (2 services per client)
        $this->mockMysql->shouldReceive('insert')
            ->with(\Mockery::pattern('/INSERT INTO detail_bills/'), \Mockery::type('array'))
            ->times($clientCount * 2)
            ->andReturn(true);
    }
}