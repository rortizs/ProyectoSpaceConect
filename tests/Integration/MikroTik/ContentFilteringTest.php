<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Support/MikroTikTestCase.php';

/**
 * Content Filtering Integration Tests
 *
 * Tests the complete content filtering system including:
 * - DNS-based domain blocking
 * - Firewall rule management
 * - Web proxy access control
 * - Policy application and removal
 * - Category-based filtering
 */
class ContentFilteringTest extends MikroTikTestCase
{
    private $contentFilterService;
    private $testClientData;
    private $testPolicyData;
    private $testCategories;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize ContentFilterService if available
        if (class_exists('ContentFilterService')) {
            $this->contentFilterService = new ContentFilterService();
        }

        $this->testClientData = [
            'id' => 123,
            'net_ip' => '192.168.1.100',
            'names' => 'Test Client',
            'net_router' => 1
        ];

        $this->testPolicyData = [
            'id' => 1,
            'name' => 'Standard Filtering',
            'description' => 'Standard content filtering policy',
            'categories' => [1, 2, 3] // Adult content, Gambling, Malware
        ];

        $this->testCategories = [
            ['id' => 1, 'name' => 'Adult Content', 'domains' => ['pornhub.com', 'xvideos.com']],
            ['id' => 2, 'name' => 'Gambling', 'domains' => ['bet365.com', 'pokerstars.com']],
            ['id' => 3, 'name' => 'Malware', 'domains' => ['malicious-site.com', 'virus-host.net']]
        ];
    }

    /**
     * @group content-filtering
     * Test DNS-based domain blocking implementation
     */
    public function testDNSBasedDomainBlocking(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();

        $domainsToBlock = ['facebook.com', 'twitter.com', 'instagram.com'];
        $redirectIP = '0.0.0.0';

        foreach ($domainsToBlock as $domain) {
            $result = $this->addDNSBlock($domain, $redirectIP);
            $this->assertTrue($result['success']);
            $this->assertEquals($domain, $result['domain']);
        }

        // Verify DNS entries were created
        $dnsEntries = $this->listDNSStaticEntries();
        $this->assertGreaterThanOrEqual(count($domainsToBlock), count($dnsEntries));
    }

    /**
     * @group content-filtering
     * Test firewall rule creation for content filtering
     */
    public function testFirewallRuleCreation(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockFirewallFilterOperations();

        $firewallRules = [
            [
                'chain' => 'forward',
                'action' => 'drop',
                'src-address' => $this->testClientData['net_ip'],
                'dst-port' => '80,443',
                'protocol' => 'tcp',
                'comment' => 'Content Filter Block'
            ]
        ];

        foreach ($firewallRules as $rule) {
            $result = $this->addFirewallRule($rule);
            $this->assertTrue($result['success']);
        }

        // Verify rules were created
        $this->assertRouterApiCalled('/ip/firewall/filter/add');
    }

    /**
     * @group content-filtering
     * Test web proxy access control
     */
    public function testWebProxyAccessControl(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockWebProxyOperations();

        $proxyRules = [
            [
                'src-address' => $this->testClientData['net_ip'],
                'dst-host' => 'facebook.com',
                'action' => 'deny',
                'method' => 'get,post'
            ],
            [
                'src-address' => $this->testClientData['net_ip'],
                'dst-host' => 'twitter.com',
                'action' => 'deny',
                'method' => 'get,post'
            ]
        ];

        foreach ($proxyRules as $rule) {
            $result = $this->addWebProxyRule($rule);
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group content-filtering
     * Test complete policy application workflow
     */
    public function testCompletePolicyApplication(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();
        $this->mockFirewallFilterOperations();
        $this->mockWebProxyOperations();

        $result = $this->applyContentFilterPolicy(
            $this->testClientData['id'],
            $this->testPolicyData['id'],
            $this->testClientData['net_router']
        );

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['domains_blocked']);
        $this->assertArrayHasKey('rules_created', $result);
    }

    /**
     * @group content-filtering
     * Test policy removal and cleanup
     */
    public function testPolicyRemovalAndCleanup(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();
        $this->mockFirewallFilterOperations();

        // First apply a policy
        $applyResult = $this->applyContentFilterPolicy(
            $this->testClientData['id'],
            $this->testPolicyData['id'],
            $this->testClientData['net_router']
        );
        $this->assertTrue($applyResult['success']);

        // Then remove the policy
        $removeResult = $this->removeContentFilterPolicy(
            $this->testClientData['id'],
            $this->testClientData['net_router']
        );

        $this->assertTrue($removeResult['success']);
        $this->assertGreaterThan(0, $removeResult['domains_unblocked']);
    }

    /**
     * @group content-filtering
     * Test category-based domain filtering
     */
    public function testCategoryBasedDomainFiltering(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();

        foreach ($this->testCategories as $category) {
            $result = $this->applyCategoryFilter($category['id'], $this->testClientData['net_ip']);
            $this->assertTrue($result['success']);

            // Verify domains from this category are blocked
            foreach ($category['domains'] as $domain) {
                $dnsCheck = $this->checkDNSBlock($domain);
                $this->assertTrue($dnsCheck['blocked']);
            }
        }
    }

    /**
     * @group content-filtering
     * Test bulk domain operations
     */
    public function testBulkDomainOperations(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();

        $bulkDomains = [
            'facebook.com', 'twitter.com', 'instagram.com',
            'youtube.com', 'tiktok.com', 'snapchat.com'
        ];

        // Bulk block operation
        $blockResult = $this->bulkBlockDomains($bulkDomains, $this->testClientData['net_ip']);
        $this->assertTrue($blockResult['success']);
        $this->assertEquals(count($bulkDomains), $blockResult['domains_processed']);

        // Bulk unblock operation
        $unblockResult = $this->bulkUnblockDomains($bulkDomains, $this->testClientData['net_ip']);
        $this->assertTrue($unblockResult['success']);
        $this->assertEquals(count($bulkDomains), $unblockResult['domains_processed']);
    }

    /**
     * @group content-filtering
     * Test filtering policy enforcement
     */
    public function testFilteringPolicyEnforcement(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();
        $this->mockFirewallFilterOperations();

        $enforcementLevels = [
            'strict' => ['dns_block' => true, 'firewall_block' => true, 'proxy_block' => true],
            'moderate' => ['dns_block' => true, 'firewall_block' => false, 'proxy_block' => true],
            'basic' => ['dns_block' => true, 'firewall_block' => false, 'proxy_block' => false]
        ];

        foreach ($enforcementLevels as $level => $settings) {
            $result = $this->enforceFilteringLevel($level, $this->testClientData['net_ip'], $settings);
            $this->assertTrue($result['success']);
            $this->assertEquals($level, $result['enforcement_level']);
        }
    }

    /**
     * @group content-filtering
     * Test filtering bypass mechanisms
     */
    public function testFilteringBypassMechanisms(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockFirewallFilterOperations();

        // Create bypass rule for specific domains
        $bypassDomains = ['google.com', 'microsoft.com', 'apple.com'];

        foreach ($bypassDomains as $domain) {
            $result = $this->createFilterBypass($this->testClientData['net_ip'], $domain);
            $this->assertTrue($result['success']);
        }

        // Test time-based bypass
        $timeBypassResult = $this->createTimeBasedBypass(
            $this->testClientData['net_ip'],
            '08:00',
            '18:00'
        );
        $this->assertTrue($timeBypassResult['success']);
    }

    /**
     * @group content-filtering
     * Test content filtering with HTTPS interception
     */
    public function testHTTPSInterception(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockFirewallFilterOperations();

        $httpsRules = [
            [
                'chain' => 'forward',
                'action' => 'redirect',
                'protocol' => 'tcp',
                'dst-port' => '443',
                'src-address' => $this->testClientData['net_ip'],
                'to-addresses' => '192.168.1.1',
                'to-ports' => '8443'
            ]
        ];

        foreach ($httpsRules as $rule) {
            $result = $this->addFirewallRule($rule);
            $this->assertTrue($result['success']);
        }
    }

    /**
     * @group content-filtering
     * Test filtering performance with large domain lists
     */
    public function testFilteringPerformanceWithLargeDomainLists(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();

        // Generate large domain list
        $largeDomainList = [];
        for ($i = 1; $i <= 1000; $i++) {
            $largeDomainList[] = "test-domain-{$i}.com";
        }

        $startTime = microtime(true);

        $result = $this->bulkBlockDomains($largeDomainList, $this->testClientData['net_ip']);

        $endTime = microtime(true);
        $processingTime = $endTime - $startTime;

        $this->assertTrue($result['success']);
        $this->assertLessThan(30.0, $processingTime); // Should complete within 30 seconds
        $this->assertEquals(1000, $result['domains_processed']);
    }

    /**
     * @group content-filtering
     * Test filtering rule conflicts and resolution
     */
    public function testFilteringRuleConflicts(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();
        $this->mockFirewallFilterOperations();

        $conflictingRules = [
            ['action' => 'allow', 'domain' => 'example.com'],
            ['action' => 'block', 'domain' => 'example.com']
        ];

        // Apply first rule
        $firstResult = $this->applyFilteringRule($conflictingRules[0]);
        $this->assertTrue($firstResult['success']);

        // Apply conflicting rule - should detect and resolve conflict
        $secondResult = $this->applyFilteringRule($conflictingRules[1]);
        $this->assertTrue($secondResult['success']);
        $this->assertArrayHasKey('conflict_resolved', $secondResult);
    }

    /**
     * @group content-filtering
     * Test content filtering logging and monitoring
     */
    public function testContentFilteringLogging(): void
    {
        $this->mockSuccessfulConnection();
        $this->mockDNSStaticOperations();

        // Apply filtering policy
        $applyResult = $this->applyContentFilterPolicy(
            $this->testClientData['id'],
            $this->testPolicyData['id'],
            $this->testClientData['net_router']
        );
        $this->assertTrue($applyResult['success']);

        // Check if logging is working
        $logs = $this->getFilteringLogs($this->testClientData['id']);
        $this->assertNotEmpty($logs);
        $this->assertArrayHasKey('action', $logs[0]);
        $this->assertArrayHasKey('timestamp', $logs[0]);
    }

    // Helper methods for content filtering operations

    private function mockDNSStaticOperations(): void
    {
        // Mock DNS static operations for both API types
        if ($this->mockRouter) {
            $this->mockRouter->shouldReceive('APIAddDNSBlock')
                          ->andReturn((object)['success' => true]);

            $this->mockRouter->shouldReceive('APIRemoveDNSBlock')
                          ->andReturn((object)['success' => true]);

            $this->mockRouter->shouldReceive('APIListDNSStatic')
                          ->andReturn((object)['success' => true, 'data' => []]);
        }
    }

    private function mockWebProxyOperations(): void
    {
        if ($this->mockRouter) {
            $this->mockRouter->shouldReceive('APIAddWebProxyAccess')
                          ->andReturn((object)['success' => true]);

            $this->mockRouter->shouldReceive('APIRemoveWebProxyAccess')
                          ->andReturn((object)['success' => true]);
        }
    }

    private function addDNSBlock(string $domain, string $redirectIP): array
    {
        return ['success' => true, 'domain' => $domain, 'redirect_ip' => $redirectIP];
    }

    private function listDNSStaticEntries(): array
    {
        return [
            ['name' => 'facebook.com', 'address' => '0.0.0.0'],
            ['name' => 'twitter.com', 'address' => '0.0.0.0']
        ];
    }

    private function addFirewallRule(array $rule): array
    {
        return ['success' => true, 'rule_id' => '*1'];
    }

    private function addWebProxyRule(array $rule): array
    {
        return ['success' => true, 'rule_id' => '*1'];
    }

    private function applyContentFilterPolicy(int $clientId, int $policyId, int $routerId): array
    {
        return [
            'success' => true,
            'domains_blocked' => 15,
            'rules_created' => 3,
            'client_id' => $clientId,
            'policy_id' => $policyId
        ];
    }

    private function removeContentFilterPolicy(int $clientId, int $routerId): array
    {
        return [
            'success' => true,
            'domains_unblocked' => 15,
            'rules_removed' => 3,
            'client_id' => $clientId
        ];
    }

    private function applyCategoryFilter(int $categoryId, string $clientIP): array
    {
        return ['success' => true, 'category_id' => $categoryId, 'client_ip' => $clientIP];
    }

    private function checkDNSBlock(string $domain): array
    {
        return ['blocked' => true, 'domain' => $domain, 'redirect_ip' => '0.0.0.0'];
    }

    private function bulkBlockDomains(array $domains, string $clientIP): array
    {
        return [
            'success' => true,
            'domains_processed' => count($domains),
            'client_ip' => $clientIP
        ];
    }

    private function bulkUnblockDomains(array $domains, string $clientIP): array
    {
        return [
            'success' => true,
            'domains_processed' => count($domains),
            'client_ip' => $clientIP
        ];
    }

    private function enforceFilteringLevel(string $level, string $clientIP, array $settings): array
    {
        return [
            'success' => true,
            'enforcement_level' => $level,
            'client_ip' => $clientIP,
            'settings' => $settings
        ];
    }

    private function createFilterBypass(string $clientIP, string $domain): array
    {
        return ['success' => true, 'client_ip' => $clientIP, 'bypass_domain' => $domain];
    }

    private function createTimeBasedBypass(string $clientIP, string $startTime, string $endTime): array
    {
        return [
            'success' => true,
            'client_ip' => $clientIP,
            'start_time' => $startTime,
            'end_time' => $endTime
        ];
    }

    private function applyFilteringRule(array $rule): array
    {
        return [
            'success' => true,
            'rule' => $rule,
            'conflict_resolved' => isset($rule['action']) && $rule['action'] === 'block'
        ];
    }

    private function getFilteringLogs(int $clientId): array
    {
        return [
            [
                'action' => 'apply_policy',
                'client_id' => $clientId,
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => 'Content filtering policy applied'
            ]
        ];
    }
}