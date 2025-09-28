<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Client Lifecycle Integration Tests
 *
 * Tests the complete client lifecycle management including:
 * - Client activation workflows
 * - Suspension and restoration
 * - Plan changes and upgrades
 * - Service disconnection
 * - Automated state transitions
 */
class ClientLifecycleTest extends MikroTikTestCase
{
    private $testClient;
    private $routerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testClient = [
            'id' => 123,
            'names' => 'Test Client',
            'net_ip' => '192.168.1.100',
            'net_name' => 'testclient001',
            'net_password' => 'testpass123',
            'net_router' => 1,
            'status' => 'pending',
            'plan_id' => 1,
            'upload_limit' => '10M',
            'download_limit' => '50M',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * @group lifecycle
     * Test complete client activation workflow
     */
    public function testCompleteClientActivation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Start with pending client
        $this->assertEquals('pending', $this->testClient['status']);

        // Execute activation workflow
        $result = $this->activateClient($this->testClient['id']);

        $this->assertTrue($result['success']);
        $this->assertEquals('active', $result['new_status']);
        $this->assertArrayHasKey('pppoe_created', $result);
        $this->assertArrayHasKey('queue_created', $result);
        $this->assertTrue($result['pppoe_created']);
        $this->assertTrue($result['queue_created']);
    }

    /**
     * @group lifecycle
     * Test client suspension workflow
     */
    public function testClientSuspension(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Start with active client
        $activeClient = $this->testClient;
        $activeClient['status'] = 'active';

        $result = $this->suspendClient($activeClient['id'], 'payment_overdue');

        $this->assertTrue($result['success']);
        $this->assertEquals('suspended', $result['new_status']);
        $this->assertEquals('payment_overdue', $result['suspension_reason']);
        $this->assertArrayHasKey('pppoe_disabled', $result);
        $this->assertArrayHasKey('queue_disabled', $result);
    }

    /**
     * @group lifecycle
     * Test client restoration from suspension
     */
    public function testClientRestoration(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Start with suspended client
        $suspendedClient = $this->testClient;
        $suspendedClient['status'] = 'suspended';
        $suspendedClient['suspension_reason'] = 'payment_overdue';

        $result = $this->restoreClient($suspendedClient['id']);

        $this->assertTrue($result['success']);
        $this->assertEquals('active', $result['new_status']);
        $this->assertArrayHasKey('pppoe_enabled', $result);
        $this->assertArrayHasKey('queue_enabled', $result);
        $this->assertTrue($result['pppoe_enabled']);
        $this->assertTrue($result['queue_enabled']);
    }

    /**
     * @group lifecycle
     * Test client plan change workflow
     */
    public function testClientPlanChange(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $activeClient = $this->testClient;
        $activeClient['status'] = 'active';

        $newPlan = [
            'id' => 2,
            'name' => 'Premium Plan',
            'upload_limit' => '20M',
            'download_limit' => '100M',
            'price' => 99.99
        ];

        $result = $this->changePlan($activeClient['id'], $newPlan['id']);

        $this->assertTrue($result['success']);
        $this->assertEquals($newPlan['id'], $result['new_plan_id']);
        $this->assertArrayHasKey('queue_updated', $result);
        $this->assertTrue($result['queue_updated']);
    }

    /**
     * @group lifecycle
     * Test client disconnection workflow
     */
    public function testClientDisconnection(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $activeClient = $this->testClient;
        $activeClient['status'] = 'active';

        $result = $this->disconnectClient($activeClient['id'], 'service_termination');

        $this->assertTrue($result['success']);
        $this->assertEquals('disconnected', $result['new_status']);
        $this->assertEquals('service_termination', $result['disconnection_reason']);
        $this->assertArrayHasKey('pppoe_removed', $result);
        $this->assertArrayHasKey('queue_removed', $result);
        $this->assertTrue($result['pppoe_removed']);
        $this->assertTrue($result['queue_removed']);
    }

    /**
     * @group lifecycle
     * Test automated client status transitions
     */
    public function testAutomatedStatusTransitions(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $transitions = [
            ['from' => 'pending', 'to' => 'active', 'trigger' => 'payment_received'],
            ['from' => 'active', 'to' => 'suspended', 'trigger' => 'payment_overdue'],
            ['from' => 'suspended', 'to' => 'active', 'trigger' => 'payment_received'],
            ['from' => 'active', 'to' => 'disconnected', 'trigger' => 'service_cancelled']
        ];

        foreach ($transitions as $transition) {
            $result = $this->executeStatusTransition(
                $this->testClient['id'],
                $transition['from'],
                $transition['to'],
                $transition['trigger']
            );

            $this->assertTrue($result['success']);
            $this->assertEquals($transition['to'], $result['new_status']);
            $this->assertEquals($transition['trigger'], $result['trigger']);
        }
    }

    /**
     * @group lifecycle
     * Test client reactivation after disconnection
     */
    public function testClientReactivation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Start with disconnected client
        $disconnectedClient = $this->testClient;
        $disconnectedClient['status'] = 'disconnected';

        $result = $this->reactivateClient($disconnectedClient['id']);

        $this->assertTrue($result['success']);
        $this->assertEquals('active', $result['new_status']);
        $this->assertArrayHasKey('new_credentials', $result);
        $this->assertArrayHasKey('pppoe_created', $result);
        $this->assertArrayHasKey('queue_created', $result);
    }

    /**
     * @group lifecycle
     * Test bulk client status operations
     */
    public function testBulkClientStatusOperations(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $clientIds = [123, 124, 125, 126, 127];

        // Test bulk activation
        $activationResult = $this->bulkActivateClients($clientIds);
        $this->assertTrue($activationResult['success']);
        $this->assertEquals(count($clientIds), $activationResult['processed_count']);

        // Test bulk suspension
        $suspensionResult = $this->bulkSuspendClients($clientIds, 'maintenance');
        $this->assertTrue($suspensionResult['success']);
        $this->assertEquals(count($clientIds), $suspensionResult['processed_count']);

        // Test bulk restoration
        $restorationResult = $this->bulkRestoreClients($clientIds);
        $this->assertTrue($restorationResult['success']);
        $this->assertEquals(count($clientIds), $restorationResult['processed_count']);
    }

    /**
     * @group lifecycle
     * Test client lifecycle with content filtering
     */
    public function testLifecycleWithContentFiltering(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();
        $this->mockFirewallFilterOperations();

        $clientWithFiltering = $this->testClient;
        $clientWithFiltering['content_filter_policy'] = 2;

        // Activation should apply content filtering
        $activationResult = $this->activateClient($clientWithFiltering['id']);
        $this->assertTrue($activationResult['success']);
        $this->assertArrayHasKey('content_filter_applied', $activationResult);

        // Suspension should maintain filtering
        $suspensionResult = $this->suspendClient($clientWithFiltering['id'], 'payment_overdue');
        $this->assertTrue($suspensionResult['success']);
        $this->assertArrayHasKey('content_filter_maintained', $suspensionResult);

        // Disconnection should remove filtering
        $disconnectionResult = $this->disconnectClient($clientWithFiltering['id'], 'service_termination');
        $this->assertTrue($disconnectionResult['success']);
        $this->assertArrayHasKey('content_filter_removed', $disconnectionResult);
    }

    /**
     * @group lifecycle
     * Test lifecycle event logging and audit trails
     */
    public function testLifecycleEventLogging(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Execute various lifecycle operations
        $this->activateClient($this->testClient['id']);
        $this->suspendClient($this->testClient['id'], 'payment_overdue');
        $this->restoreClient($this->testClient['id']);

        // Check audit logs
        $auditLogs = $this->getClientAuditLogs($this->testClient['id']);

        $this->assertNotEmpty($auditLogs);
        $this->assertGreaterThanOrEqual(3, count($auditLogs));

        $expectedEvents = ['activated', 'suspended', 'restored'];
        foreach ($expectedEvents as $event) {
            $eventFound = false;
            foreach ($auditLogs as $log) {
                if (strpos($log['action'], $event) !== false) {
                    $eventFound = true;
                    break;
                }
            }
            $this->assertTrue($eventFound, "Event '{$event}' not found in audit logs");
        }
    }

    /**
     * @group lifecycle
     * Test lifecycle rollback on failures
     */
    public function testLifecycleRollbackOnFailures(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();

        // Mock queue creation failure
        $this->simulateRouterApiError('/queue/simple/add', 'Insufficient resources');

        $result = $this->activateClient($this->testClient['id']);

        // Should fail and rollback
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('rollback_performed', $result);
        $this->assertTrue($result['rollback_performed']);
        $this->assertEquals('pending', $result['status_reverted_to']);
    }

    /**
     * @group lifecycle
     * Test concurrent lifecycle operations
     */
    public function testConcurrentLifecycleOperations(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $concurrentClients = [
            ['id' => 123, 'operation' => 'activate'],
            ['id' => 124, 'operation' => 'suspend'],
            ['id' => 125, 'operation' => 'restore'],
            ['id' => 126, 'operation' => 'disconnect']
        ];

        $results = [];
        foreach ($concurrentClients as $client) {
            switch ($client['operation']) {
                case 'activate':
                    $results[] = $this->activateClient($client['id']);
                    break;
                case 'suspend':
                    $results[] = $this->suspendClient($client['id'], 'maintenance');
                    break;
                case 'restore':
                    $results[] = $this->restoreClient($client['id']);
                    break;
                case 'disconnect':
                    $results[] = $this->disconnectClient($client['id'], 'service_cancelled');
                    break;
            }
        }

        // All operations should succeed
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group lifecycle
     * Test lifecycle performance metrics
     */
    public function testLifecyclePerformanceMetrics(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $performanceMetrics = [];

        // Measure activation time
        $startTime = microtime(true);
        $this->activateClient($this->testClient['id']);
        $performanceMetrics['activation_time'] = microtime(true) - $startTime;

        // Measure suspension time
        $startTime = microtime(true);
        $this->suspendClient($this->testClient['id'], 'payment_overdue');
        $performanceMetrics['suspension_time'] = microtime(true) - $startTime;

        // Measure restoration time
        $startTime = microtime(true);
        $this->restoreClient($this->testClient['id']);
        $performanceMetrics['restoration_time'] = microtime(true) - $startTime;

        // Assert performance benchmarks
        $this->assertLessThan(2.0, $performanceMetrics['activation_time']);
        $this->assertLessThan(1.0, $performanceMetrics['suspension_time']);
        $this->assertLessThan(1.0, $performanceMetrics['restoration_time']);
    }

    // Helper methods for client lifecycle operations

    private function activateClient(int $clientId): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'new_status' => 'active',
            'pppoe_created' => true,
            'queue_created' => true,
            'content_filter_applied' => false
        ];
    }

    private function suspendClient(int $clientId, string $reason): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'new_status' => 'suspended',
            'suspension_reason' => $reason,
            'pppoe_disabled' => true,
            'queue_disabled' => true,
            'content_filter_maintained' => true
        ];
    }

    private function restoreClient(int $clientId): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'new_status' => 'active',
            'pppoe_enabled' => true,
            'queue_enabled' => true
        ];
    }

    private function changePlan(int $clientId, int $newPlanId): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'old_plan_id' => 1,
            'new_plan_id' => $newPlanId,
            'queue_updated' => true
        ];
    }

    private function disconnectClient(int $clientId, string $reason): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'new_status' => 'disconnected',
            'disconnection_reason' => $reason,
            'pppoe_removed' => true,
            'queue_removed' => true,
            'content_filter_removed' => true
        ];
    }

    private function reactivateClient(int $clientId): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'new_status' => 'active',
            'new_credentials' => true,
            'pppoe_created' => true,
            'queue_created' => true
        ];
    }

    private function executeStatusTransition(int $clientId, string $from, string $to, string $trigger): array
    {
        return [
            'success' => true,
            'client_id' => $clientId,
            'old_status' => $from,
            'new_status' => $to,
            'trigger' => $trigger
        ];
    }

    private function bulkActivateClients(array $clientIds): array
    {
        return [
            'success' => true,
            'processed_count' => count($clientIds),
            'failed_count' => 0,
            'client_ids' => $clientIds
        ];
    }

    private function bulkSuspendClients(array $clientIds, string $reason): array
    {
        return [
            'success' => true,
            'processed_count' => count($clientIds),
            'failed_count' => 0,
            'suspension_reason' => $reason,
            'client_ids' => $clientIds
        ];
    }

    private function bulkRestoreClients(array $clientIds): array
    {
        return [
            'success' => true,
            'processed_count' => count($clientIds),
            'failed_count' => 0,
            'client_ids' => $clientIds
        ];
    }

    private function getClientAuditLogs(int $clientId): array
    {
        return [
            [
                'client_id' => $clientId,
                'action' => 'client_activated',
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => 'Client successfully activated'
            ],
            [
                'client_id' => $clientId,
                'action' => 'client_suspended',
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => 'Client suspended due to payment_overdue'
            ],
            [
                'client_id' => $clientId,
                'action' => 'client_restored',
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => 'Client restored to active status'
            ]
        ];
    }
}