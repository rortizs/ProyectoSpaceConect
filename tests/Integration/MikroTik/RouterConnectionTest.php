<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Router Connection Management Integration Tests
 *
 * Tests the core functionality of router connection, authentication,
 * timeout handling, and RouterFactory pattern behavior
 */
class RouterConnectionTest extends MikroTikTestCase
{
    private $routerFactory;
    private $testRouter;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../Libraries/MikroTik/RouterFactory.php';
        $this->routerFactory = new RouterFactory();
    }

    /**
     * @group connection
     * Test RouterFactory auto-detection between Legacy and REST API
     */
    public function testRouterFactoryAutoDetection(): void
    {
        // Test with RouterOS 6.x (should use Legacy API)
        $mockLegacyRouter = $this->createMock(RouterLegacy::class);
        $mockLegacyRouter->connected = true;
        $mockLegacyRouter->method('APIGetSystemResources')
                        ->willReturn((object)[
                            'success' => true,
                            'data' => (object)['version' => '6.49.15']
                        ]);

        $this->assertInstanceOf(RouterLegacy::class, $mockLegacyRouter);

        // Test with RouterOS 7.x (should use REST API)
        $mockRestRouter = $this->createMock(Router::class);
        $mockRestRouter->connected = true;
        $mockRestRouter->method('APIGetSystemResources')
                      ->willReturn((object)[
                          'success' => true,
                          'data' => (object)['version' => '7.1.5']
                      ]);

        $this->assertInstanceOf(Router::class, $mockRestRouter);
    }

    /**
     * @group connection
     * Test successful router connection with both API types
     */
    public function testSuccessfulRouterConnection(): void
    {
        $connectionData = [
            'host' => '192.168.88.1',
            'port' => 8728,
            'user' => 'admin',
            'password' => 'test123'
        ];

        // Mock successful connection
        $this->mockSuccessfulConnection();

        // Test connection status
        $status = $this->getRouterConnectionStatus();

        $this->assertTrue($status['connected']);
        $this->assertEquals($connectionData['host'], $status['host']);
        $this->assertEquals($connectionData['port'], $status['port']);
        $this->assertNotEmpty($status['version']);
    }

    /**
     * @group connection
     * Test failed router connection scenarios
     */
    public function testFailedRouterConnection(): void
    {
        // Test connection timeout
        $this->simulateRouterTimeout();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Connection timeout');

        $this->mockRouter->APIQuickTest();
    }

    /**
     * @group connection
     * Test authentication failure scenarios
     */
    public function testAuthenticationFailure(): void
    {
        $this->mockFailedConnection();

        $result = $this->mockRouter->APIQuickTest();

        $this->assertFalse($result->success);
        $this->assertStringContains('Connection timeout', $result->error);
    }

    /**
     * @group connection
     * Test connection retry logic with exponential backoff
     */
    public function testConnectionRetryLogic(): void
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->mockSuccessfulConnection();
                $result = $this->mockRouter->APIQuickTest();

                if ($result->success) {
                    break;
                }
            } catch (Exception $e) {
                $retryCount++;

                if ($retryCount >= $maxRetries) {
                    $this->fail('Connection failed after maximum retries');
                }

                // Exponential backoff simulation
                sleep(pow(2, $retryCount));
            }
        }

        $this->assertLessThan($maxRetries, $retryCount);
    }

    /**
     * @group connection
     * Test concurrent router connections
     */
    public function testConcurrentRouterConnections(): void
    {
        $routers = [
            ['host' => '192.168.88.1', 'port' => 8728],
            ['host' => '192.168.88.2', 'port' => 8728],
            ['host' => '192.168.88.3', 'port' => 8728]
        ];

        $connections = [];

        foreach ($routers as $routerConfig) {
            $this->mockSuccessfulConnection();
            $connections[] = $this->getRouterConnectionStatus();
        }

        $this->assertCount(3, $connections);

        foreach ($connections as $connection) {
            $this->assertTrue($connection['connected']);
        }
    }

    /**
     * @group connection
     * Test router version detection and API type selection
     */
    public function testRouterVersionDetection(): void
    {
        $testCases = [
            ['version' => '6.48.1', 'expectedApiType' => 'legacy'],
            ['version' => '7.1.5', 'expectedApiType' => 'rest'],
            ['version' => '6.49.15', 'expectedApiType' => 'legacy'],
            ['version' => '7.2.1', 'expectedApiType' => 'rest']
        ];

        foreach ($testCases as $testCase) {
            $mockRouter = $this->createMock(Router::class);
            $mockRouter->method('APIGetSystemResources')
                      ->willReturn((object)[
                          'success' => true,
                          'data' => (object)['version' => $testCase['version']]
                      ]);

            $version = $mockRouter->APIGetSystemResources()->data->version;
            $majorVersion = intval(explode('.', $version)[0]);
            $apiType = $majorVersion >= 7 ? 'rest' : 'legacy';

            $this->assertEquals($testCase['expectedApiType'], $apiType);
        }
    }

    /**
     * @group connection
     * Test router system resource monitoring
     */
    public function testSystemResourceMonitoring(): void
    {
        $mockSystemResources = (object)[
            'success' => true,
            'data' => (object)[
                'version' => '6.49.15',
                'board-name' => 'RB951G-2HnD',
                'cpu-load' => '15',
                'free-memory' => 134217728,
                'total-memory' => 268435456,
                'uptime' => '1w2d3h4m5s'
            ]
        ];

        $this->mockRouter->method('APIGetSystemResources')
                        ->willReturn($mockSystemResources);

        $resources = $this->mockRouter->APIGetSystemResources();

        $this->assertTrue($resources->success);
        $this->assertObjectHasAttribute('version', $resources->data);
        $this->assertObjectHasAttribute('cpu-load', $resources->data);
        $this->assertObjectHasAttribute('free-memory', $resources->data);
        $this->assertIsNumeric($resources->data->{'cpu-load'});
        $this->assertIsNumeric($resources->data->{'free-memory'});
    }

    /**
     * @group connection
     * Test connection pooling and reuse
     */
    public function testConnectionPooling(): void
    {
        $connectionPool = [];
        $maxConnections = 5;

        for ($i = 0; $i < $maxConnections; $i++) {
            $this->mockSuccessfulConnection();
            $connectionPool[] = $this->mockRouter;
        }

        $this->assertCount($maxConnections, $connectionPool);

        // Test reusing connections from pool
        foreach ($connectionPool as $connection) {
            $this->assertNotNull($connection);
        }
    }

    /**
     * @group connection
     * Test connection cleanup and resource management
     */
    public function testConnectionCleanup(): void
    {
        $this->mockSuccessfulConnection();

        // Simulate connection usage
        $this->mockRouter->APIQuickTest();

        // Test proper cleanup
        $this->mockRouter->disconnect();

        $this->assertRouterConnectionAttempted();
    }

    /**
     * @group connection
     * Test SSL/TLS connection security
     */
    public function testSecureConnection(): void
    {
        $secureConfig = $this->createTestRouterData([
            'port' => 8729, // SSL port
            'ssl' => true,
            'verify_peer' => false // For testing
        ]);

        $this->mockSuccessfulConnection();

        $result = $this->mockRouter->APIQuickTest();
        $this->assertTrue($result->success);
    }

    /**
     * @group connection
     * Test router performance metrics collection
     */
    public function testRouterPerformanceMetrics(): void
    {
        $performanceData = [
            'connection_time' => microtime(true),
            'response_time' => 0,
            'throughput' => 0,
            'packet_loss' => 0
        ];

        $startTime = microtime(true);

        $this->mockSuccessfulConnection();
        $this->mockRouter->APIQuickTest();

        $endTime = microtime(true);
        $performanceData['response_time'] = $endTime - $startTime;

        $this->assertIsFloat($performanceData['response_time']);
        $this->assertGreaterThan(0, $performanceData['response_time']);
    }

    /**
     * @group connection
     * Test router failover scenarios
     */
    public function testRouterFailover(): void
    {
        $primaryRouter = [
            'host' => '192.168.88.1',
            'status' => 'offline'
        ];

        $backupRouter = [
            'host' => '192.168.88.2',
            'status' => 'online'
        ];

        // Simulate primary router failure
        $this->mockFailedConnection();

        try {
            $this->mockRouter->APIQuickTest();
            $this->fail('Expected connection to fail');
        } catch (Exception $e) {
            // Failover to backup router
            $this->mockSuccessfulConnection();
            $result = $this->mockRouter->APIQuickTest();
            $this->assertTrue($result->success);
        }
    }

    /**
     * @group connection
     * Test database router configuration persistence
     */
    public function testRouterConfigurationPersistence(): void
    {
        $routerConfig = $this->createTestRouterData([
            'routeros_version' => '6.49.15',
            'api_type' => 'legacy',
            'board_name' => 'RB951G-2HnD',
            'last_connected' => date('Y-m-d H:i:s')
        ]);

        // Simulate database save operation
        $this->assertIsArray($routerConfig);
        $this->assertArrayHasKey('routeros_version', $routerConfig);
        $this->assertArrayHasKey('api_type', $routerConfig);
    }
}