# API Testing Guide - ISP Management System

This comprehensive guide covers testing strategies for external API integrations, internal API endpoints, and service-to-service communication in the ISP Management System. It includes best practices for mocking, contract testing, and integration validation.

## 📋 Table of Contents

1. [API Testing Overview](#api-testing-overview)
2. [Internal API Testing](#internal-api-testing)
3. [External API Integration Testing](#external-api-integration-testing)
4. [MikroTik API Testing](#mikrotik-api-testing)
5. [Payment Gateway Testing](#payment-gateway-testing)
6. [WhatsApp API Testing](#whatsapp-api-testing)
7. [Email Service Testing](#email-service-testing)
8. [Contract Testing](#contract-testing)
9. [API Mocking Strategies](#api-mocking-strategies)
10. [Performance Testing APIs](#performance-testing-apis)
11. [Security Testing](#security-testing)
12. [Documentation and Tools](#documentation-and-tools)

## 🎯 API Testing Overview

### Testing Pyramid for APIs

```
        /\
       /  \     E2E API Tests (5%)
      /____\    - Full integration flows
     /      \   - Real external services
    /        \  - Production-like scenarios
   /Contract \
  /  Tests   \ Contract Tests (15%)
 /   (15%)    \ - API schema validation
/______________\ - Request/response contracts
\              /
 \ Integration/ Integration Tests (30%)
  \  Tests   /  - Service-to-service
   \ (30%)  /   - Mocked external APIs
    \______/    - Database integration
     \    /
      \  /     Unit Tests (50%)
       \/      - Individual API methods
               - Business logic validation
               - Fast execution
```

### API Testing Types

| Test Type | Purpose | Scope | Speed | Dependencies |
|-----------|---------|-------|-------|--------------|
| **Unit** | Individual API methods | Single function | Very Fast | None |
| **Integration** | Service interactions | Multiple components | Fast | Mocked externals |
| **Contract** | API schema compliance | Request/Response | Fast | Schema definitions |
| **End-to-End** | Complete workflows | Full system | Slow | Real services |
| **Performance** | Load and response time | API endpoints | Variable | Load generators |
| **Security** | Authentication & authorization | Security layer | Fast | Auth systems |

## 🔌 Internal API Testing

### REST API Endpoint Testing

Create comprehensive tests for internal API endpoints:

```php
<?php

class APIEndpointTest extends BaseTestCase
{
    use DatabaseTransactions, MocksExternalServices;

    private string $baseUrl = 'http://localhost/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupAPIEnvironment();
    }

    /**
     * @group api
     * @group functional
     */
    public function testGetClientsEndpoint(): void
    {
        // Arrange
        $this->createTestClients(5);
        $this->authenticateAPI();

        // Act
        $response = $this->makeAPIRequest('GET', '/clients');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertCount(5, $response['body']['data']);
        $this->validateClientSchema($response['body']['data'][0]);
    }

    /**
     * @group api
     * @group validation
     */
    public function testCreateClientWithValidData(): void
    {
        // Arrange
        $clientData = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '555-0123',
            'plan_id' => 1
        ];

        $this->authenticateAPI();

        // Act
        $response = $this->makeAPIRequest('POST', '/clients', $clientData);

        // Assert
        $this->assertEquals(201, $response['status_code']);
        $this->assertArrayHasKey('id', $response['body']['data']);
        $this->assertEquals($clientData['email'], $response['body']['data']['email']);

        // Verify in database
        $this->assertDatabaseHas('clients', [
            'email' => $clientData['email'],
            'name' => $clientData['name']
        ]);
    }

    /**
     * @group api
     * @group validation
     * @group error-handling
     */
    public function testCreateClientWithInvalidData(): void
    {
        // Arrange
        $invalidData = [
            'name' => '', // Invalid: empty name
            'email' => 'invalid-email', // Invalid: malformed email
            'phone' => '123' // Invalid: too short
        ];

        $this->authenticateAPI();

        // Act
        $response = $this->makeAPIRequest('POST', '/clients', $invalidData);

        // Assert
        $this->assertEquals(422, $response['status_code']);
        $this->assertArrayHasKey('errors', $response['body']);
        $this->assertArrayHasKey('name', $response['body']['errors']);
        $this->assertArrayHasKey('email', $response['body']['errors']);
        $this->assertArrayHasKey('phone', $response['body']['errors']);
    }

    /**
     * @group api
     * @group authentication
     */
    public function testUnauthorizedAccess(): void
    {
        // Act - No authentication
        $response = $this->makeAPIRequest('GET', '/clients');

        // Assert
        $this->assertEquals(401, $response['status_code']);
        $this->assertArrayHasKey('error', $response['body']);
        $this->assertEquals('Unauthorized', $response['body']['error']);
    }

    /**
     * @group api
     * @group pagination
     */
    public function testClientsPagination(): void
    {
        // Arrange
        $this->createTestClients(25);
        $this->authenticateAPI();

        // Act - First page
        $response = $this->makeAPIRequest('GET', '/clients?page=1&limit=10');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertCount(10, $response['body']['data']);
        $this->assertEquals(1, $response['body']['pagination']['current_page']);
        $this->assertEquals(3, $response['body']['pagination']['total_pages']);
        $this->assertEquals(25, $response['body']['pagination']['total_items']);

        // Act - Second page
        $response = $this->makeAPIRequest('GET', '/clients?page=2&limit=10');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertCount(10, $response['body']['data']);
        $this->assertEquals(2, $response['body']['pagination']['current_page']);
    }

    /**
     * @group api
     * @group filtering
     */
    public function testClientsFiltering(): void
    {
        // Arrange
        $activeClient = $this->createTestClient(['status' => 'active']);
        $suspendedClient = $this->createTestClient(['status' => 'suspended']);
        $this->authenticateAPI();

        // Act - Filter by status
        $response = $this->makeAPIRequest('GET', '/clients?status=active');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertCount(1, $response['body']['data']);
        $this->assertEquals('active', $response['body']['data'][0]['status']);
        $this->assertEquals($activeClient['id'], $response['body']['data'][0]['id']);
    }

    /**
     * @group api
     * @group sorting
     */
    public function testClientsSorting(): void
    {
        // Arrange
        $client1 = $this->createTestClient(['name' => 'Alpha Client']);
        $client2 = $this->createTestClient(['name' => 'Beta Client']);
        $client3 = $this->createTestClient(['name' => 'Charlie Client']);
        $this->authenticateAPI();

        // Act - Sort by name ascending
        $response = $this->makeAPIRequest('GET', '/clients?sort=name&order=asc');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertEquals('Alpha Client', $response['body']['data'][0]['name']);
        $this->assertEquals('Beta Client', $response['body']['data'][1]['name']);
        $this->assertEquals('Charlie Client', $response['body']['data'][2]['name']);

        // Act - Sort by name descending
        $response = $this->makeAPIRequest('GET', '/clients?sort=name&order=desc');

        // Assert
        $this->assertEquals('Charlie Client', $response['body']['data'][0]['name']);
        $this->assertEquals('Beta Client', $response['body']['data'][1]['name']);
        $this->assertEquals('Alpha Client', $response['body']['data'][2]['name']);
    }

    private function makeAPIRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = $this->getAPIHeaders();

        // Simulate HTTP request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status_code' => $statusCode,
            'body' => json_decode($response, true)
        ];
    }

    private function authenticateAPI(): void
    {
        // Get authentication token
        $response = $this->makeAPIRequest('POST', '/auth/login', [
            'username' => 'test_user',
            'password' => 'test_password'
        ]);

        $this->authToken = $response['body']['token'];
    }

    private function getAPIHeaders(): array
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if (isset($this->authToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }

        return $headers;
    }

    private function validateClientSchema(array $client): void
    {
        $requiredFields = ['id', 'name', 'email', 'status', 'created_at'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $client, "Missing required field: {$field}");
        }

        $this->assertIsInt($client['id']);
        $this->assertIsString($client['name']);
        $this->assertMatchesRegularExpression('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $client['email']);
        $this->assertContains($client['status'], ['active', 'suspended', 'cancelled']);
    }
}
```

### JSON API Response Testing

```php
class JSONAPIResponseTest extends BaseTestCase
{
    /**
     * @group api
     * @group json-api
     */
    public function testJSONAPICompliance(): void
    {
        $this->authenticateAPI();
        $response = $this->makeAPIRequest('GET', '/clients/1');

        // Test JSON:API structure
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('type', $response['body']['data']);
        $this->assertArrayHasKey('id', $response['body']['data']);
        $this->assertArrayHasKey('attributes', $response['body']['data']);

        // Test content type
        $this->assertEquals('application/vnd.api+json', $response['headers']['content-type']);
    }

    /**
     * @group api
     * @group json-api
     * @group relationships
     */
    public function testJSONAPIRelationships(): void
    {
        // Create client with contract
        $client = $this->createTestClient();
        $contract = $this->createTestContract($client['id']);

        $this->authenticateAPI();
        $response = $this->makeAPIRequest('GET', '/clients/' . $client['id'] . '?include=contracts');

        // Test relationships structure
        $this->assertArrayHasKey('relationships', $response['body']['data']);
        $this->assertArrayHasKey('contracts', $response['body']['data']['relationships']);
        $this->assertArrayHasKey('included', $response['body']);

        // Validate included resources
        $includedContract = collect($response['body']['included'])
            ->where('type', 'contracts')
            ->where('id', $contract['id'])
            ->first();

        $this->assertNotNull($includedContract);
        $this->assertEquals('contracts', $includedContract['type']);
    }
}
```

## 🔗 External API Integration Testing

### Payment Gateway Integration

```php
class PaymentGatewayIntegrationTest extends BaseTestCase
{
    use MocksExternalServices;

    /**
     * @group integration
     * @group payment
     * @group external-api
     */
    public function testSuccessfulPaymentProcessing(): void
    {
        // Arrange
        $this->mockPaymentGatewaySuccess();
        $client = $this->createTestClient();
        $bill = $this->createTestBill($client['id'], 100.00);

        // Act
        $paymentService = new PaymentGatewayService();
        $result = $paymentService->processPayment([
            'amount' => 100.00,
            'currency' => 'USD',
            'card_token' => 'test_token_123',
            'bill_id' => $bill['id']
        ]);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['transaction_id']);
        $this->assertEquals('completed', $result['status']);

        // Verify external API was called correctly
        $this->assertPaymentGatewayWasCalled([
            'amount' => 10000, // Cents
            'currency' => 'USD',
            'source' => 'test_token_123'
        ]);
    }

    /**
     * @group integration
     * @group payment
     * @group error-handling
     */
    public function testPaymentGatewayError(): void
    {
        // Arrange
        $this->mockPaymentGatewayError('Your card was declined.');
        $client = $this->createTestClient();
        $bill = $this->createTestBill($client['id'], 100.00);

        // Act
        $paymentService = new PaymentGatewayService();
        $result = $paymentService->processPayment([
            'amount' => 100.00,
            'currency' => 'USD',
            'card_token' => 'test_token_declined',
            'bill_id' => $bill['id']
        ]);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Your card was declined.', $result['error_message']);
        $this->assertEquals('declined', $result['status']);

        // Verify bill remains unpaid
        $this->assertDatabaseHas('bills', [
            'id' => $bill['id'],
            'status' => 'pending'
        ]);
    }

    /**
     * @group integration
     * @group payment
     * @group timeout
     */
    public function testPaymentGatewayTimeout(): void
    {
        // Arrange
        $this->mockPaymentGatewayTimeout();
        $client = $this->createTestClient();
        $bill = $this->createTestBill($client['id'], 100.00);

        // Act
        $paymentService = new PaymentGatewayService();
        $result = $paymentService->processPayment([
            'amount' => 100.00,
            'currency' => 'USD',
            'card_token' => 'test_token_timeout',
            'bill_id' => $bill['id']
        ]);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('timeout', strtolower($result['error_message']));
        $this->assertEquals('timeout', $result['status']);
    }

    /**
     * @group integration
     * @group payment
     * @group webhook
     */
    public function testPaymentWebhookProcessing(): void
    {
        // Arrange
        $client = $this->createTestClient();
        $bill = $this->createTestBill($client['id'], 100.00);

        $webhookPayload = [
            'event_type' => 'payment.completed',
            'transaction_id' => 'txn_123456789',
            'amount' => 10000, // Cents
            'currency' => 'USD',
            'metadata' => [
                'bill_id' => $bill['id']
            ],
            'status' => 'succeeded'
        ];

        // Act
        $webhookHandler = new PaymentWebhookHandler();
        $result = $webhookHandler->processWebhook($webhookPayload);

        // Assert
        $this->assertTrue($result);

        // Verify bill was marked as paid
        $this->assertDatabaseHas('bills', [
            'id' => $bill['id'],
            'status' => 'paid'
        ]);

        // Verify payment record was created
        $this->assertDatabaseHas('payments', [
            'bill_id' => $bill['id'],
            'transaction_id' => 'txn_123456789',
            'amount' => 100.00,
            'status' => 'completed'
        ]);
    }

    private function mockPaymentGatewaySuccess(): void
    {
        $this->mockExternalAPI('payment_gateway', [
            'endpoint' => '/charges',
            'method' => 'POST',
            'response' => [
                'id' => 'ch_123456789',
                'amount' => 10000,
                'currency' => 'usd',
                'status' => 'succeeded',
                'paid' => true
            ],
            'status_code' => 200
        ]);
    }

    private function mockPaymentGatewayError(string $errorMessage): void
    {
        $this->mockExternalAPI('payment_gateway', [
            'endpoint' => '/charges',
            'method' => 'POST',
            'response' => [
                'error' => [
                    'type' => 'card_error',
                    'code' => 'card_declined',
                    'message' => $errorMessage
                ]
            ],
            'status_code' => 402
        ]);
    }

    private function mockPaymentGatewayTimeout(): void
    {
        $this->mockExternalAPI('payment_gateway', [
            'endpoint' => '/charges',
            'method' => 'POST',
            'timeout' => true
        ]);
    }
}
```

### Third-Party Service Testing

```php
class ThirdPartyServiceTest extends BaseTestCase
{
    use MocksExternalServices;

    /**
     * @group integration
     * @group external-api
     * @group whatsapp
     */
    public function testWhatsAppMessageSending(): void
    {
        // Arrange
        $this->mockWhatsAppAPI();
        $client = $this->createTestClient(['phone' => '+1234567890']);

        // Act
        $whatsAppService = new WhatsAppService();
        $result = $whatsAppService->sendMessage([
            'to' => $client['phone'],
            'message' => 'Your bill is ready for payment.',
            'template' => 'bill_notification'
        ]);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['message_id']);

        // Verify API was called with correct parameters
        $this->assertExternalAPIWasCalled('whatsapp', [
            'to' => '+1234567890',
            'body' => 'Your bill is ready for payment.',
            'template' => 'bill_notification'
        ]);
    }

    /**
     * @group integration
     * @group external-api
     * @group email
     */
    public function testEmailServiceIntegration(): void
    {
        // Arrange
        $this->mockEmailServiceAPI();
        $client = $this->createTestClient(['email' => 'test@example.com']);
        $bill = $this->createTestBill($client['id']);

        // Act
        $emailService = new EmailService();
        $result = $emailService->sendBillNotification($client, $bill);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['message_id']);

        // Verify email content
        $this->assertExternalAPIWasCalled('email_service', [
            'to' => 'test@example.com',
            'subject' => 'Your Internet Service Bill',
            'template' => 'bill_notification'
        ]);
    }

    private function mockWhatsAppAPI(): void
    {
        $this->mockExternalAPI('whatsapp', [
            'endpoint' => '/messages',
            'method' => 'POST',
            'response' => [
                'messaging_product' => 'whatsapp',
                'contacts' => [
                    [
                        'input' => '+1234567890',
                        'wa_id' => '1234567890'
                    ]
                ],
                'messages' => [
                    [
                        'id' => 'wamid.123456789'
                    ]
                ]
            ],
            'status_code' => 200
        ]);
    }

    private function mockEmailServiceAPI(): void
    {
        $this->mockExternalAPI('email_service', [
            'endpoint' => '/send',
            'method' => 'POST',
            'response' => [
                'id' => 'email_123456789',
                'status' => 'queued'
            ],
            'status_code' => 202
        ]);
    }
}
```

## 🔌 MikroTik API Testing

### Router API Integration

```php
class MikroTikAPITest extends MikroTikTestCase
{
    /**
     * @group integration
     * @group mikrotik
     * @group connection
     */
    public function testRouterConnection(): void
    {
        // Arrange
        $router = $this->getTestRouter();

        // Act
        $connected = $router->connect();

        // Assert
        $this->assertTrue($connected, 'Failed to connect to test router');
        $this->assertTrue($router->isConnected());
    }

    /**
     * @group integration
     * @group mikrotik
     * @group pppoe
     */
    public function testPPPoEUserManagement(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();
        $client = $this->createTestClient();

        $pppoeData = [
            'name' => $client['username'],
            'password' => $client['password'],
            'profile' => 'default',
            'local-address' => '10.0.0.1',
            'remote-address' => '10.0.0.100'
        ];

        // Act - Add PPPoE user
        $addResult = $router->addPppoeUser($pppoeData);

        // Assert
        $this->assertTrue($addResult);

        // Verify user exists
        $users = $router->getPppoeUsers();
        $createdUser = collect($users)->firstWhere('name', $client['username']);
        $this->assertNotNull($createdUser);
        $this->assertEquals($client['username'], $createdUser['name']);

        // Act - Remove PPPoE user
        $removeResult = $router->removePppoeUser($client['username']);

        // Assert
        $this->assertTrue($removeResult);

        // Verify user is removed
        $users = $router->getPppoeUsers();
        $removedUser = collect($users)->firstWhere('name', $client['username']);
        $this->assertNull($removedUser);
    }

    /**
     * @group integration
     * @group mikrotik
     * @group queue
     */
    public function testSimpleQueueManagement(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();
        $client = $this->createTestClient();

        $queueData = [
            'name' => 'queue_' . $client['username'],
            'target' => '192.168.1.100/32',
            'max-limit' => '10M/10M',
            'burst-limit' => '15M/15M',
            'burst-threshold' => '8M/8M',
            'burst-time' => '8s/8s'
        ];

        // Act - Add queue
        $addResult = $router->addSimpleQueue($queueData);

        // Assert
        $this->assertTrue($addResult);

        // Verify queue exists
        $queues = $router->getSimpleQueues();
        $createdQueue = collect($queues)->firstWhere('name', $queueData['name']);
        $this->assertNotNull($createdQueue);
        $this->assertEquals($queueData['target'], $createdQueue['target']);
        $this->assertEquals($queueData['max-limit'], $createdQueue['max-limit']);

        // Act - Update queue
        $updateData = ['max-limit' => '20M/20M'];
        $updateResult = $router->updateSimpleQueue($queueData['name'], $updateData);

        // Assert
        $this->assertTrue($updateResult);

        // Verify queue is updated
        $queues = $router->getSimpleQueues();
        $updatedQueue = collect($queues)->firstWhere('name', $queueData['name']);
        $this->assertEquals('20M/20M', $updatedQueue['max-limit']);

        // Cleanup
        $router->removeSimpleQueue($queueData['name']);
    }

    /**
     * @group integration
     * @group mikrotik
     * @group firewall
     */
    public function testFirewallRuleManagement(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();

        $firewallRule = [
            'chain' => 'forward',
            'src-address' => '192.168.1.100',
            'action' => 'drop',
            'comment' => 'Test rule for client suspension'
        ];

        // Act - Add firewall rule
        $addResult = $router->addFirewallRule($firewallRule);

        // Assert
        $this->assertTrue($addResult);

        // Verify rule exists
        $rules = $router->getFirewallRules();
        $createdRule = collect($rules)->firstWhere('comment', $firewallRule['comment']);
        $this->assertNotNull($createdRule);
        $this->assertEquals($firewallRule['src-address'], $createdRule['src-address']);
        $this->assertEquals($firewallRule['action'], $createdRule['action']);

        // Cleanup
        $router->removeFirewallRule($createdRule['id']);
    }

    /**
     * @group integration
     * @group mikrotik
     * @group error-handling
     */
    public function testRouterErrorHandling(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();

        // Act - Try to add invalid PPPoE user
        $invalidData = [
            'name' => '', // Invalid: empty name
            'password' => 'test',
            'profile' => 'nonexistent_profile' // Invalid: profile doesn't exist
        ];

        $result = $router->addPppoeUser($invalidData);

        // Assert
        $this->assertFalse($result);
        $this->assertNotEmpty($router->getLastError());
    }

    /**
     * @group integration
     * @group mikrotik
     * @group performance
     */
    public function testRouterAPIPerformance(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();
        $startTime = microtime(true);

        // Act - Perform multiple operations
        for ($i = 0; $i < 10; $i++) {
            $router->getPppoeUsers();
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Assert - Should complete within reasonable time
        $this->assertLessThan(5.0, $executionTime, 'Router API calls taking too long');
    }

    private function getConnectedRouter(): Router
    {
        $router = $this->getTestRouter();
        $connected = $router->connect();

        if (!$connected) {
            $this->markTestSkipped('Cannot connect to test router');
        }

        return $router;
    }
}
```

### Router Configuration Testing

```php
class RouterConfigurationTest extends MikroTikTestCase
{
    /**
     * @group integration
     * @group mikrotik
     * @group configuration
     */
    public function testRouterConfigurationBackup(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();

        // Act
        $backupResult = $router->createBackup('test_backup');

        // Assert
        $this->assertTrue($backupResult);

        // Verify backup exists
        $backups = $router->getBackups();
        $this->assertContains('test_backup', $backups);

        // Cleanup
        $router->removeBackup('test_backup');
    }

    /**
     * @group integration
     * @group mikrotik
     * @group monitoring
     */
    public function testRouterResourceMonitoring(): void
    {
        // Arrange
        $router = $this->getConnectedRouter();

        // Act
        $resources = $router->getSystemResources();

        // Assert
        $this->assertIsArray($resources);
        $this->assertArrayHasKey('cpu-load', $resources);
        $this->assertArrayHasKey('free-memory', $resources);
        $this->assertArrayHasKey('total-memory', $resources);

        // Verify values are reasonable
        $this->assertGreaterThanOrEqual(0, $resources['cpu-load']);
        $this->assertLessThanOrEqual(100, $resources['cpu-load']);
        $this->assertGreaterThan(0, $resources['free-memory']);
        $this->assertGreaterThan(0, $resources['total-memory']);
    }
}
```

## 🎭 API Mocking Strategies

### Mock External Services Trait

```php
trait MocksExternalServices
{
    private array $mockedAPIs = [];
    private array $apiCallLog = [];

    protected function mockExternalAPI(string $service, array $config): void
    {
        $this->mockedAPIs[$service] = $config;
    }

    protected function assertExternalAPIWasCalled(string $service, array $expectedData = []): void
    {
        $calls = $this->apiCallLog[$service] ?? [];
        $this->assertNotEmpty($calls, "API {$service} was not called");

        if (!empty($expectedData)) {
            $lastCall = end($calls);
            foreach ($expectedData as $key => $value) {
                $this->assertEquals($value, $lastCall['data'][$key]);
            }
        }
    }

    protected function assertExternalAPICallCount(string $service, int $expectedCount): void
    {
        $calls = $this->apiCallLog[$service] ?? [];
        $this->assertCount($expectedCount, $calls);
    }

    protected function getExternalAPICalls(string $service): array
    {
        return $this->apiCallLog[$service] ?? [];
    }

    protected function clearAPICallLog(): void
    {
        $this->apiCallLog = [];
    }

    // Mock HTTP client responses
    protected function mockHttpClient(): void
    {
        $mock = $this->createMock(HttpClient::class);

        $mock->method('request')
             ->willReturnCallback(function($method, $url, $options) {
                return $this->handleMockedRequest($method, $url, $options);
             });

        // Replace real HTTP client with mock
        app()->instance(HttpClient::class, $mock);
    }

    private function handleMockedRequest(string $method, string $url, array $options): MockResponse
    {
        // Log the API call
        $service = $this->identifyService($url);
        $this->apiCallLog[$service][] = [
            'method' => $method,
            'url' => $url,
            'data' => $options['json'] ?? [],
            'timestamp' => time()
        ];

        // Return mocked response
        $config = $this->mockedAPIs[$service] ?? null;

        if ($config && isset($config['timeout']) && $config['timeout']) {
            throw new RequestTimeoutException('Request timeout');
        }

        return new MockResponse(
            $config['response'] ?? [],
            $config['status_code'] ?? 200,
            $config['headers'] ?? []
        );
    }

    private function identifyService(string $url): string
    {
        // Map URLs to service names
        $serviceMap = [
            'api.stripe.com' => 'payment_gateway',
            'graph.facebook.com' => 'whatsapp',
            'api.sendgrid.com' => 'email_service',
            'api.mailgun.net' => 'email_service'
        ];

        foreach ($serviceMap as $domain => $service) {
            if (strpos($url, $domain) !== false) {
                return $service;
            }
        }

        return 'unknown';
    }
}

class MockResponse
{
    private array $data;
    private int $statusCode;
    private array $headers;

    public function __construct(array $data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return json_encode($this->data);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
```

### Wiremock Integration

```php
class WiremockTestCase extends BaseTestCase
{
    private string $wiremockUrl = 'http://localhost:8080';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupWiremock();
    }

    protected function tearDown(): void
    {
        $this->resetWiremock();
        parent::tearDown();
    }

    protected function stubAPI(string $endpoint, array $response, int $statusCode = 200): void
    {
        $stub = [
            'request' => [
                'method' => 'POST',
                'url' => $endpoint
            ],
            'response' => [
                'status' => $statusCode,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($response)
            ]
        ];

        $this->makeWiremockRequest('/__admin/mappings', 'POST', $stub);
    }

    protected function verifyAPICall(string $endpoint, array $expectedBody = []): void
    {
        $verifyRequest = [
            'method' => 'POST',
            'url' => $endpoint
        ];

        if (!empty($expectedBody)) {
            $verifyRequest['bodyPatterns'] = [
                ['equalToJson' => json_encode($expectedBody)]
            ];
        }

        $response = $this->makeWiremockRequest('/__admin/requests/count', 'POST', $verifyRequest);
        $count = json_decode($response, true)['count'];

        $this->assertGreaterThan(0, $count, "Expected API call to {$endpoint} was not made");
    }

    private function setupWiremock(): void
    {
        // Reset all stubs
        $this->resetWiremock();
    }

    private function resetWiremock(): void
    {
        $this->makeWiremockRequest('/__admin/reset', 'POST');
    }

    private function makeWiremockRequest(string $endpoint, string $method, array $data = []): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->wiremockUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
```

## 📊 Contract Testing

### API Schema Validation

```php
class APIContractTest extends BaseTestCase
{
    private SchemaValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SchemaValidator();
    }

    /**
     * @group contract
     * @group api-schema
     */
    public function testClientAPISchema(): void
    {
        // Arrange
        $client = $this->createTestClient();
        $this->authenticateAPI();

        // Act
        $response = $this->makeAPIRequest('GET', '/clients/' . $client['id']);

        // Assert - Validate response schema
        $schema = $this->loadSchema('client_response.json');
        $isValid = $this->validator->validate($response['body'], $schema);

        $this->assertTrue($isValid, 'Response does not match client schema: ' .
                         implode(', ', $this->validator->getErrors()));
    }

    /**
     * @group contract
     * @group api-schema
     */
    public function testCreateClientRequestSchema(): void
    {
        // Arrange
        $validRequest = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '555-0123'
        ];

        // Act & Assert
        $schema = $this->loadSchema('create_client_request.json');
        $isValid = $this->validator->validate($validRequest, $schema);

        $this->assertTrue($isValid);
    }

    /**
     * @group contract
     * @group api-schema
     */
    public function testPaginationSchema(): void
    {
        // Arrange
        $this->createTestClients(15);
        $this->authenticateAPI();

        // Act
        $response = $this->makeAPIRequest('GET', '/clients?page=1&limit=10');

        // Assert
        $paginationSchema = $this->loadSchema('pagination_response.json');
        $isValid = $this->validator->validate($response['body']['pagination'], $paginationSchema);

        $this->assertTrue($isValid, 'Pagination response does not match schema');

        // Verify pagination data
        $pagination = $response['body']['pagination'];
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(15, $pagination['total_items']);
        $this->assertEquals(2, $pagination['total_pages']);
    }

    private function loadSchema(string $schemaFile): array
    {
        $schemaPath = __DIR__ . '/schemas/' . $schemaFile;

        if (!file_exists($schemaPath)) {
            throw new InvalidArgumentException("Schema file not found: {$schemaFile}");
        }

        return json_decode(file_get_contents($schemaPath), true);
    }
}

class SchemaValidator
{
    private array $errors = [];

    public function validate(array $data, array $schema): bool
    {
        $this->errors = [];
        return $this->validateRecursive($data, $schema, '');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateRecursive(array $data, array $schema, string $path): bool
    {
        $valid = true;

        // Check required fields
        if (isset($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (!array_key_exists($field, $data)) {
                    $this->errors[] = "Required field missing: {$path}.{$field}";
                    $valid = false;
                }
            }
        }

        // Check properties
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $property => $propertySchema) {
                if (array_key_exists($property, $data)) {
                    $propertyPath = $path ? "{$path}.{$property}" : $property;

                    if (!$this->validateProperty($data[$property], $propertySchema, $propertyPath)) {
                        $valid = false;
                    }
                }
            }
        }

        return $valid;
    }

    private function validateProperty($value, array $schema, string $path): bool
    {
        $valid = true;

        // Type validation
        if (isset($schema['type'])) {
            if (!$this->validateType($value, $schema['type'])) {
                $this->errors[] = "Type mismatch at {$path}: expected {$schema['type']}, got " . gettype($value);
                $valid = false;
            }
        }

        // Format validation
        if (isset($schema['format'])) {
            if (!$this->validateFormat($value, $schema['format'])) {
                $this->errors[] = "Format validation failed at {$path}: expected {$schema['format']}";
                $valid = false;
            }
        }

        // Nested object validation
        if (isset($schema['properties']) && is_array($value)) {
            if (!$this->validateRecursive($value, $schema, $path)) {
                $valid = false;
            }
        }

        return $valid;
    }

    private function validateType($value, string $expectedType): bool
    {
        switch ($expectedType) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value);
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value);
            case 'array':
                return is_array($value);
            case 'object':
                return is_array($value) || is_object($value);
            default:
                return true;
        }
    }

    private function validateFormat($value, string $format): bool
    {
        switch ($format) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'date':
                return strtotime($value) !== false;
            case 'uri':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            default:
                return true;
        }
    }
}
```

### API Schema Files

Create `tests/schemas/client_response.json`:

```json
{
    "type": "object",
    "required": ["data"],
    "properties": {
        "data": {
            "type": "object",
            "required": ["id", "type", "attributes"],
            "properties": {
                "id": {
                    "type": "integer"
                },
                "type": {
                    "type": "string",
                    "enum": ["clients"]
                },
                "attributes": {
                    "type": "object",
                    "required": ["name", "email", "status", "created_at"],
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "email": {
                            "type": "string",
                            "format": "email"
                        },
                        "phone": {
                            "type": "string"
                        },
                        "status": {
                            "type": "string",
                            "enum": ["active", "suspended", "cancelled"]
                        },
                        "created_at": {
                            "type": "string",
                            "format": "date"
                        }
                    }
                }
            }
        }
    }
}
```

## ⚡ Performance Testing APIs

### Load Testing

```php
class APIPerformanceTest extends BaseTestCase
{
    /**
     * @group performance
     * @group api
     * @group slow
     */
    public function testAPIResponseTime(): void
    {
        $this->markTestSkipped('Only run during performance testing cycles');

        // Arrange
        $this->createTestClients(100);
        $this->authenticateAPI();

        // Act
        $startTime = microtime(true);
        $response = $this->makeAPIRequest('GET', '/clients?limit=50');
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertLessThan(2.0, $responseTime, 'API response time should be under 2 seconds');
    }

    /**
     * @group performance
     * @group api
     * @group concurrent
     */
    public function testConcurrentAPIRequests(): void
    {
        $this->markTestSkipped('Only run during performance testing cycles');

        // Arrange
        $this->createTestClients(10);
        $this->authenticateAPI();

        $concurrentRequests = 10;
        $promises = [];

        // Act - Simulate concurrent requests
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $promises[] = $this->makeAsyncAPIRequest('GET', '/clients');
        }

        $startTime = microtime(true);
        $responses = $this->resolvePromises($promises);
        $endTime = microtime(true);

        $totalTime = $endTime - $startTime;

        // Assert
        $this->assertCount($concurrentRequests, $responses);

        foreach ($responses as $response) {
            $this->assertEquals(200, $response['status_code']);
        }

        // Should handle concurrent requests efficiently
        $this->assertLessThan(5.0, $totalTime);
    }

    /**
     * @group performance
     * @group api
     * @group memory
     */
    public function testAPIMemoryUsage(): void
    {
        // Arrange
        $this->createTestClients(1000);
        $this->authenticateAPI();

        $startMemory = memory_get_usage();

        // Act
        $response = $this->makeAPIRequest('GET', '/clients?limit=500');

        $endMemory = memory_get_usage();
        $memoryUsed = $endMemory - $startMemory;

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertCount(500, $response['body']['data']);

        // Should not use excessive memory
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'API should use less than 50MB memory');
    }

    private function makeAsyncAPIRequest(string $method, string $endpoint): Promise
    {
        // Simulate async request (implementation depends on HTTP client)
        return new Promise(function($resolve) use ($method, $endpoint) {
            $response = $this->makeAPIRequest($method, $endpoint);
            $resolve($response);
        });
    }

    private function resolvePromises(array $promises): array
    {
        // Wait for all promises to resolve
        $responses = [];
        foreach ($promises as $promise) {
            $responses[] = $promise->wait();
        }
        return $responses;
    }
}
```

## 🔒 Security Testing

### API Security Tests

```php
class APISecurityTest extends BaseTestCase
{
    /**
     * @group security
     * @group api
     * @group authentication
     */
    public function testAPIAuthenticationRequired(): void
    {
        // Act - Request without authentication
        $response = $this->makeAPIRequest('GET', '/clients');

        // Assert
        $this->assertEquals(401, $response['status_code']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    /**
     * @group security
     * @group api
     * @group authorization
     */
    public function testAPIAuthorizationChecks(): void
    {
        // Arrange - Create user with limited permissions
        $limitedUser = $this->createTestUser(['role' => 'read_only']);
        $this->authenticateAs($limitedUser);

        // Act - Try to create client (should fail)
        $response = $this->makeAPIRequest('POST', '/clients', [
            'name' => 'Test Client',
            'email' => 'test@example.com'
        ]);

        // Assert
        $this->assertEquals(403, $response['status_code']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    /**
     * @group security
     * @group api
     * @group input-validation
     */
    public function testAPIInputValidation(): void
    {
        // Arrange
        $this->authenticateAPI();

        $maliciousInputs = [
            ['name' => '<script>alert("xss")</script>'],
            ['email' => '"; DROP TABLE clients; --'],
            ['phone' => '{{7*7}}'], // Template injection
            ['address' => str_repeat('A', 10000)] // Buffer overflow attempt
        ];

        foreach ($maliciousInputs as $input) {
            // Act
            $response = $this->makeAPIRequest('POST', '/clients', $input);

            // Assert - Should reject malicious input
            $this->assertContains($response['status_code'], [400, 422]);
        }
    }

    /**
     * @group security
     * @group api
     * @group rate-limiting
     */
    public function testAPIRateLimiting(): void
    {
        // Arrange
        $this->authenticateAPI();

        // Act - Make many requests quickly
        $responses = [];
        for ($i = 0; $i < 100; $i++) {
            $responses[] = $this->makeAPIRequest('GET', '/clients');
        }

        // Assert - Should eventually get rate limited
        $rateLimitedResponses = array_filter($responses, function($response) {
            return $response['status_code'] === 429;
        });

        $this->assertGreaterThan(0, count($rateLimitedResponses), 'Rate limiting should be enforced');
    }

    /**
     * @group security
     * @group api
     * @group injection
     */
    public function testSQLInjectionPrevention(): void
    {
        // Arrange
        $this->authenticateAPI();

        $injectionAttempts = [
            "1'; DROP TABLE clients; --",
            "1' OR '1'='1",
            "1' UNION SELECT * FROM users --"
        ];

        foreach ($injectionAttempts as $injection) {
            // Act
            $response = $this->makeAPIRequest('GET', "/clients/{$injection}");

            // Assert - Should handle safely
            $this->assertContains($response['status_code'], [400, 404]);

            // Verify table still exists
            $this->assertDatabaseTableExists('clients');
        }
    }

    /**
     * @group security
     * @group api
     * @group cors
     */
    public function testCORSHeaders(): void
    {
        // Act
        $response = $this->makeAPIRequest('OPTIONS', '/clients', [], [
            'Origin: https://example.com',
            'Access-Control-Request-Method: GET'
        ]);

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $response['headers']);
        $this->assertArrayHasKey('Access-Control-Allow-Methods', $response['headers']);
    }
}
```

## 📚 Documentation and Tools

### API Testing Tools

**Postman Collection Generation:**
```php
class PostmanCollectionGenerator
{
    public function generateCollection(): array
    {
        return [
            'info' => [
                'name' => 'ISP Management API',
                'description' => 'API collection for testing ISP management endpoints',
                'version' => '1.0.0'
            ],
            'item' => [
                $this->generateAuthenticationRequests(),
                $this->generateClientRequests(),
                $this->generateBillRequests()
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => 'http://localhost/api/v1'],
                ['key' => 'auth_token', 'value' => '']
            ]
        ];
    }

    private function generateClientRequests(): array
    {
        return [
            'name' => 'Clients',
            'item' => [
                [
                    'name' => 'Get All Clients',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}']
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/clients',
                            'host' => ['{{base_url}}'],
                            'path' => ['clients']
                        ]
                    ]
                ],
                [
                    'name' => 'Create Client',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'Authorization', 'value' => 'Bearer {{auth_token}}'],
                            ['key' => 'Content-Type', 'value' => 'application/json']
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'name' => 'Test Client',
                                'email' => 'test@example.com',
                                'phone' => '555-0123'
                            ])
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/clients',
                            'host' => ['{{base_url}}'],
                            'path' => ['clients']
                        ]
                    ]
                ]
            ]
        ];
    }
}
```

### API Documentation Testing

```php
class APIDocumentationTest extends BaseTestCase
{
    /**
     * @group documentation
     * @group api
     */
    public function testOpenAPISpecification(): void
    {
        // Act
        $response = $this->makeAPIRequest('GET', '/docs/openapi.json');

        // Assert
        $this->assertEquals(200, $response['status_code']);
        $this->assertArrayHasKey('openapi', $response['body']);
        $this->assertArrayHasKey('paths', $response['body']);
        $this->assertArrayHasKey('components', $response['body']);

        // Validate OpenAPI specification
        $validator = new OpenAPIValidator();
        $isValid = $validator->validate($response['body']);

        $this->assertTrue($isValid, 'OpenAPI specification is invalid');
    }

    /**
     * @group documentation
     * @group api
     */
    public function testAPIDocumentationEndpoints(): void
    {
        // Get OpenAPI spec
        $spec = $this->getOpenAPISpec();

        foreach ($spec['paths'] as $path => $methods) {
            foreach ($methods as $method => $definition) {
                if (in_array($method, ['get', 'post', 'put', 'delete', 'patch'])) {
                    $this->validateEndpointDocumentation($path, $method, $definition);
                }
            }
        }
    }

    private function validateEndpointDocumentation(string $path, string $method, array $definition): void
    {
        // Check required documentation fields
        $this->assertArrayHasKey('summary', $definition, "Missing summary for {$method} {$path}");
        $this->assertArrayHasKey('responses', $definition, "Missing responses for {$method} {$path}");

        // Check for 200 response
        $this->assertArrayHasKey('200', $definition['responses'], "Missing 200 response for {$method} {$path}");

        // Check for error responses
        if ($method !== 'get') {
            $this->assertArrayHasKey('400', $definition['responses'], "Missing 400 response for {$method} {$path}");
        }
    }

    private function getOpenAPISpec(): array
    {
        $response = $this->makeAPIRequest('GET', '/docs/openapi.json');
        return $response['body'];
    }
}
```

---

This comprehensive API testing guide provides everything needed to test APIs effectively in the ISP Management System. It covers internal endpoints, external integrations, security considerations, and documentation validation.

**Key Takeaways:**
1. Use the testing pyramid approach for balanced API test coverage
2. Mock external services to ensure reliable, fast tests
3. Validate API contracts and schemas consistently
4. Include security testing in your API test suite
5. Monitor API performance and establish baselines
6. Document your APIs and test the documentation

**Next Steps:**
1. Implement the test patterns that match your current needs
2. Set up API mocking infrastructure
3. Create contract tests for critical API endpoints
4. Integrate API tests into your CI/CD pipeline