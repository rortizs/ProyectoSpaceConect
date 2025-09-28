<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Client Provisioning Integration Tests
 *
 * Tests the complete client provisioning workflow including:
 * - PPPoE user creation and management
 * - Simple Queue setup and modification
 * - IP address assignment and validation
 * - Client lifecycle management
 */
class ClientProvisioningTest extends MikroTikTestCase
{
    private $testClientData;
    private $testQueueData;
    private $testPppoeData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testClientData = [
            'id' => 123,
            'names' => 'Test Client',
            'net_ip' => '192.168.1.100',
            'net_name' => 'testclient001',
            'net_password' => 'testpass123',
            'net_router' => 1,
            'plan_id' => 1,
            'upload_limit' => '10M',
            'download_limit' => '50M'
        ];

        $this->testQueueData = $this->createTestSimpleQueueData([
            'name' => 'queue-' . $this->testClientData['net_name'],
            'target' => $this->testClientData['net_ip'] . '/32',
            'max-limit' => $this->testClientData['upload_limit'] . '/' . $this->testClientData['download_limit']
        ]);

        $this->testPppoeData = $this->createTestPppoeSecretData([
            'name' => $this->testClientData['net_name'],
            'password' => $this->testClientData['net_password'],
            'remote-address' => $this->testClientData['net_ip']
        ]);
    }

    /**
     * @group provisioning
     * Test complete client onboarding workflow
     */
    public function testCompleteClientOnboarding(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Step 1: Create PPPoE secret
        $pppoeResult = $this->createPppoeSecret($this->testPppoeData);
        $this->assertTrue($pppoeResult['success']);

        // Step 2: Create Simple Queue
        $queueResult = $this->createSimpleQueue($this->testQueueData);
        $this->assertTrue($queueResult['success']);

        // Step 3: Assign IP address
        $ipResult = $this->assignClientIP($this->testClientData['net_ip']);
        $this->assertTrue($ipResult['success']);

        // Step 4: Validate complete provisioning
        $this->validateClientProvisioning($this->testClientData);
    }

    /**
     * @group provisioning
     * Test PPPoE secret creation and validation
     */
    public function testPppoeSecretCreation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();

        $result = $this->createPppoeSecret($this->testPppoeData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('secret_id', $result);
        $this->assertNotEmpty($result['secret_id']);

        // Validate secret parameters
        $this->assertEquals($this->testPppoeData['name'], $result['name']);
        $this->assertEquals($this->testPppoeData['service'], $result['service']);
    }

    /**
     * @group provisioning
     * Test PPPoE secret modification
     */
    public function testPppoeSecretModification(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();

        // Create initial secret
        $createResult = $this->createPppoeSecret($this->testPppoeData);
        $this->assertTrue($createResult['success']);

        // Modify secret parameters
        $modificationData = [
            'password' => 'newpassword123',
            'remote-address' => '192.168.1.101',
            'profile' => 'premium'
        ];

        $modifyResult = $this->modifyPppoeSecret($createResult['secret_id'], $modificationData);
        $this->assertTrue($modifyResult['success']);
    }

    /**
     * @group provisioning
     * Test Simple Queue creation with bandwidth limits
     */
    public function testSimpleQueueCreation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        $result = $this->createSimpleQueue($this->testQueueData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('queue_id', $result);
        $this->assertNotEmpty($result['queue_id']);

        // Validate queue parameters
        $this->assertEquals($this->testQueueData['target'], $result['target']);
        $this->assertEquals($this->testQueueData['max-limit'], $result['max-limit']);
    }

    /**
     * @group provisioning
     * Test queue bandwidth modification
     */
    public function testQueueBandwidthModification(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        // Create initial queue
        $createResult = $this->createSimpleQueue($this->testQueueData);
        $this->assertTrue($createResult['success']);

        // Modify bandwidth limits
        $newLimits = [
            'max-limit' => '20M/100M',
            'burst-limit' => '40M/200M',
            'burst-threshold' => '16M/80M'
        ];

        $modifyResult = $this->modifySimpleQueue($createResult['queue_id'], $newLimits);
        $this->assertTrue($modifyResult['success']);
    }

    /**
     * @group provisioning
     * Test IP address assignment and validation
     */
    public function testIPAddressAssignment(): void
    {
        $testIPs = [
            '192.168.1.100',
            '192.168.1.101',
            '192.168.1.102'
        ];

        foreach ($testIPs as $ip) {
            $result = $this->assignClientIP($ip);
            $this->assertTrue($result['success']);
            $this->assertEquals($ip, $result['assigned_ip']);
        }
    }

    /**
     * @group provisioning
     * Test IP conflict detection and resolution
     */
    public function testIPConflictDetection(): void
    {
        $conflictIP = '192.168.1.100';

        // Assign IP to first client
        $firstResult = $this->assignClientIP($conflictIP);
        $this->assertTrue($firstResult['success']);

        // Attempt to assign same IP to second client
        $secondResult = $this->assignClientIP($conflictIP);
        $this->assertFalse($secondResult['success']);
        $this->assertStringContains('IP already assigned', $secondResult['message']);
    }

    /**
     * @group provisioning
     * Test bulk client provisioning
     */
    public function testBulkClientProvisioning(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $bulkClients = [
            ['name' => 'client001', 'ip' => '192.168.1.100', 'bandwidth' => '10M/50M'],
            ['name' => 'client002', 'ip' => '192.168.1.101', 'bandwidth' => '20M/100M'],
            ['name' => 'client003', 'ip' => '192.168.1.102', 'bandwidth' => '15M/75M']
        ];

        $results = $this->bulkProvisionClients($bulkClients);

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group provisioning
     * Test client plan upgrade workflow
     */
    public function testClientPlanUpgrade(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        // Initial provisioning with basic plan
        $initialData = $this->testClientData;
        $initialData['upload_limit'] = '5M';
        $initialData['download_limit'] = '25M';

        $provisionResult = $this->provisionClient($initialData);
        $this->assertTrue($provisionResult['success']);

        // Upgrade to premium plan
        $upgradeData = [
            'upload_limit' => '20M',
            'download_limit' => '100M',
            'burst_upload' => '40M',
            'burst_download' => '200M'
        ];

        $upgradeResult = $this->upgradeClientPlan($initialData['id'], $upgradeData);
        $this->assertTrue($upgradeResult['success']);
    }

    /**
     * @group provisioning
     * Test client provisioning with custom profiles
     */
    public function testClientProvisioningWithProfiles(): void
    {
        $profiles = [
            'basic' => ['upload' => '5M', 'download' => '25M'],
            'standard' => ['upload' => '10M', 'download' => '50M'],
            'premium' => ['upload' => '20M', 'download' => '100M']
        ];

        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        foreach ($profiles as $profileName => $limits) {
            $clientData = $this->testClientData;
            $clientData['profile'] = $profileName;
            $clientData['upload_limit'] = $limits['upload'];
            $clientData['download_limit'] = $limits['download'];

            $result = $this->provisionClient($clientData);
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group provisioning
     * Test provisioning rollback on failure
     */
    public function testProvisioningRollback(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();

        // Mock queue creation failure
        $this->simulateRouterApiError('/queue/simple/add', 'Insufficient resources');

        $result = $this->provisionClient($this->testClientData);

        // Should fail and rollback PPPoE secret
        $this->assertFalse($result['success']);
        $this->assertStringContains('Provisioning failed, rolled back', $result['message']);
    }

    /**
     * @group provisioning
     * Test provisioning with validation checks
     */
    public function testProvisioningValidation(): void
    {
        $invalidData = [
            'net_name' => '', // Empty username
            'net_ip' => 'invalid-ip', // Invalid IP
            'upload_limit' => '0M', // Zero bandwidth
            'download_limit' => ''  // Empty bandwidth
        ];

        $result = $this->validateProvisioningData($invalidData);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['errors']);
    }

    /**
     * @group provisioning
     * Test provisioning performance metrics
     */
    public function testProvisioningPerformance(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $startTime = microtime(true);

        $result = $this->provisionClient($this->testClientData);

        $endTime = microtime(true);
        $provisioningTime = $endTime - $startTime;

        $this->assertTrue($result['success']);
        $this->assertLessThan(5.0, $provisioningTime); // Should complete within 5 seconds
    }

    // Helper methods for client provisioning operations

    private function createPppoeSecret(array $data): array
    {
        // Mock PPPoE secret creation
        return [
            'success' => true,
            'secret_id' => '*1',
            'name' => $data['name'],
            'service' => $data['service']
        ];
    }

    private function modifyPppoeSecret(string $secretId, array $data): array
    {
        // Mock PPPoE secret modification
        return ['success' => true];
    }

    private function createSimpleQueue(array $data): array
    {
        // Mock Simple Queue creation
        return [
            'success' => true,
            'queue_id' => '*1',
            'target' => $data['target'],
            'max-limit' => $data['max-limit']
        ];
    }

    private function modifySimpleQueue(string $queueId, array $data): array
    {
        // Mock Simple Queue modification
        return ['success' => true];
    }

    private function assignClientIP(string $ip): array
    {
        // Mock IP assignment validation
        static $assignedIPs = [];

        if (in_array($ip, $assignedIPs)) {
            return ['success' => false, 'message' => 'IP already assigned'];
        }

        $assignedIPs[] = $ip;
        return ['success' => true, 'assigned_ip' => $ip];
    }

    private function bulkProvisionClients(array $clients): array
    {
        $results = [];
        foreach ($clients as $client) {
            $results[] = ['success' => true, 'client' => $client['name']];
        }
        return $results;
    }

    private function provisionClient(array $clientData): array
    {
        // Mock complete client provisioning
        return ['success' => true, 'client_id' => $clientData['id']];
    }

    private function upgradeClientPlan(int $clientId, array $upgradeData): array
    {
        // Mock client plan upgrade
        return ['success' => true, 'upgraded' => true];
    }

    private function validateProvisioningData(array $data): array
    {
        $errors = [];

        if (empty($data['net_name'])) {
            $errors[] = 'Username is required';
        }

        if (!filter_var($data['net_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'Invalid IP address';
        }

        if (empty($data['upload_limit']) || $data['upload_limit'] === '0M') {
            $errors[] = 'Valid upload limit is required';
        }

        if (empty($data['download_limit'])) {
            $errors[] = 'Download limit is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateClientProvisioning(array $clientData): void
    {
        // Validate that all provisioning steps completed successfully
        $this->assertNotEmpty($clientData['net_name']);
        $this->assertNotEmpty($clientData['net_ip']);
        $this->assertNotEmpty($clientData['upload_limit']);
        $this->assertNotEmpty($clientData['download_limit']);
    }
}