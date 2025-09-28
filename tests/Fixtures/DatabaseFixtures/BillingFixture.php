<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Billing Fixture
 *
 * Creates bills and payments with different states, methods, and scenarios.
 * Includes current, overdue, paid bills and various payment scenarios.
 */
class BillingFixture extends BaseFixture
{
    protected array $dependencies = ['EssentialDataFixture', 'PlansFixture', 'RouterFixture', 'ClientsFixture'];

    // Bill states
    const BILL_STATE_PENDING = 1;
    const BILL_STATE_PAID = 2;
    const BILL_STATE_OVERDUE = 3;
    const BILL_STATE_PARTIAL = 4;
    const BILL_STATE_CANCELLED = 5;

    // Payment methods
    const PAYMENT_CASH = 1;
    const PAYMENT_TRANSFER = 2;
    const PAYMENT_CARD = 3;
    const PAYMENT_DIGITAL = 4;

    public function getName(): string
    {
        return 'Billing';
    }

    public function load(): array
    {
        $this->log('Loading billing data...');

        $data = [];

        // Validate required tables exist
        $this->validateTables(['bills', 'payments']);

        // Create payment types if they don't exist
        $data['payment_types'] = $this->createPaymentTypes();

        // Get existing clients
        $clients = $this->getExistingClients();

        if (empty($clients)) {
            $this->log('No clients found. Billing fixture requires clients to be created first.');
            return $data;
        }

        // Create bills for different scenarios
        $data['bills'] = $this->createBills($clients);

        // Create payments for bills
        $data['payments'] = $this->createPayments($data['bills']);

        $this->log('Billing data loaded successfully');

        return $data;
    }

    /**
     * Create payment types
     */
    private function createPaymentTypes(): array
    {
        $paymentTypes = [
            [
                'id' => 1,
                'name' => 'Efectivo',
                'description' => 'Pago en efectivo',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Transferencia Bancaria',
                'description' => 'Transferencia o depósito bancario',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Tarjeta de Crédito/Débito',
                'description' => 'Pago con tarjeta',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Pago Digital',
                'description' => 'Yape, Plin, BIM, etc.',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdTypes = [];
        foreach ($paymentTypes as $type) {
            $id = $this->getOrCreate('payment_types', ['id' => $type['id']], $type);
            $createdTypes[] = array_merge($type, ['id' => $id]);
        }

        return $createdTypes;
    }

    /**
     * Get existing clients for billing
     */
    private function getExistingClients(): array
    {
        $sql = "SELECT c.*, co.id as contract_id FROM clients c
                LEFT JOIN contracts co ON c.id = co.clientid
                WHERE c.state IN (1, 2)
                ORDER BY c.id LIMIT 50";

        if ($this->db->query($sql)) {
            return $this->db->getResults();
        }

        return [];
    }

    /**
     * Create bills with various scenarios
     */
    private function createBills(array $clients): array
    {
        $createdBills = [];

        foreach ($clients as $client) {
            if (empty($client['contract_id'])) {
                continue; // Skip clients without contracts
            }

            // Create historical bills (last 6 months)
            $historicalBills = $this->createHistoricalBills($client);
            $createdBills = array_merge($createdBills, $historicalBills);

            // Create current month bill
            $currentBill = $this->createCurrentBill($client);
            if ($currentBill) {
                $createdBills[] = $currentBill;
            }
        }

        return $createdBills;
    }

    /**
     * Create historical bills for a client
     */
    private function createHistoricalBills(array $client): array
    {
        $historicalBills = [];
        $clientId = $client['id'];

        // Create bills for last 6 months
        for ($i = 6; $i >= 1; $i--) {
            $billDate = date('Y-m-d', strtotime("-{$i} months"));
            $billMonth = date('Y-m-01', strtotime("-{$i} months"));
            $expirationDate = date('Y-m-d', strtotime($billDate . ' +15 days'));

            // Determine bill characteristics based on client and month
            $billData = $this->getBillDataForPeriod($client, $i, $billDate, $expirationDate, $billMonth);

            try {
                $billId = $this->insert('bills', $billData);
                $historicalBills[] = array_merge($billData, ['id' => $billId]);
                $this->log("Created historical bill for client {$clientId}, month -{$i}");
            } catch (Exception $e) {
                $this->log("Failed to create historical bill for client {$clientId}: " . $e->getMessage());
            }
        }

        return $historicalBills;
    }

    /**
     * Create current month bill
     */
    private function createCurrentBill(array $client): ?array
    {
        $clientId = $client['id'];
        $billDate = date('Y-m-01'); // First day of current month
        $billMonth = date('Y-m-01');
        $expirationDate = date('Y-m-d', strtotime($billDate . ' +15 days'));

        $billData = $this->getCurrentBillData($client, $billDate, $expirationDate, $billMonth);

        try {
            $billId = $this->insert('bills', $billData);
            $this->log("Created current bill for client {$clientId}");
            return array_merge($billData, ['id' => $billId]);
        } catch (Exception $e) {
            $this->log("Failed to create current bill for client {$clientId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get bill data for historical period
     */
    private function getBillDataForPeriod(array $client, int $monthsAgo, string $billDate, string $expirationDate, string $billMonth): array
    {
        $clientId = $client['id'];
        static $correlativeCounter = 1;

        // Base service price
        $basePrice = $this->getClientServicePrice($client);

        // Apply variations for different scenarios
        $subtotal = $basePrice;
        $discount = 0.00;

        // Random discounts for some bills
        if (rand(1, 10) <= 2) { // 20% chance
            $discount = $basePrice * 0.1; // 10% discount
        }

        $total = $subtotal - $discount;

        // Determine bill state based on age and client characteristics
        $billState = $this->determineBillState($client, $monthsAgo);

        // Calculate payments
        $amountPaid = 0.00;
        $remainingAmount = $total;

        if ($billState === self::BILL_STATE_PAID) {
            $amountPaid = $total;
            $remainingAmount = 0.00;
        } elseif ($billState === self::BILL_STATE_PARTIAL) {
            $amountPaid = $total * rand(30, 80) / 100; // 30-80% paid
            $remainingAmount = $total - $amountPaid;
        }

        // Promise pay feature for overdue bills
        $promiseEnabled = 0;
        $promiseDate = null;
        $promiseSetDate = null;
        $promiseComment = '';

        if ($billState === self::BILL_STATE_OVERDUE && rand(1, 10) <= 3) { // 30% of overdue bills have promises
            $promiseEnabled = 1;
            $promiseDate = date('Y-m-d', strtotime($expirationDate . ' +' . rand(3, 10) . ' days'));
            $promiseSetDate = date('Y-m-d', strtotime($expirationDate . ' +' . rand(1, 3) . ' days'));
            $promiseComment = 'Cliente comprometido a pagar en fecha indicada';
        }

        return [
            'userid' => 1, // Admin user
            'clientid' => $clientId,
            'voucherid' => $this->getVoucherIdForClient($client),
            'serieid' => 1, // Default series
            'internal_code' => 'BILL-' . date('Ym', strtotime($billMonth)) . '-' . str_pad($correlativeCounter++, 6, '0', STR_PAD_LEFT),
            'correlative' => $correlativeCounter,
            'date_issue' => $billDate,
            'expiration_date' => $expirationDate,
            'billed_month' => $billMonth,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'remaining_amount' => $remainingAmount,
            'type' => 1, // Service bill
            'sales_method' => 1, // Regular
            'observation' => $this->getBillObservation($client, $monthsAgo),
            'promise_enabled' => $promiseEnabled,
            'promise_date' => $promiseDate,
            'promise_set_date' => $promiseSetDate,
            'promise_comment' => $promiseComment,
            'state' => $billState,
            'compromise_date' => $expirationDate
        ];
    }

    /**
     * Get current bill data
     */
    private function getCurrentBillData(array $client, string $billDate, string $expirationDate, string $billMonth): array
    {
        static $correlativeCounter = 10000;

        $clientId = $client['id'];
        $basePrice = $this->getClientServicePrice($client);

        $subtotal = $basePrice;
        $discount = 0.00;
        $total = $subtotal - $discount;

        // Current bills are typically pending
        $billState = self::BILL_STATE_PENDING;
        $amountPaid = 0.00;
        $remainingAmount = $total;

        // Some current bills might be paid already
        if (rand(1, 10) <= 3) { // 30% chance
            $billState = self::BILL_STATE_PAID;
            $amountPaid = $total;
            $remainingAmount = 0.00;
        }

        return [
            'userid' => 1,
            'clientid' => $clientId,
            'voucherid' => $this->getVoucherIdForClient($client),
            'serieid' => 1,
            'internal_code' => 'BILL-' . date('Ym', strtotime($billMonth)) . '-' . str_pad($correlativeCounter++, 6, '0', STR_PAD_LEFT),
            'correlative' => $correlativeCounter,
            'date_issue' => $billDate,
            'expiration_date' => $expirationDate,
            'billed_month' => $billMonth,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'remaining_amount' => $remainingAmount,
            'type' => 1,
            'sales_method' => 1,
            'observation' => 'Facturación mensual del servicio de internet',
            'promise_enabled' => 0,
            'promise_date' => null,
            'promise_set_date' => null,
            'promise_comment' => '',
            'state' => $billState,
            'compromise_date' => $expirationDate
        ];
    }

    /**
     * Get client service price
     */
    private function getClientServicePrice(array $client): float
    {
        // Default prices based on client type
        $isBusinessClient = $client['documentid'] == 2;
        $isPremiumClient = strpos($client['note'] ?? '', 'premium') !== false;
        $isStudentClient = strpos($client['email'] ?? '', 'estudiante') !== false;

        if ($isPremiumClient) {
            return 200.00;
        } elseif ($isBusinessClient) {
            return 120.00;
        } elseif ($isStudentClient) {
            return 40.00; // Student discount applied
        } else {
            return 50.00; // Standard residential
        }
    }

    /**
     * Determine bill state based on client and age
     */
    private function determineBillState(array $client, int $monthsAgo): int
    {
        $clientState = $client['state'] ?? 1;

        // Suspended/cancelled clients typically have overdue bills
        if ($clientState == 2) { // Suspended
            return rand(1, 10) <= 8 ? self::BILL_STATE_OVERDUE : self::BILL_STATE_PARTIAL;
        }

        if ($clientState == 3) { // Cancelled
            return self::BILL_STATE_CANCELLED;
        }

        // Active clients - older bills are more likely to be paid
        if ($monthsAgo >= 3) {
            return rand(1, 10) <= 9 ? self::BILL_STATE_PAID : self::BILL_STATE_PARTIAL;
        } elseif ($monthsAgo >= 2) {
            return rand(1, 10) <= 7 ? self::BILL_STATE_PAID : self::BILL_STATE_OVERDUE;
        } else {
            // Recent bills - mix of states
            $weights = [
                self::BILL_STATE_PAID => 50,
                self::BILL_STATE_PENDING => 30,
                self::BILL_STATE_OVERDUE => 15,
                self::BILL_STATE_PARTIAL => 5
            ];

            return $this->weightedRandom($weights);
        }
    }

    /**
     * Get voucher ID for client
     */
    private function getVoucherIdForClient(array $client): int
    {
        $isBusinessClient = $client['documentid'] == 2;
        return $isBusinessClient ? 1 : 2; // Factura for business, Boleta for residential
    }

    /**
     * Get bill observation
     */
    private function getBillObservation(array $client, int $monthsAgo): string
    {
        $observations = [
            'Facturación mensual del servicio de internet',
            'Servicio de internet residencial',
            'Servicio de internet empresarial',
            'Incluye soporte técnico 24/7',
            'Servicio sin interrupciones durante el mes'
        ];

        if ($monthsAgo >= 3) {
            $observations[] = 'Pago recibido sin observaciones';
            $observations[] = 'Cliente cumplió con los pagos en tiempo';
        }

        return $observations[array_rand($observations)];
    }

    /**
     * Create payments for bills
     */
    private function createPayments(array $bills): array
    {
        $createdPayments = [];

        foreach ($bills as $bill) {
            $billId = $bill['id'];
            $billState = $bill['state'];
            $amountPaid = $bill['amount_paid'];
            $total = $bill['total'];

            // Skip if no payment was made
            if ($amountPaid <= 0) {
                continue;
            }

            // Create payment records
            if ($billState === self::BILL_STATE_PAID) {
                // Full payment
                $payment = $this->createPaymentRecord($bill, $amountPaid, $total);
                if ($payment) {
                    $createdPayments[] = $payment;
                }
            } elseif ($billState === self::BILL_STATE_PARTIAL) {
                // Partial payment - might be multiple payments
                $payments = $this->createPartialPayments($bill, $amountPaid);
                $createdPayments = array_merge($createdPayments, $payments);
            }
        }

        return $createdPayments;
    }

    /**
     * Create single payment record
     */
    private function createPaymentRecord(array $bill, float $amount, float $totalBill, string $comment = ''): ?array
    {
        static $paymentCounter = 1;

        $paymentDate = $this->getPaymentDate($bill);
        $paymentMethod = $this->getRandomPaymentMethod();

        $payment = [
            'billid' => $bill['id'],
            'userid' => 1, // Admin user
            'clientid' => $bill['clientid'],
            'internal_code' => 'PAY-' . str_pad($paymentCounter++, 8, '0', STR_PAD_LEFT),
            'paytypeid' => $paymentMethod,
            'payment_date' => $paymentDate,
            'comment' => $comment ?: $this->getPaymentComment($paymentMethod),
            'amount_paid' => $amount,
            'amount_total' => $totalBill,
            'remaining_credit' => max(0, $amount - $totalBill),
            'state' => 1, // Confirmed
            'ticket_number' => $this->generateTicketNumber($paymentMethod),
            'reference_number' => $this->generateReferenceNumber($paymentMethod)
        ];

        try {
            $paymentId = $this->insert('payments', $payment);
            $this->log("Created payment for bill {$bill['id']}: amount {$amount}");
            return array_merge($payment, ['id' => $paymentId]);
        } catch (Exception $e) {
            $this->log("Failed to create payment for bill {$bill['id']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create partial payments
     */
    private function createPartialPayments(array $bill, float $totalPaid): array
    {
        $payments = [];
        $remainingAmount = $totalPaid;
        $paymentCount = rand(1, 3); // 1-3 partial payments

        for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
            $paymentAmount = ($i === $paymentCount - 1) ?
                $remainingAmount : // Last payment gets the remainder
                min($remainingAmount, $remainingAmount * rand(30, 70) / 100);

            $payment = $this->createPaymentRecord(
                $bill,
                $paymentAmount,
                $bill['total'],
                "Pago parcial " . ($i + 1) . " de {$paymentCount}"
            );

            if ($payment) {
                $payments[] = $payment;
                $remainingAmount -= $paymentAmount;
            }
        }

        return $payments;
    }

    /**
     * Get payment date relative to bill
     */
    private function getPaymentDate(array $bill): string
    {
        $billDate = $bill['date_issue'];
        $expirationDate = $bill['expiration_date'];
        $billState = $bill['state'];

        if ($billState === self::BILL_STATE_PAID) {
            // Paid bills - payment could be before or after expiration
            $daysAfterBill = rand(1, 30);
            return date('Y-m-d H:i:s', strtotime($billDate . " +{$daysAfterBill} days"));
        } else {
            // Partial payments - usually closer to expiration or after
            $daysAfterBill = rand(10, 45);
            return date('Y-m-d H:i:s', strtotime($billDate . " +{$daysAfterBill} days"));
        }
    }

    /**
     * Get random payment method
     */
    private function getRandomPaymentMethod(): int
    {
        $weights = [
            self::PAYMENT_CASH => 40,
            self::PAYMENT_TRANSFER => 30,
            self::PAYMENT_DIGITAL => 20,
            self::PAYMENT_CARD => 10
        ];

        return $this->weightedRandom($weights);
    }

    /**
     * Get payment comment based on method
     */
    private function getPaymentComment(int $paymentMethod): string
    {
        $comments = [
            self::PAYMENT_CASH => 'Pago en efectivo en oficina',
            self::PAYMENT_TRANSFER => 'Transferencia bancaria confirmada',
            self::PAYMENT_DIGITAL => 'Pago digital mediante aplicación móvil',
            self::PAYMENT_CARD => 'Pago con tarjeta de crédito/débito'
        ];

        return $comments[$paymentMethod] ?? 'Pago confirmado';
    }

    /**
     * Generate ticket number
     */
    private function generateTicketNumber(int $paymentMethod): string
    {
        switch ($paymentMethod) {
            case self::PAYMENT_CASH:
                return 'TICKET-' . date('Ymd') . '-' . rand(1000, 9999);
            case self::PAYMENT_TRANSFER:
                return 'TRF-' . rand(100000, 999999);
            case self::PAYMENT_DIGITAL:
                return 'DIG-' . rand(10000000, 99999999);
            case self::PAYMENT_CARD:
                return 'CARD-' . rand(1000000, 9999999);
            default:
                return 'TKT-' . rand(100000, 999999);
        }
    }

    /**
     * Generate reference number
     */
    private function generateReferenceNumber(int $paymentMethod): string
    {
        switch ($paymentMethod) {
            case self::PAYMENT_TRANSFER:
                return 'OP-' . date('Ymd') . rand(100000, 999999);
            case self::PAYMENT_DIGITAL:
                return 'YAPE-' . rand(10000000000, 99999999999);
            case self::PAYMENT_CARD:
                return 'AUTH-' . rand(100000, 999999);
            default:
                return '';
        }
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $current = 0;

        foreach ($weights as $value => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    /**
     * Get bills by state
     */
    public function getBillsByState(int $state): array
    {
        return array_filter($this->getCreatedData('bills'), function($item) use ($state) {
            return $item['data']['state'] === $state;
        });
    }

    /**
     * Get overdue bills
     */
    public function getOverdueBills(): array
    {
        return $this->getBillsByState(self::BILL_STATE_OVERDUE);
    }

    /**
     * Get pending bills
     */
    public function getPendingBills(): array
    {
        return $this->getBillsByState(self::BILL_STATE_PENDING);
    }

    /**
     * Get billing statistics
     */
    public function getBillingStats(): array
    {
        $bills = $this->getCreatedData('bills');
        $payments = $this->getCreatedData('payments');

        $stats = [
            'total_bills' => count($bills),
            'total_payments' => count($payments),
            'total_billed' => 0,
            'total_paid' => 0,
            'bills_by_state' => [],
            'payments_by_method' => []
        ];

        foreach ($bills as $bill) {
            $data = $bill['data'];
            $stats['total_billed'] += $data['total'];

            $state = $data['state'];
            $stats['bills_by_state'][$state] = ($stats['bills_by_state'][$state] ?? 0) + 1;
        }

        foreach ($payments as $payment) {
            $data = $payment['data'];
            $stats['total_paid'] += $data['amount_paid'];

            $method = $data['paytypeid'];
            $stats['payments_by_method'][$method] = ($stats['payments_by_method'][$method] ?? 0) + 1;
        }

        return $stats;
    }

    /**
     * Create test scenarios for billing edge cases
     */
    public function createTestScenarios(): array
    {
        $scenarios = [];

        // Create bulk billing data for performance testing
        if (defined('CREATE_BULK_DATA') && CREATE_BULK_DATA) {
            $scenarios['bulk_billing'] = $this->createBulkBilling(100);
        }

        // Create specific test scenarios
        $scenarios['edge_cases'] = $this->createEdgeCases();

        return $scenarios;
    }

    /**
     * Create bulk billing data
     */
    private function createBulkBilling(int $count): array
    {
        // This would create many bills quickly for performance testing
        $this->log("Bulk billing creation not implemented - would create {$count} bills");
        return [];
    }

    /**
     * Create edge case scenarios
     */
    private function createEdgeCases(): array
    {
        $edgeCases = [
            'zero_amount_bill' => 'Bill with zero amount',
            'overpayment' => 'Payment exceeding bill amount',
            'negative_discount' => 'Bill with negative discount',
            'future_bill_date' => 'Bill with future date',
            'past_expiration' => 'Bill expired long ago'
        ];

        // These would be specific test cases for error handling
        return $edgeCases;
    }
}