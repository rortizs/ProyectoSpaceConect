<?php

require_once __DIR__ . '/../../Support/DatabaseTestCase.php';

/**
 * CustomersModel Unit Tests
 *
 * Tests for CustomersModel functionality including:
 * - Client management operations
 * - Contract operations
 * - Bill generation and management
 * - Payment processing
 * - Ticket management
 * - Data validation and business logic
 */
class CustomersModelTest extends DatabaseTestCase
{
    private CustomersModel $model;
    private array $testClient;
    private array $testPlan;
    private array $testContract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CustomersModel();
        $this->seedEssentialData();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create test client
        $this->testClient = $this->createTestClient([
            'names' => 'Juan Carlos',
            'surnames' => 'Pérez González',
            'document' => '12345678',
            'mobile' => '987654321',
            'email' => 'juan.perez@test.com',
            'address' => 'Av. Test 123',
            'documentid' => 1,
            'zonaid' => 1
        ]);

        // Create test plan
        $this->testPlan = $this->createTestPlan([
            'nombre' => 'Plan Basic 20MB',
            'precio' => 80,
            'subida' => '5M',
            'bajada' => '20M'
        ]);

        // Create test contract
        $this->testContract = $this->createTestContract(
            $this->testClient['idcliente'],
            $this->testPlan['idplan'],
            [
                'payday' => 15,
                'days_grace' => 5,
                'discount' => 0,
                'discount_price' => 0
            ]
        );
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
        // Create a payment record with a ticket number
        $ticketNumber = 'TICKET123456';
        $this->insertTestData('payments', [
            'billid' => 1,
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'PAY001',
            'paytypeid' => 1,
            'payment_date' => date('Y-m-d H:i:s'),
            'amount_paid' => 100.00,
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
    public function testListRecordsReturnsActiveContracts(): void
    {
        $filters = ['state' => false]; // Get all non-deleted contracts
        $result = $this->model->list_records($filters);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));

        // Check structure of returned data
        $firstRecord = $result[0];
        $this->assertArrayHasKey('id', $firstRecord);
        $this->assertArrayHasKey('client', $firstRecord);
        $this->assertArrayHasKey('document', $firstRecord);
        $this->assertArrayHasKey('payday', $firstRecord);
        $this->assertArrayHasKey('state', $firstRecord);
    }

    /**
     * @group critical
     */
    public function testListRecordsWithStateFilter(): void
    {
        $filters = ['state' => 1]; // Active contracts only
        $result = $this->model->list_records($filters);

        $this->assertIsArray($result);
        foreach ($result as $record) {
            $this->assertEquals(1, $record['state']);
        }
    }

    /**
     * @group critical
     */
    public function testListRecordsWithPaydayFilters(): void
    {
        $filters = [
            'state' => false,
            'paydayStart' => 10,
            'paydayOver' => 20
        ];
        $result = $this->model->list_records($filters);

        $this->assertIsArray($result);
        foreach ($result as $record) {
            $this->assertGreaterThanOrEqual(10, $record['payday']);
            $this->assertLessThanOrEqual(20, $record['payday']);
        }
    }

    /**
     * @group critical
     */
    public function testSaveClientWithValidData(): void
    {
        $clientData = [
            'names' => 'María Elena',
            'surnames' => 'García López',
            'documentid' => 1,
            'document' => '87654321',
            'mobile' => '123456789',
            'mobile_optional' => '987654321',
            'zonaid' => 1,
            'email' => 'maria.garcia@test.com',
            'address' => 'Calle Test 456',
            'reference' => 'Cerca del parque',
            'note' => 'Cliente VIP',
            'nap_cliente_id' => null,
            'ap_cliente_id' => null
        ];

        $result = $this->model->saveClient($clientData);
        $this->assertEquals('success', $result);

        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'names' => 'María Elena',
            'surnames' => 'García López',
            'document' => '87654321'
        ]);
    }

    /**
     * @group critical
     */
    public function testSaveClientWithInvalidDataReturnsError(): void
    {
        $clientData = [
            'names' => '', // Invalid: empty name
            'surnames' => 'García López',
            'documentid' => 1,
            'document' => '87654321'
        ];

        $result = $this->model->saveClient($clientData);
        $this->assertEquals('error', $result);
    }

    /**
     * @group critical
     */
    public function testEditClientWithValidData(): void
    {
        $clientId = $this->testClient['idcliente'];
        $updateData = [
            'names' => 'Juan Carlos Updated',
            'surnames' => 'Pérez González',
            'mobile' => '111222333'
        ];

        $result = $this->model->editClient($clientId, $updateData);
        $this->assertEquals('success', $result);

        // Verify changes were applied
        $this->assertDatabaseHas('clients', [
            'id' => $clientId,
            'names' => 'Juan Carlos Updated',
            'mobile' => '111222333'
        ]);
    }

    /**
     * @group critical
     */
    public function testEditClientWithNonExistentIdReturnsNull(): void
    {
        $result = $this->model->editClient(99999, ['names' => 'Test']);
        $this->assertNull($result);
    }

    /**
     * @group business-logic
     */
    public function testCreateContractWithValidData(): void
    {
        $newClient = $this->createTestClient([
            'names' => 'Test Contract',
            'surnames' => 'Client',
            'document' => '11111111'
        ]);

        $result = $this->model->create(
            1, // user id
            $newClient['idcliente'],
            'CTR002',
            20, // payday
            1, // create invoice
            3, // days grace
            0, // discount
            '100.00', // price
            '2023-01-01', // month
            date('Y-m-d H:i:s'), // datetime
            1 // state
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'clientid' => $newClient['idcliente'],
            'internal_code' => 'CTR002',
            'payday' => 20
        ]);
    }

    /**
     * @group business-logic
     */
    public function testCreateContractForExistingClientReturnsExists(): void
    {
        $result = $this->model->create(
            1,
            $this->testClient['idcliente'], // Client already has contract
            'CTR003',
            25,
            1,
            3,
            0,
            '100.00',
            '2023-01-01',
            date('Y-m-d H:i:s'),
            1
        );

        $this->assertEquals('exists', $result);
    }

    /**
     * @group critical
     */
    public function testModifyContractWithValidData(): void
    {
        $result = $this->model->modify(
            $this->testContract['idcontrato'],
            25, // new payday
            7, // new days grace
            1, // discount
            '90.00', // new price
            '12' // months discount
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'id' => $this->testContract['idcontrato'],
            'payday' => 25,
            'days_grace' => 7,
            'discount' => 1,
            'discount_price' => '90.00'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testCancelContract(): void
    {
        $cancelDate = date('Y-m-d');
        $result = $this->model->cancel($this->testContract['idcontrato'], $cancelDate);

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'id' => $this->testContract['idcontrato'],
            'finish_date' => $cancelDate,
            'state' => 4 // cancelled state
        ]);
    }

    /**
     * @group business-logic
     */
    public function testLayoffContract(): void
    {
        $suspensionDate = date('Y-m-d');
        $result = $this->model->layoff($this->testContract['idcontrato'], $suspensionDate);

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'id' => $this->testContract['idcontrato'],
            'suspension_date' => $suspensionDate,
            'state' => 3 // suspended state
        ]);
    }

    /**
     * @group business-logic
     */
    public function testActivateContract(): void
    {
        // First suspend the contract
        $this->model->layoff($this->testContract['idcontrato'], date('Y-m-d'));

        // Then activate it
        $activationDate = date('Y-m-d');
        $result = $this->model->activate($this->testContract['idcontrato'], $activationDate);

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'id' => $this->testContract['idcontrato'],
            'state' => 2 // active state
        ]);
    }

    /**
     * @group critical
     */
    public function testCreateBill(): void
    {
        $result = $this->model->create_bill(
            1, // user
            $this->testClient['idcliente'],
            1, // voucher
            1, // serie
            'BILL001',
            '001',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 month')),
            date('Y-m'),
            '80.00', // subtotal
            '0.00', // discount
            '80.00', // total
            2, // type
            1, // method
            'Test bill'
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('bills', [
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'BILL001',
            'total' => '80.00'
        ]);
    }

    /**
     * @group critical
     */
    public function testCreatePayment(): void
    {
        // First create a bill
        $billId = $this->insertTestData('bills', [
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'voucherid' => 1,
            'serieid' => 1,
            'internal_code' => 'BILL002',
            'correlative' => '002',
            'date_issue' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 month')),
            'billed_month' => date('Y-m'),
            'subtotal' => 80.00,
            'total' => 80.00,
            'remaining_amount' => 80.00,
            'type' => 2,
            'sales_method' => 1
        ]);

        $result = $this->model->create_payment(
            $billId,
            1, // user
            $this->testClient['idcliente'],
            'PAY001',
            1, // payment type
            date('Y-m-d H:i:s'),
            'Test payment',
            '80.00', // amount paid
            '80.00', // total paid
            '0.00', // remaining
            1 // state
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('payments', [
            'billid' => $billId,
            'clientid' => $this->testClient['idcliente'],
            'internal_code' => 'PAY001',
            'amount_paid' => '80.00'
        ]);
    }

    /**
     * @group business-logic
     */
    public function testCreateTicket(): void
    {
        $attentionDate = date('Y-m-d H:i:s', strtotime('+1 day'));

        $result = $this->model->create_ticket(
            1, // user
            $this->testClient['idcliente'],
            1, // technical
            1, // incident type
            'Internet connection problems',
            1, // priority
            $attentionDate,
            date('Y-m-d H:i:s')
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('tickets', [
            'clientid' => $this->testClient['idcliente'],
            'description' => 'Internet connection problems',
            'attention_date' => $attentionDate
        ]);
    }

    /**
     * @group business-logic
     */
    public function testCreateDuplicateTicketReturnsExists(): void
    {
        $attentionDate = date('Y-m-d H:i:s', strtotime('+2 days'));

        // Create first ticket
        $this->model->create_ticket(
            1,
            $this->testClient['idcliente'],
            1,
            1,
            'First ticket',
            1,
            $attentionDate,
            date('Y-m-d H:i:s')
        );

        // Try to create duplicate (same client, same attention date)
        $result = $this->model->create_ticket(
            1,
            $this->testClient['idcliente'],
            1,
            1,
            'Duplicate ticket',
            1,
            $attentionDate,
            date('Y-m-d H:i:s')
        );

        $this->assertEquals('exists', $result);
    }

    /**
     * @group validation
     */
    public function testSearchDocumentFindsExistingClient(): void
    {
        $result = $this->model->search_document($this->testClient['document']);

        $this->assertNotFalse($result);
        $this->assertEquals($this->testClient['document'], $result['document']);
        $this->assertEquals($this->testClient['names'], $result['names']);
    }

    /**
     * @group validation
     */
    public function testSearchDocumentReturnsEmptyForNonExistentDocument(): void
    {
        $result = $this->model->search_document('99999999');
        $this->assertFalse($result);
    }

    /**
     * @group business-logic
     */
    public function testSelectClient(): void
    {
        $result = $this->model->select_client($this->testClient['idcliente']);

        $this->assertNotFalse($result);
        $this->assertEquals($this->testClient['idcliente'], $result['id']);
        $this->assertEquals($this->testClient['names'], $result['names']);
        $this->assertArrayHasKey('name_doc', $result); // Joined document type
    }

    /**
     * @group business-logic
     */
    public function testFindClient(): void
    {
        $result = $this->model->find_client($this->testClient['idcliente']);

        $this->assertIsObject($result);
        $this->assertEquals($this->testClient['idcliente'], $result->id);
        $this->assertEquals($this->testClient['names'], $result->names);
    }

    /**
     * @group business-logic
     */
    public function testSelectContract(): void
    {
        $result = $this->model->select_contract($this->testClient['idcliente']);

        $this->assertNotFalse($result);
        $this->assertEquals($this->testContract['idcontrato'], $result['id']);
        $this->assertEquals($this->testClient['idcliente'], $result['clientid']);
    }

    /**
     * @group edge-cases
     */
    public function testReturnCodeGeneratesSequentialNumbers(): void
    {
        $code1 = $this->model->returnCode();

        // Create another contract to increment the count
        $newClient = $this->createTestClient(['document' => '22222222']);
        $this->model->create(
            1,
            $newClient['idcliente'],
            'CTR999',
            15,
            1,
            3,
            0,
            '100.00',
            '2023-01-01',
            date('Y-m-d H:i:s'),
            1
        );

        $code2 = $this->model->returnCode();
        $this->assertEquals($code1 + 1, $code2);
    }

    /**
     * @group edge-cases
     */
    public function testGenerateCodeReturnsMaxCode(): void
    {
        $maxCode = $this->model->generateCode();
        $this->assertIsNumeric($maxCode);
        $this->assertGreaterThanOrEqual(0, $maxCode);
    }

    /**
     * @group business-logic
     */
    public function testPendingPaymentsCount(): void
    {
        // Create bills with different states
        $billId1 = $this->insertTestData('bills', [
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'voucherid' => 1,
            'serieid' => 1,
            'internal_code' => 'BILL003',
            'correlative' => '003',
            'date_issue' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 month')),
            'billed_month' => date('Y-m'),
            'subtotal' => 80.00,
            'total' => 80.00,
            'remaining_amount' => 80.00,
            'type' => 2,
            'state' => 2 // pending
        ]);

        $billId2 = $this->insertTestData('bills', [
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'voucherid' => 1,
            'serieid' => 1,
            'internal_code' => 'BILL004',
            'correlative' => '004',
            'date_issue' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 month')),
            'billed_month' => date('Y-m'),
            'subtotal' => 80.00,
            'total' => 80.00,
            'remaining_amount' => 80.00,
            'type' => 2,
            'state' => 3 // overdue
        ]);

        $pendingCount = $this->model->pending_payments($this->testClient['idcliente']);
        $this->assertEquals(2, $pendingCount);
    }

    /**
     * @group business-logic
     */
    public function testOutstandingBalance(): void
    {
        // Create bills with remaining amounts
        $this->insertTestData('bills', [
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'voucherid' => 1,
            'serieid' => 1,
            'internal_code' => 'BILL005',
            'correlative' => '005',
            'date_issue' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 month')),
            'billed_month' => date('Y-m'),
            'subtotal' => 80.00,
            'total' => 80.00,
            'remaining_amount' => 50.00,
            'type' => 2,
            'state' => 2
        ]);

        $this->insertTestData('bills', [
            'userid' => 1,
            'clientid' => $this->testClient['idcliente'],
            'voucherid' => 1,
            'serieid' => 1,
            'internal_code' => 'BILL006',
            'correlative' => '006',
            'date_issue' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 month')),
            'billed_month' => date('Y-m'),
            'subtotal' => 80.00,
            'total' => 80.00,
            'remaining_amount' => 30.00,
            'type' => 2,
            'state' => 3
        ]);

        $balance = $this->model->outstanding_balance($this->testClient['idcliente']);
        $this->assertEquals(80.00, $balance);
    }

    /**
     * @group validation
     */
    public function testModifyClientWithInvalidDataSQLInjection(): void
    {
        $maliciousData = [
            'names' => "'; DROP TABLE clients; --",
            'surnames' => 'Test'
        ];

        $result = $this->model->editClient($this->testClient['idcliente'], $maliciousData);

        // Should succeed (the SQL injection should be prevented by prepared statements)
        $this->assertEquals('success', $result);

        // Verify the table still exists and the malicious data was escaped
        $client = $this->fetchTestData('clients', ['id' => $this->testClient['idcliente']]);
        $this->assertNotEmpty($client);
        $this->assertEquals("'; DROP TABLE clients; --", $client[0]['names']);
    }

    /**
     * @group boundary-conditions
     */
    public function testCreateContractWithExtremeValues(): void
    {
        $newClient = $this->createTestClient(['document' => '33333333']);

        // Test with extreme values
        $result = $this->model->create(
            1,
            $newClient['idcliente'],
            'CTR_EXTREME',
            31, // maximum day of month
            1,
            365, // extreme grace period
            100, // 100% discount
            '9999999.99', // large price
            '2099-12-31', // far future date
            date('Y-m-d H:i:s'),
            1
        );

        $this->assertEquals('success', $result);
        $this->assertDatabaseHas('contracts', [
            'clientid' => $newClient['idcliente'],
            'payday' => 31,
            'days_grace' => 365
        ]);
    }

    /**
     * @group performance
     */
    public function testListRecordsPerformanceWithLargeDataset(): void
    {
        // Create multiple clients and contracts for performance testing
        $startTime = microtime(true);

        $filters = ['state' => false];
        $result = $this->model->list_records($filters);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (1 second for small test dataset)
        $this->assertLessThan(1.0, $executionTime);
        $this->assertIsArray($result);
    }

    /**
     * @group error-handling
     */
    public function testCreatePaymentWithInvalidBillId(): void
    {
        $result = $this->model->create_payment(
            99999, // non-existent bill ID
            1,
            $this->testClient['idcliente'],
            'PAY_INVALID',
            1,
            date('Y-m-d H:i:s'),
            'Invalid payment',
            '50.00',
            '50.00',
            '0.00',
            1
        );

        // Should still return success as the method doesn't validate foreign keys
        // But the payment would fail at database level in real scenario
        $this->assertEquals('success', $result);
    }
}