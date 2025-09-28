<?php

require_once __DIR__ . '/../../Support/DatabaseTestCase.php';

/**
 * BillsModel Unit Tests
 *
 * Tests for BillsModel functionality including:
 * - Bill creation and management
 * - Payment processing
 * - Invoice generation
 * - Mass operations
 * - Stock management
 * - Data export functionality
 */
class BillsModelTest extends DatabaseTestCase
{
    private BillsModel $model;
    private array $testClient;
    private array $testBill;
    private array $testContract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BillsModel();
        $this->seedEssentialData();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create test client
        $this->testClient = $this->createTestClient([
            'names' => 'Test Bill',
            'surnames' => 'Client',
            'document' => '87654321',
            'mobile' => '987654321',
            'email' => 'test.bill@example.com'
        ]);

        // Create test contract
        $this->testContract = $this->createTestContract(
            $this->testClient['idcliente'],
            1, // Assuming plan ID 1 exists
            ['payday' => 15, 'state' => 2]
        );

        // Create test voucher and series
        $voucherId = $this->insertTestData('vouchers', [
            'voucher' => 'Factura',
            'state' => 1
        ]);

        $serieId = $this->insertTestData('voucher_series', [
            'voucherid' => $voucherId,
            'serie' => 'F001',
            'from_number' => 1,
            'until' => 1000,
            'available' => 999,
            'state' => 1
        ]);

        // Create test bill
        $this->testBill = [
            'id' => $this->insertTestData('bills', [
                'userid' => 1,
                'clientid' => $this->testClient['idcliente'],
                'voucherid' => $voucherId,
                'serieid' => $serieId,
                'internal_code' => 'BILL001',
                'correlative' => '001',
                'date_issue' => date('Y-m-d'),
                'expiration_date' => date('Y-m-d', strtotime('+1 month')),
                'billed_month' => date('Y-m'),
                'subtotal' => 100.00,
                'discount' => 0.00,
                'total' => 100.00,
                'remaining_amount' => 100.00,
                'amount_paid' => 0.00,
                'type' => 2,
                'sales_method' => 1,
                'state' => 2
            ]),
            'voucherid' => $voucherId,
            'serieid' => $serieId
        ];
    }

    /**
     * @group critical
     */
    public function testCheckTicketNumberReturnsFalseForNonExistentTicket(): void
    {
        $result = $this->model->checkTicketNumber('NONEXISTENT123');
        $this->assertFalse($result);
    }

    /**
     * @group critical
     */
    public function testCheckTicketNumberReturnsTrueForExistingTicket(): void
    {
        $ticketNumber = 'BILL_TICKET123';
        $this->insertTestData('payments', [
            'billid' => $this->testBill['id'],
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'PAY001',
            'paytypeid' => 1,
            'payment_date' => date('Y-m-d H:i:s'),
            'amount_paid' => 50.00,
            'ticket_number' => $ticketNumber,
            'state' => 1
        ]);

        $result = $this->model->checkTicketNumber($ticketNumber);
        $this->assertNotFalse($result);
        $this->assertEquals($ticketNumber, $result['ticket_number']);
    }

    /**
     * @group critical
     */
    public function testListRecordsWithoutFiltersReturnsAllBills(): void
    {
        $result = $this->model->list_records('', '', '');

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));

        // Verify structure
        $firstBill = $result[0];
        $this->assertArrayHasKey('id', $firstBill);
        $this->assertArrayHasKey('client', $firstBill);
        $this->assertArrayHasKey('internal_code', $firstBill);
        $this->assertArrayHasKey('total', $firstBill);
        $this->assertArrayHasKey('state', $firstBill);
    }

    /**
     * @group critical
     */
    public function testListRecordsWithDateFilter(): void
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        $result = $this->model->list_records($startDate, $endDate, '');

        $this->assertIsArray($result);
        foreach ($result as $bill) {
            $billDate = date('Y-m-d', strtotime($bill['date_issue']));
            $this->assertGreaterThanOrEqual($startDate, $billDate);
            $this->assertLessThanOrEqual($endDate, $billDate);
        }
    }

    /**
     * @group critical
     */
    public function testListRecordsWithStateFilter(): void
    {
        $state = '2'; // Pending bills
        $result = $this->model->list_records('', '', $state);

        $this->assertIsArray($result);
        foreach ($result as $bill) {
            $this->assertEquals(2, $bill['state']);
        }
    }

    /**
     * @group critical
     */
    public function testListPendingBills(): void
    {
        $result = $this->model->list_pendings();

        $this->assertIsArray($result);
        foreach ($result as $bill) {
            $this->assertNotContains($bill['state'], [0, 1, 4]); // Not deleted, paid, or cancelled
        }
    }

    /**
     * @group critical
     */
    public function testCreateBillWithValidData(): void
    {
        $result = $this->model->create(
            1, // user
            $this->testClient['idcliente'],
            $this->testBill['voucherid'],
            $this->testBill['serieid'],
            'BILL002',
            '002',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '80.00',
            '0.00',
            '80.00',
            2,
            1,
            'Test bill creation'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'BILL002',
            'total' => '80.00'
        ]);
    }

    /**
     * @group critical
     */
    public function testModifyBillWithValidData(): void
    {
        $result = $this->model->modify(
            $this->testBill['id'],
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '90.00', // new subtotal
            '10.00', // discount
            '80.00', // new total
            'Modified bill',
            2 // state
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'id' => $this->testBill['id'],
            'subtotal' => '90.00',
            'discount' => '10.00',
            'total' => '80.00'
        ]);
    }

    /**
     * @group critical
     */
    public function testModifyAmountsWithStateUpdate(): void
    {
        $result = $this->model->modify_amounts(
            $this->testBill['id'],
            '50.00', // amount paid
            '50.00', // remaining
            1 // new state (paid)
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'id' => $this->testBill['id'],
            'remaining_amount' => '50.00',
            'state' => 1
        ]);
    }

    /**
     * @group critical
     */
    public function testModifyAmountsWithoutStateUpdate(): void
    {
        $result = $this->model->modify_amounts(
            $this->testBill['id'],
            '30.00',
            '70.00',
            0 // don't update state
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'id' => $this->testBill['id'],
            'remaining_amount' => '70.00',
            'state' => 2 // original state preserved
        ]);
    }

    /**
     * @group critical
     */
    public function testCreatePaymentWithValidData(): void
    {
        $result = $this->model->create_payment(
            $this->testBill['id'],
            1, // user
            $this->testClient['idcliente'],
            'PAY001',
            1, // payment type
            date('Y-m-d H:i:s'),
            'Test payment',
            '50.00', // amount paid
            '100.00', // total
            '50.00', // remaining
            1, // state
            'TICKET123',
            'REF456'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('payments', [
            'billid' => $this->testBill['id'],
            'internal_code' => 'PAY001',
            'amount_paid' => '50.00',
            'ticket_number' => 'TICKET123',
            'reference_number' => 'REF456'
        ]);
    }

    /**
     * @group critical
     */
    public function testCancelBill(): void
    {
        $result = $this->model->cancel($this->testBill['id']);

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'id' => $this->testBill['id'],
            'state' => 4 // cancelled
        ]);
    }

    /**
     * @group business-logic
     */
    public function testImportBillWithValidData(): void
    {
        $newClient = $this->createTestClient(['document' => '11111111']);

        $result = $this->model->import(
            1,
            $newClient['idcliente'],
            $this->testBill['voucherid'],
            $this->testBill['serieid'],
            'IMP001',
            '001',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '100.00',
            '0.00',
            '100.00',
            2,
            1,
            'Imported bill',
            '2',
            date('Y'),
            date('n')
        );

        $this->assertGreaterThan(0, $result);
        $this->assertDatabaseHas('bills', [
            'clientid' => $newClient['idcliente'],
            'internal_code' => 'IMP001'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testImportDuplicateBillReturnsZero(): void
    {
        $result = $this->model->import(
            1,
            $this->testClient['idcliente'], // Same client
            $this->testBill['voucherid'],
            $this->testBill['serieid'],
            'DUP001',
            '001',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'), // Same month as existing bill
            '100.00',
            '0.00',
            '100.00',
            2,
            1,
            'Duplicate bill',
            '2',
            date('Y'),
            date('n')
        );

        $this->assertEquals(0, $result);
    }

    /**
     * @group business-logic
     */
    public function testSelectInvoiceWithValidClient(): void
    {
        // Create a contract and service for invoice generation
        $serviceId = $this->insertTestData('services', [
            'internal_code' => 'SRV001',
            'service' => 'Internet Service',
            'type' => 1,
            'price' => 80.00,
            'state' => 1
        ]);

        $this->insertTestData('detail_contracts', [
            'contractid' => $this->testContract['idcontrato'],
            'serviceid' => $serviceId,
            'price' => 80.00
        ]);

        $result = $this->model->select_invoice($this->testClient['idcliente']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('detail', $result);
        $this->assertArrayHasKey('service', $result);

        if (!empty($result['invoice'])) {
            $this->assertEquals($this->testClient['idcliente'], $result['invoice']['idclient']);
        }
    }

    /**
     * @group business-logic
     */
    public function testViewBillReturnsCompleteData(): void
    {
        $result = $this->model->view_bill($this->testBill['id']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('bill', $result);
        $this->assertArrayHasKey('detail', $result);
        $this->assertArrayHasKey('business', $result);
        $this->assertArrayHasKey('payments', $result);
        $this->assertArrayHasKey('atm', $result);

        $this->assertEquals($this->testBill['id'], $result['bill']['id']);
    }

    /**
     * @group business-logic
     */
    public function testTotalPaidCalculation(): void
    {
        // Create some payments for the bill
        $this->insertTestData('payments', [
            'billid' => $this->testBill['id'],
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'PAY002',
            'paytypeid' => 1,
            'payment_date' => date('Y-m-d H:i:s'),
            'amount_paid' => 30.00,
            'state' => 1
        ]);

        $this->insertTestData('payments', [
            'billid' => $this->testBill['id'],
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'PAY003',
            'paytypeid' => 1,
            'payment_date' => date('Y-m-d H:i:s'),
            'amount_paid' => 20.00,
            'state' => 1
        ]);

        $totalPaid = $this->model->total_paid($this->testBill['id']);
        $this->assertEquals(50.00, $totalPaid);
    }

    /**
     * @group business-logic
     */
    public function testRemainingAmount(): void
    {
        $remaining = $this->model->remaining_amount($this->testBill['id']);
        $this->assertEquals(100.00, $remaining);

        // Update remaining amount
        $this->model->remaining_bill($this->testBill['id'], '75.00');
        $remaining = $this->model->remaining_amount($this->testBill['id']);
        $this->assertEquals(75.00, $remaining);
    }

    /**
     * @group business-logic
     */
    public function testCreateBillDetail(): void
    {
        $result = $this->model->create_datail(
            $this->testBill['id'],
            1, // type
            1, // product/service id
            'Internet Service',
            '1',
            '80.00',
            '80.00'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('detail_bills', [
            'billid' => $this->testBill['id'],
            'description' => 'Internet Service',
            'price' => '80.00'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testRemoveBillDetail(): void
    {
        // First create detail
        $this->model->create_datail(
            $this->testBill['id'],
            1,
            1,
            'Service to remove',
            '1',
            '50.00',
            '50.00'
        );

        // Then remove it
        $result = $this->model->remove_datail($this->testBill['id']);
        $this->assertEquals('success', $result);

        $this->assertDatabaseMissing('detail_bills', [
            'billid' => $this->testBill['id']
        ]);
    }

    /**
     * @group business-logic
     */
    public function testStockManagement(): void
    {
        // Create a product
        $productId = $this->insertTestData('products', [
            'name' => 'Test Product',
            'price' => 50.00,
            'stock' => 100,
            'state' => 1
        ]);

        // Test subtract stock
        $result = $this->model->subtract_stock($productId, 10);
        $this->assertEquals('success', $result);

        $product = $this->fetchTestData('products', ['id' => $productId]);
        $this->assertEquals(90, $product[0]['stock']);

        // Test increase stock
        $result = $this->model->increase_stock($productId, 5);
        $this->assertEquals('success', $result);

        $product = $this->fetchTestData('products', ['id' => $productId]);
        $this->assertEquals(95, $product[0]['stock']);
    }

    /**
     * @group business-logic
     */
    public function testVoucherSeriesManagement(): void
    {
        // Test decrease available
        $result = $this->model->modify_available(
            $this->testBill['voucherid'],
            $this->testBill['serieid']
        );
        $this->assertEquals('success', $result);

        $series = $this->fetchTestData('voucher_series', ['id' => $this->testBill['serieid']]);
        $this->assertEquals(998, $series[0]['available']);

        // Test increase available
        $result = $this->model->increase_serie(
            $this->testBill['voucherid'],
            $this->testBill['serieid']
        );
        $this->assertEquals('success', $result);

        $series = $this->fetchTestData('voucher_series', ['id' => $this->testBill['serieid']]);
        $this->assertEquals(999, $series[0]['available']);
    }

    /**
     * @group business-logic
     */
    public function testReturnCorrelative(): void
    {
        $correlative = $this->model->returnCorrelative(
            $this->testBill['voucherid'],
            $this->testBill['serieid']
        );

        $this->assertIsNumeric($correlative);
        $this->assertEquals('001', $correlative);
    }

    /**
     * @group business-logic
     */
    public function testReturnUsed(): void
    {
        $used = $this->model->returnUsed(
            $this->testBill['voucherid'],
            $this->testBill['serieid']
        );

        $this->assertIsNumeric($used);
        $this->assertGreaterThan(0, $used);
    }

    /**
     * @group business-logic
     */
    public function testCodeGeneration(): void
    {
        $count = $this->model->returnCode();
        $maxCode = $this->model->generateCode();

        $this->assertIsNumeric($count);
        $this->assertIsNumeric($maxCode);
        $this->assertGreaterThanOrEqual($count, $maxCode);
    }

    /**
     * @group business-logic
     */
    public function testPaymentCodeGeneration(): void
    {
        $count = $this->model->returnCodePayment();
        $maxCode = $this->model->generateCodePayment();

        $this->assertIsNumeric($count);
        $this->assertIsNumeric($maxCode);
        $this->assertGreaterThanOrEqual(0, $count);
        $this->assertGreaterThanOrEqual(0, $maxCode);
    }

    /**
     * @group business-logic
     */
    public function testStatePayments(): void
    {
        // Create a payment first
        $this->model->create_payment(
            $this->testBill['id'],
            1,
            $this->testClient['idcliente'],
            'PAY_STATE',
            1,
            date('Y-m-d H:i:s'),
            'Payment for state test',
            '25.00',
            '100.00',
            '75.00',
            1,
            '',
            ''
        );

        // Change payment state
        $result = $this->model->state_payments($this->testBill['id'], 2);
        $this->assertEquals('success', $result);

        $this->assertDatabaseHas('payments', [
            'billid' => $this->testBill['id'],
            'state' => 2
        ]);
    }

    /**
     * @group business-logic
     */
    public function testDebtOpening(): void
    {
        $month = date('n');
        $year = date('Y');

        $result = $this->model->debt_opening($month, $year);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('issued_invoices', $result);
        $this->assertArrayHasKey('total_clients', $result);
        $this->assertIsNumeric($result['issued_invoices']);
        $this->assertIsNumeric($result['total_clients']);
    }

    /**
     * @group business-logic
     */
    public function testDetailOpening(): void
    {
        $month = date('n');
        $year = date('Y');

        $result = $this->model->detail_opening($month, $year);

        $this->assertIsArray($result);
    }

    /**
     * @group validation
     */
    public function testExistingClient(): void
    {
        $result = $this->model->existing_client(
            $this->testClient['names'],
            $this->testClient['surnames']
        );

        $this->assertNotFalse($result);
        $this->assertEquals($this->testClient['names'], $result['names']);
        $this->assertEquals($this->testClient['surnames'], $result['surnames']);
    }

    /**
     * @group validation
     */
    public function testExistingClientReturnsFalseForNonExistent(): void
    {
        $result = $this->model->existing_client('Non', 'Existent');
        $this->assertFalse($result);
    }

    /**
     * @group edge-cases
     */
    public function testMassRegistrationWithValidData(): void
    {
        $newClient = $this->createTestClient(['document' => '55555555']);

        $result = $this->model->mass_registration(
            1,
            $newClient['idcliente'],
            $this->testBill['voucherid'],
            $this->testBill['serieid'],
            'MASS001',
            '001',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '150.00',
            '0.00',
            '150.00',
            2,
            1,
            'Mass registration test',
            '2'
        );

        $this->assertGreaterThan(0, $result);
        $this->assertDatabaseHas('bills', [
            'clientid' => $newClient['idcliente'],
            'internal_code' => 'MASS001'
        ]);
    }

    /**
     * @group error-handling
     */
    public function testModifyInvalidBillId(): void
    {
        $result = $this->model->modify(
            99999, // non-existent bill
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '100.00',
            '0.00',
            '100.00',
            'Invalid bill test',
            2
        );

        // Should return error for non-existent bill
        $this->assertEquals('error', $result);
    }

    /**
     * @group boundary-conditions
     */
    public function testCreateBillWithZeroAmount(): void
    {
        $result = $this->model->create(
            1,
            $this->testClient['idcliente'],
            $this->testBill['voucherid'],
            $this->testBill['serieid'],
            'ZERO001',
            '001',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '0.00',
            '0.00',
            '0.00',
            2,
            1,
            'Zero amount bill'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'internal_code' => 'ZERO001',
            'total' => '0.00'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testEditBillWithQueryBuilder(): void
    {
        $payload = [
            'observation' => 'Updated via query builder',
            'state' => 3
        ];

        $result = $this->model->edit_bill($this->testBill['id'], $payload);
        $this->assertTrue($result);

        $this->assertDatabaseHas('bills', [
            'id' => $this->testBill['id'],
            'observation' => 'Updated via query builder',
            'state' => 3
        ]);
    }
}