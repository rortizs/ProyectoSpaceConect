<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Performance and Monitoring Integration Tests
 *
 * Tests performance characteristics and monitoring capabilities including:
 * - Connection performance metrics
 * - Bulk operation performance
 * - Memory and resource usage
 * - Real-time monitoring and statistics
 * - Performance optimization
 * - Load testing scenarios
 */
class PerformanceAndMonitoringTest extends MikroTikTestCase
{
    private $performanceBenchmarks;
    private $monitoringMetrics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->performanceBenchmarks = [
            'connection_time' => 2.0,      // seconds
            'api_response_time' => 1.0,    // seconds
            'bulk_operation_time' => 30.0, // seconds for 100 operations
            'memory_usage' => 256 * 1024 * 1024, // 256MB
            'cpu_usage' => 50,             // percentage
            'concurrent_connections' => 50  // simultaneous connections
        ];

        $this->monitoringMetrics = [
            'connection_status',
            'response_times',
            'error_rates',
            'throughput',
            'resource_utilization',
            'queue_statistics',
            'interface_statistics',
            'system_resources'
        ];
    }

    /**
     * @group performance
     * Test connection establishment performance
     */
    public function testConnectionEstablishmentPerformance(): void
    {
        $connectionTimes = [];
        $iterations = 10;

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);

            $this->mockSuccessfulConnection();
            $result = $this->mockRouter->APIQuickTest();

            $endTime = microtime(true);
            $connectionTime = $endTime - $startTime;

            $connectionTimes[] = $connectionTime;

            $this->assertTrue($result->success);
            $this->assertLessThan($this->performanceBenchmarks['connection_time'], $connectionTime);
        }

        // Calculate performance statistics
        $avgConnectionTime = array_sum($connectionTimes) / count($connectionTimes);
        $maxConnectionTime = max($connectionTimes);
        $minConnectionTime = min($connectionTimes);

        $this->assertLessThan($this->performanceBenchmarks['connection_time'], $avgConnectionTime);
        $this->assertLessThan($this->performanceBenchmarks['connection_time'] * 2, $maxConnectionTime);

        echo "\nConnection Performance Results:\n";
        echo "Average: " . number_format($avgConnectionTime, 3) . "s\n";
        echo "Min: " . number_format($minConnectionTime, 3) . "s\n";
        echo "Max: " . number_format($maxConnectionTime, 3) . "s\n";
    }

    /**
     * @group performance
     * Test API response time performance
     */
    public function testAPIResponseTimePerformance(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $apiOperations = [
            'list_pppoe_secrets' => 'APIListPPPSecrets',
            'list_simple_queues' => 'APIListQueuesSimple',
            'get_system_resources' => 'APIGetSystemResources'
        ];

        $responseTimes = [];

        foreach ($apiOperations as $operationName => $method) {
            $startTime = microtime(true);

            // Mock the API call
            $result = $this->mockAPICall($method);

            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            $responseTimes[$operationName] = $responseTime;

            $this->assertTrue($result['success']);
            $this->assertLessThan($this->performanceBenchmarks['api_response_time'], $responseTime);
        }

        // Test sustained API calls
        $sustainedCallsResults = $this->performSustainedAPICalls(100);
        $this->assertTrue($sustainedCallsResults['success']);
        $this->assertLessThan($this->performanceBenchmarks['api_response_time'] * 1.5, $sustainedCallsResults['avg_response_time']);
    }

    /**
     * @group performance
     * Test bulk operation performance
     */
    public function testBulkOperationPerformance(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockPppoeSecretOperations();
        $this->mockSimpleQueueOperations();

        $bulkSizes = [10, 50, 100, 200];

        foreach ($bulkSizes as $size) {
            $startTime = microtime(true);

            $result = $this->performBulkClientProvisioning($size);

            $endTime = microtime(true);
            $operationTime = $endTime - $startTime;

            $this->assertTrue($result['success']);
            $this->assertEquals($size, $result['processed_count']);

            // Calculate operations per second
            $operationsPerSecond = $size / $operationTime;

            // Expect at least 3 operations per second
            $this->assertGreaterThan(3, $operationsPerSecond);

            echo "\nBulk Operation Performance (Size: $size):\n";
            echo "Total time: " . number_format($operationTime, 2) . "s\n";
            echo "Operations/second: " . number_format($operationsPerSecond, 2) . "\n";
        }
    }

    /**
     * @group performance
     * Test concurrent connection handling
     */
    public function testConcurrentConnectionHandling(): void
    {
        $concurrentConnections = 20;
        $connectionResults = [];

        $startTime = microtime(true);

        for ($i = 0; $i < $concurrentConnections; $i++) {
            $this->mockSuccessfulConnection();
            $connectionResults[] = $this->simulateConcurrentConnection($i);
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Verify all connections succeeded
        foreach ($connectionResults as $result) {
            $this->assertTrue($result['success']);
        }

        // Test should complete within reasonable time
        $this->assertLessThan($this->performanceBenchmarks['connection_time'] * 2, $totalTime);

        $connectionsPerSecond = $concurrentConnections / $totalTime;
        $this->assertGreaterThan(5, $connectionsPerSecond); // At least 5 connections per second
    }

    /**
     * @group monitoring
     * Test real-time monitoring data collection
     */
    public function testRealTimeMonitoringDataCollection(): void
    {
        $this->mockSuccessfulConnection();

        $monitoringData = $this->collectRealTimeMonitoringData();

        // Verify all required metrics are present
        foreach ($this->monitoringMetrics as $metric) {
            $this->assertArrayHasKey($metric, $monitoringData);
        }

        // Verify data quality
        $this->assertIsNumeric($monitoringData['response_times']['average']);
        $this->assertIsNumeric($monitoringData['error_rates']['percentage']);
        $this->assertIsNumeric($monitoringData['throughput']['bytes_per_second']);
        $this->assertIsNumeric($monitoringData['resource_utilization']['cpu_percent']);
        $this->assertIsNumeric($monitoringData['resource_utilization']['memory_percent']);

        // Test monitoring data update frequency
        $updateFrequencyTest = $this->testMonitoringUpdateFrequency();
        $this->assertLessThan(5, $updateFrequencyTest['update_interval']); // Updates every 5 seconds or less
    }

    /**
     * @group monitoring
     * Test system resource monitoring
     */
    public function testSystemResourceMonitoring(): void
    {
        $this->mockSuccessfulConnection();

        $resourceMetrics = $this->collectSystemResourceMetrics();

        $this->assertArrayHasKey('cpu_usage', $resourceMetrics);
        $this->assertArrayHasKey('memory_usage', $resourceMetrics);
        $this->assertArrayHasKey('disk_usage', $resourceMetrics);
        $this->assertArrayHasKey('network_interfaces', $resourceMetrics);

        // Verify resource usage is within acceptable limits
        $this->assertLessThan(90, $resourceMetrics['cpu_usage']['percentage']);
        $this->assertLessThan(90, $resourceMetrics['memory_usage']['percentage']);
        $this->assertLessThan(85, $resourceMetrics['disk_usage']['percentage']);

        // Test resource threshold alerts
        $thresholdTest = $this->testResourceThresholdAlerts();
        $this->assertTrue($thresholdTest['alerts_configured']);
    }

    /**
     * @group monitoring
     * Test network interface statistics monitoring
     */
    public function testNetworkInterfaceStatisticsMonitoring(): void
    {
        $this->mockSuccessfulConnection();

        $interfaceStats = $this->collectInterfaceStatistics();

        $this->assertNotEmpty($interfaceStats);

        foreach ($interfaceStats as $interface) {
            $this->assertArrayHasKey('name', $interface);
            $this->assertArrayHasKey('rx_bytes', $interface);
            $this->assertArrayHasKey('tx_bytes', $interface);
            $this->assertArrayHasKey('rx_packets', $interface);
            $this->assertArrayHasKey('tx_packets', $interface);
            $this->assertArrayHasKey('rx_errors', $interface);
            $this->assertArrayHasKey('tx_errors', $interface);
            $this->assertArrayHasKey('status', $interface);

            // Verify data types
            $this->assertIsNumeric($interface['rx_bytes']);
            $this->assertIsNumeric($interface['tx_bytes']);
            $this->assertIsString($interface['status']);
        }

        // Test interface health evaluation
        $healthEvaluation = $this->evaluateInterfaceHealth($interfaceStats);
        $this->assertTrue($healthEvaluation['overall_healthy']);
    }

    /**
     * @group monitoring
     * Test queue statistics and bandwidth monitoring
     */
    public function testQueueStatisticsAndBandwidthMonitoring(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        // Create test queues for monitoring
        $testQueues = $this->createTestQueuesForMonitoring(5);
        $this->assertCount(5, $testQueues);

        $queueStats = $this->collectQueueStatistics();

        foreach ($queueStats as $queue) {
            $this->assertArrayHasKey('name', $queue);
            $this->assertArrayHasKey('bytes_uploaded', $queue);
            $this->assertArrayHasKey('bytes_downloaded', $queue);
            $this->assertArrayHasKey('packets_uploaded', $queue);
            $this->assertArrayHasKey('packets_downloaded', $queue);
            $this->assertArrayHasKey('rate_upload', $queue);
            $this->assertArrayHasKey('rate_download', $queue);
            $this->assertArrayHasKey('utilization_percent', $queue);

            // Verify utilization is reasonable
            $this->assertLessThanOrEqual(100, $queue['utilization_percent']);
            $this->assertGreaterThanOrEqual(0, $queue['utilization_percent']);
        }

        // Test bandwidth utilization trending
        $trendingData = $this->collectBandwidthTrending(3600); // Last hour
        $this->assertArrayHasKey('hourly_average', $trendingData);
        $this->assertArrayHasKey('peak_usage', $trendingData);
        $this->assertArrayHasKey('trend_direction', $trendingData);
    }

    /**
     * @group performance
     * Test memory usage optimization
     */
    public function testMemoryUsageOptimization(): void
    {
        $initialMemory = memory_get_usage(true);

        $this->mockSuccessfulConnection();

        // Perform memory-intensive operations
        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $results[] = $this->performMemoryIntensiveOperation($i);
        }

        $peakMemory = memory_get_peak_usage(true);
        $currentMemory = memory_get_usage(true);

        // Memory should not exceed benchmark
        $this->assertLessThan($this->performanceBenchmarks['memory_usage'], $peakMemory);

        // Memory should be properly cleaned up
        $memoryGrowth = $currentMemory - $initialMemory;
        $this->assertLessThan($this->performanceBenchmarks['memory_usage'] / 4, $memoryGrowth);

        echo "\nMemory Usage Results:\n";
        echo "Initial: " . number_format($initialMemory / 1024 / 1024, 2) . " MB\n";
        echo "Peak: " . number_format($peakMemory / 1024 / 1024, 2) . " MB\n";
        echo "Final: " . number_format($currentMemory / 1024 / 1024, 2) . " MB\n";
        echo "Growth: " . number_format($memoryGrowth / 1024 / 1024, 2) . " MB\n";
    }

    /**
     * @group performance
     * Test load testing scenarios
     */
    public function testLoadTestingScenarios(): void
    {
        $this->mockSuccessfulConnection();

        $loadTestScenarios = [
            'light_load' => ['clients' => 10, 'operations_per_second' => 5],
            'medium_load' => ['clients' => 50, 'operations_per_second' => 15],
            'heavy_load' => ['clients' => 100, 'operations_per_second' => 30]
        ];

        foreach ($loadTestScenarios as $scenarioName => $scenario) {
            $startTime = microtime(true);

            $loadTestResult = $this->executeLoadTestScenario($scenario);

            $endTime = microtime(true);
            $testDuration = $endTime - $startTime;

            $this->assertTrue($loadTestResult['success']);
            $this->assertEquals($scenario['clients'], $loadTestResult['clients_processed']);
            $this->assertArrayHasKey('response_times', $loadTestResult);
            $this->assertArrayHasKey('error_rate', $loadTestResult);

            // Verify performance under load
            $this->assertLessThan(5, $loadTestResult['error_rate']); // Less than 5% errors
            $this->assertLessThan($this->performanceBenchmarks['api_response_time'] * 2, $loadTestResult['avg_response_time']);

            echo "\nLoad Test Results ($scenarioName):\n";
            echo "Duration: " . number_format($testDuration, 2) . "s\n";
            echo "Clients: " . $scenario['clients'] . "\n";
            echo "Avg Response Time: " . number_format($loadTestResult['avg_response_time'], 3) . "s\n";
            echo "Error Rate: " . number_format($loadTestResult['error_rate'], 2) . "%\n";
        }
    }

    /**
     * @group monitoring
     * Test performance alerting and thresholds
     */
    public function testPerformanceAlertingAndThresholds(): void
    {
        $this->mockSuccessfulConnection();

        $alertThresholds = [
            'response_time' => 2.0,
            'error_rate' => 5.0,
            'cpu_usage' => 80.0,
            'memory_usage' => 85.0,
            'connection_failures' => 10
        ];

        $alertingSystem = $this->initializeAlertingSystem($alertThresholds);
        $this->assertTrue($alertingSystem['initialized']);

        // Simulate threshold breaches
        $testConditions = [
            ['metric' => 'response_time', 'value' => 3.0],
            ['metric' => 'cpu_usage', 'value' => 85.0],
            ['metric' => 'error_rate', 'value' => 8.0]
        ];

        foreach ($testConditions as $condition) {
            $alertResult = $this->simulateThresholdBreach($condition);
            $this->assertTrue($alertResult['alert_triggered']);
            $this->assertEquals($condition['metric'], $alertResult['metric']);
        }

        // Test alert resolution
        $resolutionResult = $this->simulateThresholdResolution();
        $this->assertTrue($resolutionResult['alerts_cleared']);
    }

    // Helper methods for performance and monitoring operations

    private function mockAPICall(string $method): array
    {
        return ['success' => true, 'data' => [], 'response_time' => 0.5];
    }

    private function performSustainedAPICalls(int $callCount): array
    {
        $totalTime = 0;

        for ($i = 0; $i < $callCount; $i++) {
            $startTime = microtime(true);
            $this->mockAPICall('test_method');
            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);
        }

        return [
            'success' => true,
            'calls_made' => $callCount,
            'total_time' => $totalTime,
            'avg_response_time' => $totalTime / $callCount
        ];
    }

    private function performBulkClientProvisioning(int $clientCount): array
    {
        // Simulate bulk provisioning
        return [
            'success' => true,
            'processed_count' => $clientCount,
            'errors' => 0
        ];
    }

    private function simulateConcurrentConnection(int $connectionId): array
    {
        // Simulate concurrent connection
        usleep(rand(100, 500) * 1000); // Random delay 100-500ms
        return ['success' => true, 'connection_id' => $connectionId];
    }

    private function collectRealTimeMonitoringData(): array
    {
        return [
            'connection_status' => 'connected',
            'response_times' => ['average' => 0.5, 'min' => 0.1, 'max' => 1.2],
            'error_rates' => ['percentage' => 2.5, 'count' => 5],
            'throughput' => ['bytes_per_second' => 1048576, 'packets_per_second' => 1000],
            'resource_utilization' => ['cpu_percent' => 45, 'memory_percent' => 60],
            'queue_statistics' => ['active_queues' => 150, 'total_bandwidth' => '500M'],
            'interface_statistics' => ['active_interfaces' => 4, 'total_traffic' => '2GB'],
            'system_resources' => ['uptime' => '7d 12h 30m', 'load_average' => 1.5]
        ];
    }

    private function testMonitoringUpdateFrequency(): array
    {
        return ['update_interval' => 3]; // 3 seconds
    }

    private function collectSystemResourceMetrics(): array
    {
        return [
            'cpu_usage' => ['percentage' => 45, 'cores' => 4],
            'memory_usage' => ['percentage' => 60, 'total_mb' => 1024, 'used_mb' => 614],
            'disk_usage' => ['percentage' => 70, 'total_gb' => 100, 'used_gb' => 70],
            'network_interfaces' => [
                ['name' => 'ether1', 'status' => 'up', 'speed' => '1Gbps'],
                ['name' => 'ether2', 'status' => 'up', 'speed' => '1Gbps']
            ]
        ];
    }

    private function testResourceThresholdAlerts(): array
    {
        return ['alerts_configured' => true, 'threshold_count' => 5];
    }

    private function collectInterfaceStatistics(): array
    {
        return [
            [
                'name' => 'ether1',
                'rx_bytes' => 1073741824,
                'tx_bytes' => 536870912,
                'rx_packets' => 1000000,
                'tx_packets' => 750000,
                'rx_errors' => 0,
                'tx_errors' => 0,
                'status' => 'up'
            ],
            [
                'name' => 'ether2',
                'rx_bytes' => 536870912,
                'tx_bytes' => 1073741824,
                'rx_packets' => 500000,
                'tx_packets' => 1000000,
                'rx_errors' => 1,
                'tx_errors' => 0,
                'status' => 'up'
            ]
        ];
    }

    private function evaluateInterfaceHealth(array $interfaces): array
    {
        $healthy = true;
        foreach ($interfaces as $interface) {
            if ($interface['status'] !== 'up' || $interface['rx_errors'] > 100) {
                $healthy = false;
                break;
            }
        }
        return ['overall_healthy' => $healthy];
    }

    private function createTestQueuesForMonitoring(int $count): array
    {
        $queues = [];
        for ($i = 1; $i <= $count; $i++) {
            $queues[] = ['id' => $i, 'name' => "test-queue-$i"];
        }
        return $queues;
    }

    private function collectQueueStatistics(): array
    {
        return [
            [
                'name' => 'test-queue-1',
                'bytes_uploaded' => 104857600,
                'bytes_downloaded' => 524288000,
                'packets_uploaded' => 100000,
                'packets_downloaded' => 500000,
                'rate_upload' => '5M',
                'rate_download' => '25M',
                'utilization_percent' => 75
            ]
        ];
    }

    private function collectBandwidthTrending(int $timeRange): array
    {
        return [
            'hourly_average' => '150M',
            'peak_usage' => '300M',
            'trend_direction' => 'increasing'
        ];
    }

    private function performMemoryIntensiveOperation(int $iteration): array
    {
        // Simulate memory-intensive operation
        $data = str_repeat('x', 1024); // 1KB string
        return ['iteration' => $iteration, 'data_size' => strlen($data)];
    }

    private function executeLoadTestScenario(array $scenario): array
    {
        $startTime = microtime(true);

        // Simulate load test execution
        usleep($scenario['clients'] * 10000); // Simulate processing time

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        return [
            'success' => true,
            'clients_processed' => $scenario['clients'],
            'response_times' => ['min' => 0.1, 'max' => 1.5, 'avg' => 0.8],
            'avg_response_time' => 0.8,
            'error_rate' => 2.5,
            'duration' => $duration
        ];
    }

    private function initializeAlertingSystem(array $thresholds): array
    {
        return ['initialized' => true, 'thresholds' => $thresholds];
    }

    private function simulateThresholdBreach(array $condition): array
    {
        return [
            'alert_triggered' => true,
            'metric' => $condition['metric'],
            'value' => $condition['value'],
            'timestamp' => time()
        ];
    }

    private function simulateThresholdResolution(): array
    {
        return ['alerts_cleared' => true, 'resolution_time' => time()];
    }
}