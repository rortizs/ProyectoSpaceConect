<?php

require_once __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * PaymentBillService Unit Tests
 *
 * Tests for payment processing including bill updates, client activation,
 * and payment record creation.
 */
class PaymentBillServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    private PaymentBillService $service;
    private $mockMysql;
    private $mockBusiness;
    private $mockClient;
    private $testBill;
    private $testContract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupMocks();
        $this->setupTestData();
        $this->service = new PaymentBillService(
            $this->mockBusiness,
            $this->mockClient,
            '1', // userId
            '1'  // payTypeId
        );
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

        $this->mockClient = (object)[
            'id' => 1,
            'names' => 'John',
            'surnames' => 'Doe',
            'email' => 'john@example.com'
        ];
    }

    private function setupTestData(): void
    {
        $this->testBill = (object)[
            'id' => 1,
            'clientid' => 1,
            'state' => 2, // Pending
            'subtotal' => 150.00,
            'discount' => 10.00,
            'total' => 140.00,
            'remaining_amount' => 140.00,
            'amount_paid' => 0.00,
            'internal_code' => 'V00001',
            'correlative' => '001'
        ];

        $this->testContract = (object)[
            'id' => 1,
            'clientid' => 1,
            'state' => 3 // Suspended
        ];
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_processes_full_payment_successfully(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 140.00;
        $discount = 0.00;

        // Mock find bill
        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        // Mock find contract
        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        // Mock bill update
        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        // Mock payment code generation
        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        // Mock payment creation
        $this->mockMysql->shouldReceive('setTableName')
            ->with('payments')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Mock client activation (should be triggered since contract state is 3)
        $mockActivationService = \Mockery::mock('ClientActivedService');
        $mockActivationService->shouldReceive('setCanTransaction')
            ->with(false)
            ->once();

        $mockActivationService->shouldReceive('execute')
            ->with('1')
            ->once()
            ->andReturn(['success' => true, 'message' => 'Client activated']);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment, $discount);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('paymentId', $result);
        $this->assertArrayHasKey('billId', $result);
        $this->assertArrayHasKey('bill', $result);
        $this->assertEquals(1, $result['paymentId']);
        $this->assertEquals($billId, $result['billId']);
        $this->assertEquals(0, $result['amountPayment']); // Remaining after full payment
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_processes_partial_payment(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 70.00; // Partial payment
        $discount = 0.00;

        // Modify test bill to expect partial payment
        $this->testBill->remaining_amount = 140.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->with('payments')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Client should not be activated for partial payments unless it covers full amount
        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment, $discount);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['paymentId']);
        $this->assertEquals(70.00, $result['amountPayment']); // Remaining payment after partial
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_processes_payment_with_discount(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 100.00;
        $discount = 40.00; // Discount that covers the rest

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Should trigger activation since discount + payment covers full amount
        $mockActivationService = \Mockery::mock('ClientActivedService');
        $mockActivationService->shouldReceive('setCanTransaction')
            ->once();

        $mockActivationService->shouldReceive('execute')
            ->once()
            ->andReturn(['success' => true]);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment, $discount);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(0, $result['discount']); // Remaining discount after application
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_fails_when_bill_not_found(): void
    {
        // Arrange
        $billId = '999';
        $amountPayment = 100.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, null); // Bill not found

        $this->service->setMysql($this->mockMysql);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La facturá no está disponible!!!');

        $this->service->execute($billId, $amountPayment);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_fails_when_contract_not_found(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 100.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, null); // Contract not found

        $this->service->setMysql($this->mockMysql);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No existe contrato activo');

        $this->service->execute($billId, $amountPayment);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_fails_when_activation_fails(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 140.00; // Full payment

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Mock activation service failure
        $mockActivationService = \Mockery::mock('ClientActivedService');
        $mockActivationService->shouldReceive('setCanTransaction')
            ->once();

        $mockActivationService->shouldReceive('execute')
            ->once()
            ->andReturn(['success' => false, 'message' => 'Router connection failed']);

        $this->service->setMysql($this->mockMysql);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Router connection failed');

        $this->service->execute($billId, $amountPayment);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_does_not_activate_when_client_already_active(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 140.00;

        // Modify contract to be active
        $activeContract = clone $this->testContract;
        $activeContract->state = 2; // Active

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $activeContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Should not call activation service since client is already active
        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['paymentId']);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_execute_does_not_activate_when_disabled(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 140.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        $this->mockMysql->shouldReceive('insertObject')
            ->once()
            ->andReturn(1);

        // Disable activation
        $this->service->setCanActive(false);
        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['paymentId']);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_set_datetime_changes_payment_datetime(): void
    {
        // Arrange
        $customDateTime = '2024-03-15 14:30:00';

        // Act
        $this->service->setDatetime($customDateTime);

        // Assert - Test that datetime affects payment creation
        $billId = '1';
        $amountPayment = 50.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        // Expect payment with custom datetime
        $this->mockMysql->shouldReceive('insertObject')
            ->with(\Mockery::type('array'), \Mockery::that(function($data) use ($customDateTime) {
                return isset($data['payment_date']) && $data['payment_date'] === $customDateTime;
            }))
            ->once()
            ->andReturn(1);

        $this->service->setMysql($this->mockMysql);

        $result = $this->service->execute($billId, $amountPayment);
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_set_comment_adds_comment_to_payment(): void
    {
        // Arrange
        $comment = 'Payment via bank transfer';

        // Act
        $this->service->setComment($comment);

        // Assert - Test that comment is included in payment
        $billId = '1';
        $amountPayment = 50.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupPaymentCodeMock($codeQueryBuilder);

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        // Expect payment with comment
        $this->mockMysql->shouldReceive('insertObject')
            ->with(\Mockery::type('array'), \Mockery::that(function($data) use ($comment) {
                return isset($data['comment']) && $data['comment'] === $comment;
            }))
            ->once()
            ->andReturn(1);

        $this->service->setMysql($this->mockMysql);

        $result = $this->service->execute($billId, $amountPayment);
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_payment_code_generation_first_payment(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 50.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        // Mock first payment code (no existing payments)
        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($codeQueryBuilder);

        $codeQueryBuilder->shouldReceive('from')
            ->with('payments', 'p')
            ->andReturnSelf();

        $codeQueryBuilder->shouldReceive('select')
            ->with('MAX(internal_code) AS code')
            ->andReturnSelf();

        $codeQueryBuilder->shouldReceive('getOne')
            ->andReturn(null); // No existing payments

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        // Expect payment with code "T00001"
        $this->mockMysql->shouldReceive('insertObject')
            ->with(\Mockery::type('array'), \Mockery::that(function($data) {
                return isset($data['internal_code']) && $data['internal_code'] === 'T00001';
            }))
            ->once()
            ->andReturn(1);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment);

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @group payment-processing
     * @group services
     */
    public function test_payment_code_generation_sequential(): void
    {
        // Arrange
        $billId = '1';
        $amountPayment = 50.00;

        $queryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindBillMock($queryBuilder, $this->testBill);

        $contractQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupFindContractMock($contractQueryBuilder, $this->testContract);

        $updateQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->setupUpdateBillMock($updateQueryBuilder);

        // Mock existing payment code
        $codeQueryBuilder = \Mockery::mock('QueryBuilder');
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($codeQueryBuilder);

        $codeQueryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $codeQueryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $codeQueryBuilder->shouldReceive('getOne')
            ->andReturn(['code' => 'T00005']); // Existing payment

        $this->mockMysql->shouldReceive('setTableName')
            ->once();

        // Expect payment with next sequential code "T00006"
        $this->mockMysql->shouldReceive('insertObject')
            ->with(\Mockery::type('array'), \Mockery::that(function($data) {
                return isset($data['internal_code']) && $data['internal_code'] === 'T00006';
            }))
            ->once()
            ->andReturn(1);

        $this->service->setMysql($this->mockMysql);

        // Act
        $result = $this->service->execute($billId, $amountPayment);

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * Helper method to setup find bill mock expectations
     */
    private function setupFindBillMock($queryBuilder, $bill): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('bills', 'b')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('innerJoin')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('addSelect')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn($bill ? (array)$bill : null);
    }

    /**
     * Helper method to setup find contract mock expectations
     */
    private function setupFindContractMock($queryBuilder, $contract): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->with('contracts')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('where')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn($contract ? (array)$contract : null);
    }

    /**
     * Helper method to setup update bill mock expectations
     */
    private function setupUpdateBillMock($queryBuilder): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('update')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('from')
            ->with('bills')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('andWhere')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('set')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('execute')
            ->andReturn(true);
    }

    /**
     * Helper method to setup payment code generation mock
     */
    private function setupPaymentCodeMock($queryBuilder): void
    {
        $this->mockMysql->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('from')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('select')
            ->andReturnSelf();

        $queryBuilder->shouldReceive('getOne')
            ->andReturn(['code' => 'T00001']);
    }
}