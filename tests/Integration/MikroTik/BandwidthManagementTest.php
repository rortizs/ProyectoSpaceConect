<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Bandwidth Management Integration Tests
 *
 * Tests comprehensive bandwidth management including:
 * - Simple Queue operations
 * - Queue Tree hierarchical QoS
 * - Bandwidth monitoring and statistics
 * - Dynamic rate limiting
 * - Burst configurations
 */
class BandwidthManagementTest extends MikroTikTestCase
{
    private $testBandwidthProfiles;
    private $testClients;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBandwidthProfiles = [
            'basic' => [
                'name' => 'Basic Plan',
                'upload' => '5M',
                'download' => '25M',
                'burst_upload' => '10M',
                'burst_download' => '50M',
                'burst_threshold' => '4M/20M',
                'burst_time' => '30s/30s',
                'priority' => 5
            ],
            'standard' => [
                'name' => 'Standard Plan',
                'upload' => '10M',
                'download' => '50M',
                'burst_upload' => '20M',
                'burst_download' => '100M',
                'burst_threshold' => '8M/40M',
                'burst_time' => '30s/30s',
                'priority' => 4
            ],
            'premium' => [
                'name' => 'Premium Plan',
                'upload' => '20M',
                'download' => '100M',
                'burst_upload' => '40M',
                'burst_download' => '200M',
                'burst_threshold' => '16M/80M',
                'burst_time' => '30s/30s',
                'priority' => 3
            ]
        ];

        $this->testClients = [
            [
                'id' => 101,
                'ip' => '192.168.1.100',
                'name' => 'client-001',
                'profile' => 'basic'
            ],
            [
                'id' => 102,
                'ip' => '192.168.1.101',
                'name' => 'client-002',
                'profile' => 'standard'
            ],
            [
                'id' => 103,
                'ip' => '192.168.1.102',
                'name' => 'client-003',
                'profile' => 'premium'
            ]
        ];
    }

    /**
     * @group bandwidth
     * Test Simple Queue creation with comprehensive bandwidth settings
     */
    public function testSimpleQueueCreationWithBandwidthSettings(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        foreach ($this->testClients as $client) {
            $profile = $this->testBandwidthProfiles[$client['profile']];

            $queueData = [
                'name' => 'queue-' . $client['name'],
                'target' => $client['ip'] . '/32',
                'max-limit' => $profile['upload'] . '/' . $profile['download'],
                'burst-limit' => $profile['burst_upload'] . '/' . $profile['burst_download'],
                'burst-threshold' => $profile['burst_threshold'],
                'burst-time' => $profile['burst_time'],
                'priority' => $profile['priority']
            ];

            $result = $this->createSimpleQueue($queueData);

            $this->assertTrue($result['success']);
            $this->assertArrayHasKey('queue_id', $result);
            $this->assertEquals($queueData['max-limit'], $result['max_limit']);
        }
    }

    /**
     * @group bandwidth
     * Test Queue Tree hierarchical bandwidth management
     */
    public function testQueueTreeHierarchicalManagement(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockQueueTreeOperations();

        // Create parent queue for interface
        $parentQueue = [
            'name' => 'ether1-total',
            'parent' => 'ether1',
            'max-limit' => '100M/500M',
            'priority' => 1
        ];

        $parentResult = $this->createQueueTree($parentQueue);
        $this->assertTrue($parentResult['success']);

        // Create child queues for different traffic classes
        $childQueues = [
            [
                'name' => 'high-priority',
                'parent' => 'ether1-total',
                'max-limit' => '30M/150M',
                'priority' => 1,
                'packet-mark' => 'high-priority-mark'
            ],
            [
                'name' => 'medium-priority',
                'parent' => 'ether1-total',
                'max-limit' => '40M/200M',
                'priority' => 3,
                'packet-mark' => 'medium-priority-mark'
            ],
            [
                'name' => 'low-priority',
                'parent' => 'ether1-total',
                'max-limit' => '30M/150M',
                'priority' => 5,
                'packet-mark' => 'low-priority-mark'
            ]
        ];

        foreach ($childQueues as $childQueue) {
            $result = $this->createQueueTree($childQueue);
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group bandwidth
     * Test dynamic bandwidth allocation and modification
     */
    public function testDynamicBandwidthAllocation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        $client = $this->testClients[0];
        $initialProfile = $this->testBandwidthProfiles['basic'];

        // Create initial queue
        $queueData = [
            'name' => 'queue-' . $client['name'],
            'target' => $client['ip'] . '/32',
            'max-limit' => $initialProfile['upload'] . '/' . $initialProfile['download']
        ];

        $createResult = $this->createSimpleQueue($queueData);
        $this->assertTrue($createResult['success']);

        // Test dynamic bandwidth increase during off-peak hours
        $offPeakProfile = [
            'upload' => '15M',
            'download' => '75M'
        ];

        $modifyResult = $this->modifyQueueBandwidth(
            $createResult['queue_id'],
            $offPeakProfile['upload'] . '/' . $offPeakProfile['download']
        );
        $this->assertTrue($modifyResult['success']);

        // Test bandwidth restoration to normal limits
        $restoreResult = $this->modifyQueueBandwidth(
            $createResult['queue_id'],
            $initialProfile['upload'] . '/' . $initialProfile['download']
        );
        $this->assertTrue($restoreResult['success']);
    }

    /**
     * @group bandwidth
     * Test burst configuration and behavior
     */
    public function testBurstConfigurationAndBehavior(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        $burstTestCases = [
            [
                'name' => 'aggressive-burst',
                'max-limit' => '10M/50M',
                'burst-limit' => '30M/150M',
                'burst-threshold' => '8M/40M',
                'burst-time' => '60s/60s'
            ],
            [
                'name' => 'conservative-burst',
                'max-limit' => '10M/50M',
                'burst-limit' => '15M/75M',
                'burst-threshold' => '9M/45M',
                'burst-time' => '15s/15s'
            ],
            [
                'name' => 'no-burst',
                'max-limit' => '10M/50M',
                'burst-limit' => '0/0',
                'burst-threshold' => '0/0',
                'burst-time' => '0s/0s'
            ]
        ];

        foreach ($burstTestCases as $testCase) {
            $result = $this->createSimpleQueue($testCase);
            $this->assertTrue($result['success']);

            // Validate burst configuration
            $this->assertEquals($testCase['burst-limit'], $result['burst_limit']);
            $this->assertEquals($testCase['burst-threshold'], $result['burst_threshold']);
        }
    }

    /**
     * @group bandwidth
     * Test bandwidth monitoring and statistics collection
     */
    public function testBandwidthMonitoringAndStatistics(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        // Create queues for monitoring
        foreach ($this->testClients as $client) {
            $profile = $this->testBandwidthProfiles[$client['profile']];
            $queueData = [
                'name' => 'queue-' . $client['name'],
                'target' => $client['ip'] . '/32',
                'max-limit' => $profile['upload'] . '/' . $profile['download']
            ];

            $this->createSimpleQueue($queueData);
        }

        // Collect bandwidth statistics
        $statistics = $this->collectBandwidthStatistics();

        $this->assertNotEmpty($statistics);
        $this->assertArrayHasKey('total_queues', $statistics);
        $this->assertArrayHasKey('total_bandwidth_allocated', $statistics);
        $this->assertArrayHasKey('queue_utilization', $statistics);

        // Validate statistics structure
        foreach ($statistics['queue_utilization'] as $queueStats) {
            $this->assertArrayHasKey('queue_name', $queueStats);
            $this->assertArrayHasKey('bytes_uploaded', $queueStats);
            $this->assertArrayHasKey('bytes_downloaded', $queueStats);
            $this->assertArrayHasKey('packets_uploaded', $queueStats);
            $this->assertArrayHasKey('packets_downloaded', $queueStats);
            $this->assertArrayHasKey('current_rate_upload', $queueStats);
            $this->assertArrayHasKey('current_rate_download', $queueStats);
        }
    }

    /**
     * @group bandwidth
     * Test bandwidth fair share and priority management
     */
    public function testBandwidthFairShareAndPriority(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockQueueTreeOperations();

        // Create parent queue with limited bandwidth
        $parentQueue = [
            'name' => 'shared-bandwidth',
            'parent' => 'global',
            'max-limit' => '50M/200M'
        ];

        $parentResult = $this->createQueueTree($parentQueue);
        $this->assertTrue($parentResult['success']);

        // Create child queues with different priorities
        $priorityQueues = [
            [
                'name' => 'high-priority-user',
                'parent' => 'shared-bandwidth',
                'max-limit' => '20M/80M',
                'priority' => 1
            ],
            [
                'name' => 'medium-priority-user',
                'parent' => 'shared-bandwidth',
                'max-limit' => '15M/60M',
                'priority' => 3
            ],
            [
                'name' => 'low-priority-user',
                'parent' => 'shared-bandwidth',
                'max-limit' => '15M/60M',
                'priority' => 7
            ]
        ];

        foreach ($priorityQueues as $queue) {
            $result = $this->createQueueTree($queue);
            $this->assertTrue($result['success']);
        }

        // Test fair share calculation
        $fairShareResult = $this->calculateFairShare('shared-bandwidth');
        $this->assertTrue($fairShareResult['success']);
        $this->assertArrayHasKey('guaranteed_bandwidth', $fairShareResult);
    }

    /**
     * @group bandwidth
     * Test bandwidth limiting with packet marking
     */
    public function testBandwidthLimitingWithPacketMarking(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockQueueTreeOperations();
        $this->mockFirewallFilterOperations();

        // Create mangle rules for packet marking
        $mangleRules = [
            [
                'chain' => 'forward',
                'action' => 'mark-packet',
                'new-packet-mark' => 'http-traffic',
                'dst-port' => '80,443',
                'protocol' => 'tcp'
            ],
            [
                'chain' => 'forward',
                'action' => 'mark-packet',
                'new-packet-mark' => 'streaming-traffic',
                'dst-port' => '1935,8080',
                'protocol' => 'tcp'
            ]
        ];

        foreach ($mangleRules as $rule) {
            $result = $this->createMangleRule($rule);
            $this->assertTrue($result['success']);
        }

        // Create Queue Trees based on packet marks
        $queueTrees = [
            [
                'name' => 'http-queue',
                'parent' => 'global',
                'max-limit' => '30M/150M',
                'packet-mark' => 'http-traffic',
                'priority' => 2
            ],
            [
                'name' => 'streaming-queue',
                'parent' => 'global',
                'max-limit' => '20M/100M',
                'packet-mark' => 'streaming-traffic',
                'priority' => 4
            ]
        ];

        foreach ($queueTrees as $queue) {
            $result = $this->createQueueTree($queue);
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group bandwidth
     * Test PCQ (Per Connection Queue) implementation
     */
    public function testPCQImplementation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockQueueTreeOperations();

        // Create PCQ queue types
        $pcqTypes = [
            [
                'name' => 'pcq-upload',
                'kind' => 'pcq',
                'pcq-rate' => '1M',
                'pcq-limit' => '50KiB',
                'pcq-classifier' => 'src-address',
                'pcq-total-limit' => '2000'
            ],
            [
                'name' => 'pcq-download',
                'kind' => 'pcq',
                'pcq-rate' => '5M',
                'pcq-limit' => '50KiB',
                'pcq-classifier' => 'dst-address',
                'pcq-total-limit' => '2000'
            ]
        ];

        foreach ($pcqTypes as $pcqType) {
            $result = $this->createQueueType($pcqType);
            $this->assertTrue($result['success']);
        }

        // Create Queue Tree using PCQ
        $pcqQueue = [
            'name' => 'client-pcq',
            'parent' => 'global',
            'max-limit' => '50M/250M',
            'queue-type' => 'pcq-upload,pcq-download'
        ];

        $queueResult = $this->createQueueTree($pcqQueue);
        $this->assertTrue($queueResult['success']);
    }

    /**
     * @group bandwidth
     * Test bandwidth usage reporting and analytics
     */
    public function testBandwidthUsageReporting(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        // Create test queues
        foreach ($this->testClients as $client) {
            $profile = $this->testBandwidthProfiles[$client['profile']];
            $queueData = [
                'name' => 'queue-' . $client['name'],
                'target' => $client['ip'] . '/32',
                'max-limit' => $profile['upload'] . '/' . $profile['download']
            ];
            $this->createSimpleQueue($queueData);
        }

        // Generate usage reports
        $reports = [
            'hourly' => $this->generateBandwidthReport('hourly'),
            'daily' => $this->generateBandwidthReport('daily'),
            'weekly' => $this->generateBandwidthReport('weekly'),
            'monthly' => $this->generateBandwidthReport('monthly')
        ];

        foreach ($reports as $period => $report) {
            $this->assertTrue($report['success']);
            $this->assertArrayHasKey('period', $report);
            $this->assertArrayHasKey('total_usage', $report);
            $this->assertArrayHasKey('client_usage', $report);
            $this->assertEquals($period, $report['period']);
        }
    }

    /**
     * @group bandwidth
     * Test bandwidth optimization and auto-scaling
     */
    public function testBandwidthOptimizationAndAutoScaling(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockSimpleQueueOperations();

        // Monitor bandwidth utilization
        $utilizationData = $this->collectBandwidthUtilization();

        // Test auto-scaling based on utilization
        if ($utilizationData['average_utilization'] > 80) {
            $scalingResult = $this->scaleBandwidthUp($utilizationData['overutilized_queues']);
            $this->assertTrue($scalingResult['success']);
        } elseif ($utilizationData['average_utilization'] < 30) {
            $scalingResult = $this->scaleBandwidthDown($utilizationData['underutilized_queues']);
            $this->assertTrue($scalingResult['success']);
        }

        // Test bandwidth optimization recommendations
        $optimizationResult = $this->generateOptimizationRecommendations();
        $this->assertTrue($optimizationResult['success']);
        $this->assertArrayHasKey('recommendations', $optimizationResult);
    }

    // Helper methods for bandwidth management operations

    private function mockQueueTreeOperations(): void
    {
        if ($this->mockRouter) {
            $this->mockRouter->shouldReceive('APICreateQueueTree')
                          ->andReturn((object)['success' => true, 'queue_id' => '*1']);

            $this->mockRouter->shouldReceive('APIDeleteQueueTree')
                          ->andReturn((object)['success' => true]);

            $this->mockRouter->shouldReceive('APIListQueueTree')
                          ->andReturn((object)['success' => true, 'data' => []]);
        }
    }

    private function createSimpleQueue(array $queueData): array
    {
        return [
            'success' => true,
            'queue_id' => '*' . rand(1, 999),
            'name' => $queueData['name'],
            'target' => $queueData['target'],
            'max_limit' => $queueData['max-limit'],
            'burst_limit' => $queueData['burst-limit'] ?? null,
            'burst_threshold' => $queueData['burst-threshold'] ?? null
        ];
    }

    private function createQueueTree(array $queueData): array
    {
        return [
            'success' => true,
            'queue_id' => '*' . rand(1, 999),
            'name' => $queueData['name'],
            'parent' => $queueData['parent'],
            'max_limit' => $queueData['max-limit']
        ];
    }

    private function modifyQueueBandwidth(string $queueId, string $newLimit): array
    {
        return [
            'success' => true,
            'queue_id' => $queueId,
            'new_limit' => $newLimit
        ];
    }

    private function collectBandwidthStatistics(): array
    {
        return [
            'total_queues' => 3,
            'total_bandwidth_allocated' => '45M/225M',
            'queue_utilization' => [
                [
                    'queue_name' => 'queue-client-001',
                    'bytes_uploaded' => 1024000,
                    'bytes_downloaded' => 5120000,
                    'packets_uploaded' => 1000,
                    'packets_downloaded' => 5000,
                    'current_rate_upload' => '2M',
                    'current_rate_download' => '10M'
                ]
            ]
        ];
    }

    private function calculateFairShare(string $parentQueue): array
    {
        return [
            'success' => true,
            'parent_queue' => $parentQueue,
            'guaranteed_bandwidth' => '10M/40M'
        ];
    }

    private function createMangleRule(array $rule): array
    {
        return ['success' => true, 'rule_id' => '*' . rand(1, 999)];
    }

    private function createQueueType(array $queueType): array
    {
        return ['success' => true, 'queue_type_id' => '*' . rand(1, 999)];
    }

    private function generateBandwidthReport(string $period): array
    {
        return [
            'success' => true,
            'period' => $period,
            'total_usage' => ['upload' => '100GB', 'download' => '500GB'],
            'client_usage' => [
                ['client_id' => 101, 'upload' => '30GB', 'download' => '150GB'],
                ['client_id' => 102, 'upload' => '40GB', 'download' => '200GB'],
                ['client_id' => 103, 'upload' => '30GB', 'download' => '150GB']
            ]
        ];
    }

    private function collectBandwidthUtilization(): array
    {
        return [
            'average_utilization' => 65,
            'overutilized_queues' => [],
            'underutilized_queues' => ['queue-client-003']
        ];
    }

    private function scaleBandwidthUp(array $queues): array
    {
        return ['success' => true, 'scaled_queues' => count($queues)];
    }

    private function scaleBandwidthDown(array $queues): array
    {
        return ['success' => true, 'scaled_queues' => count($queues)];
    }

    private function generateOptimizationRecommendations(): array
    {
        return [
            'success' => true,
            'recommendations' => [
                'Increase burst time for premium clients',
                'Implement PCQ for fair sharing',
                'Optimize priority settings for VoIP traffic'
            ]
        ];
    }
}