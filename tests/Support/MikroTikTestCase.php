<?php

require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/Traits/MocksExternalServices.php';

/**
 * MikroTik Test Case
 *
 * Base class for tests that interact with MikroTik routers.
 * Provides mocking capabilities for router API interactions.
 */
abstract class MikroTikTestCase extends BaseTestCase
{
    use MocksExternalServices;

    /**
     * Mock router instance
     */
    protected $mockRouter;

    /**
     * Test router configuration
     */
    protected array $testRouterConfig = [];

    /**
     * Set up MikroTik testing environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeMikroTikTest();
        $this->setupRouterMocks();
    }

    /**
     * Clean up MikroTik testing environment
     */
    protected function tearDown(): void
    {
        $this->cleanupRouterMocks();
        parent::tearDown();
    }

    /**
     * Initialize MikroTik test environment
     */
    protected function initializeMikroTikTest(): void
    {
        $this->testRouterConfig = [
            'host' => $this->getTestConfig('mikrotik_host', '192.168.88.1'),
            'port' => $this->getTestConfig('mikrotik_port', 8728),
            'user' => $this->getTestConfig('mikrotik_user', 'admin'),
            'password' => $this->getTestConfig('mikrotik_password', 'test123'),
            'timeout' => 5,
        ];
    }

    /**
     * Set up router mocks
     */
    protected function setupRouterMocks(): void
    {
        if (class_exists('Mockery') && (defined('MOCK_EXTERNAL_SERVICES') && MOCK_EXTERNAL_SERVICES)) {
            $this->mockRouter = \Mockery::mock('Router');
            $this->setupDefaultRouterMocks();
        }
    }

    /**
     * Set up default router mock behaviors
     */
    protected function setupDefaultRouterMocks(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        // Mock successful connection
        $this->mockRouter->shouldReceive('APIQuickTest')
                        ->andReturn((object)['success' => true])
                        ->byDefault();

        // Mock successful login
        $this->mockRouter->shouldReceive('login')
                        ->andReturn(true)
                        ->byDefault();

        // Mock disconnect
        $this->mockRouter->shouldReceive('disconnect')
                        ->andReturn(true)
                        ->byDefault();
    }

    /**
     * Clean up router mocks
     */
    protected function cleanupRouterMocks(): void
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
        $this->mockRouter = null;
    }

    /**
     * Mock successful router connection
     */
    protected function mockSuccessfulConnection(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        $this->mockRouter->shouldReceive('APIQuickTest')
                        ->andReturn((object)['success' => true]);

        $this->mockRouter->shouldReceive('login')
                        ->with($this->testRouterConfig['user'], $this->testRouterConfig['password'])
                        ->andReturn(true);
    }

    /**
     * Mock failed router connection
     */
    protected function mockFailedConnection(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        $this->mockRouter->shouldReceive('APIQuickTest')
                        ->andReturn((object)['success' => false, 'error' => 'Connection timeout']);

        $this->mockRouter->shouldReceive('login')
                        ->andReturn(false);
    }

    /**
     * Mock PPPoE secret operations
     */
    protected function mockPppoeSecretOperations(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        // Mock adding PPPoE secret
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ppp/secret/add', \Mockery::type('array'))
                        ->andReturn([
                            '!done',
                            '=ret=*1'
                        ]);

        // Mock removing PPPoE secret
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ppp/secret/remove', \Mockery::type('array'))
                        ->andReturn(['!done']);

        // Mock enabling/disabling PPPoE secret
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ppp/secret/enable', \Mockery::type('array'))
                        ->andReturn(['!done']);

        $this->mockRouter->shouldReceive('comm')
                        ->with('/ppp/secret/disable', \Mockery::type('array'))
                        ->andReturn(['!done']);

        // Mock getting PPPoE secrets
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ppp/secret/print', \Mockery::type('array'))
                        ->andReturn([
                            '!re',
                            '=.id=*1',
                            '=name=testuser',
                            '=service=pppoe',
                            '=profile=default',
                            '=disabled=false',
                            '!done'
                        ]);
    }

    /**
     * Mock Simple Queue operations
     */
    protected function mockSimpleQueueOperations(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        // Mock adding Simple Queue
        $this->mockRouter->shouldReceive('comm')
                        ->with('/queue/simple/add', \Mockery::type('array'))
                        ->andReturn([
                            '!done',
                            '=ret=*1'
                        ]);

        // Mock removing Simple Queue
        $this->mockRouter->shouldReceive('comm')
                        ->with('/queue/simple/remove', \Mockery::type('array'))
                        ->andReturn(['!done']);

        // Mock enabling/disabling Simple Queue
        $this->mockRouter->shouldReceive('comm')
                        ->with('/queue/simple/enable', \Mockery::type('array'))
                        ->andReturn(['!done']);

        $this->mockRouter->shouldReceive('comm')
                        ->with('/queue/simple/disable', \Mockery::type('array'))
                        ->andReturn(['!done']);

        // Mock getting Simple Queues
        $this->mockRouter->shouldReceive('comm')
                        ->with('/queue/simple/print', \Mockery::type('array'))
                        ->andReturn([
                            '!re',
                            '=.id=*1',
                            '=name=test-queue',
                            '=target=192.168.1.100/32',
                            '=max-limit=10M/50M',
                            '=disabled=false',
                            '!done'
                        ]);
    }

    /**
     * Mock Firewall Filter operations
     */
    protected function mockFirewallFilterOperations(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        // Mock adding firewall filter rule
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ip/firewall/filter/add', \Mockery::type('array'))
                        ->andReturn([
                            '!done',
                            '=ret=*1'
                        ]);

        // Mock removing firewall filter rule
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ip/firewall/filter/remove', \Mockery::type('array'))
                        ->andReturn(['!done']);

        // Mock getting firewall filter rules
        $this->mockRouter->shouldReceive('comm')
                        ->with('/ip/firewall/filter/print', \Mockery::type('array'))
                        ->andReturn([
                            '!re',
                            '=.id=*1',
                            '=chain=forward',
                            '=action=drop',
                            '=src-address=192.168.1.100',
                            '=disabled=false',
                            '!done'
                        ]);
    }

    /**
     * Create test router data
     */
    protected function createTestRouterData(array $overrides = []): array
    {
        return array_merge($this->testRouterConfig, [
            'nombre' => 'Test Router',
            'descripcion' => 'Test router for unit testing',
            'estado' => 1,
            'version' => '6.48.1',
            'board' => 'RB951G-2HnD',
            'uptime' => '1w2d3h4m5s',
        ], $overrides);
    }

    /**
     * Create test PPPoE secret data
     */
    protected function createTestPppoeSecretData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'testuser' . $this->randomString(5),
            'password' => $this->randomString(8),
            'service' => 'pppoe',
            'profile' => 'default',
            'remote-address' => '10.0.0.100',
            'comment' => 'Test PPPoE secret',
        ], $overrides);
    }

    /**
     * Create test Simple Queue data
     */
    protected function createTestSimpleQueueData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'test-queue-' . $this->randomString(5),
            'target' => '192.168.1.100/32',
            'max-limit' => '10M/50M',
            'burst-limit' => '20M/100M',
            'burst-threshold' => '8M/40M',
            'burst-time' => '30s/30s',
            'comment' => 'Test Simple Queue',
        ], $overrides);
    }

    /**
     * Create test firewall filter rule data
     */
    protected function createTestFirewallFilterData(array $overrides = []): array
    {
        return array_merge([
            'chain' => 'forward',
            'action' => 'drop',
            'src-address' => '192.168.1.100',
            'dst-port' => '80,443',
            'protocol' => 'tcp',
            'comment' => 'Test firewall rule',
        ], $overrides);
    }

    /**
     * Assert that router API call was made
     */
    protected function assertRouterApiCalled(string $command, array $parameters = []): void
    {
        if (!$this->mockRouter) {
            $this->markTestSkipped('Router mocking not available');
        }

        $this->mockRouter->shouldHaveReceived('comm')
                        ->with($command, \Mockery::subset($parameters))
                        ->atLeast()
                        ->once();
    }

    /**
     * Assert that router connection was attempted
     */
    protected function assertRouterConnectionAttempted(): void
    {
        if (!$this->mockRouter) {
            $this->markTestSkipped('Router mocking not available');
        }

        $this->mockRouter->shouldHaveReceived('APIQuickTest')
                        ->atLeast()
                        ->once();
    }

    /**
     * Assert that router login was attempted
     */
    protected function assertRouterLoginAttempted(string $username = null, string $password = null): void
    {
        if (!$this->mockRouter) {
            $this->markTestSkipped('Router mocking not available');
        }

        $expectation = $this->mockRouter->shouldHaveReceived('login')
                                       ->atLeast()
                                       ->once();

        if ($username && $password) {
            $expectation->with($username, $password);
        }
    }

    /**
     * Get router connection status for testing
     */
    protected function getRouterConnectionStatus(): array
    {
        return [
            'connected' => true,
            'host' => $this->testRouterConfig['host'],
            'port' => $this->testRouterConfig['port'],
            'user' => $this->testRouterConfig['user'],
            'version' => '6.48.1',
            'uptime' => '1w2d3h4m5s',
            'board' => 'RB951G-2HnD',
        ];
    }

    /**
     * Simulate router API error response
     */
    protected function simulateRouterApiError(string $command, string $error = 'API error'): void
    {
        if (!$this->mockRouter) {
            return;
        }

        $this->mockRouter->shouldReceive('comm')
                        ->with($command, \Mockery::any())
                        ->andReturn([
                            '!trap',
                            '=message=' . $error,
                            '!done'
                        ]);
    }

    /**
     * Simulate router connection timeout
     */
    protected function simulateRouterTimeout(): void
    {
        if (!$this->mockRouter) {
            return;
        }

        $this->mockRouter->shouldReceive('APIQuickTest')
                        ->andThrow(new Exception('Connection timeout'));

        $this->mockRouter->shouldReceive('login')
                        ->andThrow(new Exception('Connection timeout'));
    }
}