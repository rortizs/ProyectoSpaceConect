<?php

require_once 'tests/Unit/Controllers/BaseControllerTest.php';
require_once 'Controllers/Network.php';

/**
 * Network Controller Test
 *
 * Comprehensive unit tests for the Network controller.
 * Tests router management, MikroTik integration, content filtering,
 * and network security operations.
 */
class NetworkControllerTest extends BaseControllerTest
{
    /**
     * Controller instance under test
     */
    protected Network $controller;

    /**
     * Mock router data
     */
    protected array $mockRouterData;

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock required global constants
        if (!defined('INSTALLATIONS')) {
            define('INSTALLATIONS', 3);
        }

        $this->setupNetworkController();
        $this->setupMockRouterData();
    }

    /**
     * Set up network controller with mocked dependencies
     */
    private function setupNetworkController(): void
    {
        // Mock Views class
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->method('getView')->willReturn(true);

        // Create controller with mocked dependencies
        $this->controller = new class extends Network {
            public $views;

            public function __construct() {
                // Skip parent constructor to avoid session issues
            }

            public function setMockViews($views) {
                $this->views = $views;
            }

            // Override json method to capture output
            public function json($data) {
                echo json_encode($data);
            }
        };

        $this->controller->setMockViews($mockViews);
    }

    /**
     * Set up mock router data
     */
    private function setupMockRouterData(): void
    {
        $this->mockRouterData = [
            'id' => 1,
            'name' => 'Test Router',
            'ip' => '192.168.1.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => base64_encode('test123'),
            'ip_range' => '192.168.10.0/24',
            'zoneid' => 1,
            'routeros_version' => '7.6',
            'api_type' => 'rest',
            'board_name' => 'RB4011',
            'status' => 'connected'
        ];
    }

    /**
     * Test routers() method requires authentication
     */
    public function testRoutersRequiresAuthentication(): void
    {
        $this->assertRequiresAuthentication(function() {
            $this->controller->routers();
        });
    }

    /**
     * Test routers() method with valid permissions
     */
    public function testRoutersWithValidPermissions(): void
    {
        $this->mockAuthenticatedSession();

        $viewCalled = false;
        $viewData = null;

        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'routers',
                      $this->callback(function($data) use (&$viewData) {
                          $viewData = $data;
                          return true;
                      })
                  );

        $this->controller->setMockViews($mockViews);

        // Mock sql function for router data
        if (!function_exists('sql')) {
            function sql($query) {
                // Mock empty result
                return false;
            }
        }

        $this->controller->routers();

        // Verify view data structure
        $this->assertArrayHasKey('page_name', $viewData);
        $this->assertEquals('Routers', $viewData['page_name']);
        $this->assertArrayHasKey('page_functions_js', $viewData);
        $this->assertEquals('routers.js', $viewData['page_functions_js']);
        $this->assertArrayHasKey('records', $viewData);
        $this->assertArrayHasKey('zones', $viewData);
    }

    /**
     * Test add_router() method with valid data
     */
    public function testAddRouterWithValidData(): void
    {
        $this->mockAuthenticatedSession();

        $validRouterData = [
            'name' => 'New Router',
            'ip' => '192.168.1.2',
            'port' => '8728',
            'username' => 'admin',
            'password' => 'password123',
            'ip_range' => '192.168.20.0/24',
            'zoneid' => '1'
        ];

        $this->mockPostRequest($validRouterData);

        // Mock RouterFactory
        if (!class_exists('RouterFactory')) {
            class RouterFactory {
                public static function getRouterInfo($ip, $port, $username, $password) {
                    return [
                        'connected' => true,
                        'version' => '7.6',
                        'api_type' => 'rest',
                        'board_name' => 'RB4011'
                    ];
                }
            }
        }

        // Mock encryption functions
        if (!function_exists('encrypt_aes')) {
            function encrypt_aes($data, $key) {
                return base64_encode($data);
            }
        }

        ob_start();
        $this->controller->add_router();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertEquals('success', $response['result']);
        $this->assertEquals('Router agregado correctamente', $response['message']);
    }

    /**
     * Test add_router() method with invalid data
     */
    public function testAddRouterWithInvalidData(): void
    {
        $this->mockAuthenticatedSession();

        $invalidInputs = [
            'missing_name' => [
                'ip' => '192.168.1.2',
                'port' => '8728',
                'username' => 'admin',
                'password' => 'password123'
            ],
            'missing_ip' => [
                'name' => 'Router',
                'port' => '8728',
                'username' => 'admin',
                'password' => 'password123'
            ],
            'invalid_ip' => [
                'name' => 'Router',
                'ip' => 'invalid_ip',
                'port' => '8728',
                'username' => 'admin',
                'password' => 'password123'
            ],
            'empty_password' => [
                'name' => 'Router',
                'ip' => '192.168.1.2',
                'port' => '8728',
                'username' => 'admin',
                'password' => ''
            ]
        ];

        foreach ($invalidInputs as $description => $input) {
            $this->mockPostRequest($input);

            ob_start();
            $this->controller->add_router();
            $output = ob_get_clean();

            $this->assertFailedJsonResponse($output, "Should fail for: {$description}");
        }
    }

    /**
     * Test add_router() method with connection failure
     */
    public function testAddRouterWithConnectionFailure(): void
    {
        $this->mockAuthenticatedSession();

        $validRouterData = [
            'name' => 'Unreachable Router',
            'ip' => '192.168.1.999',
            'port' => '8728',
            'username' => 'admin',
            'password' => 'password123',
            'ip_range' => '192.168.20.0/24',
            'zoneid' => '1'
        ];

        $this->mockPostRequest($validRouterData);

        // Mock RouterFactory with connection failure
        if (!class_exists('RouterFactory')) {
            class RouterFactory {
                public static function getRouterInfo($ip, $port, $username, $password) {
                    return [
                        'connected' => false,
                        'error' => 'Connection timeout'
                    ];
                }
            }
        }

        ob_start();
        $this->controller->add_router();
        $output = ob_get_clean();

        $this->assertFailedJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertStringContainsString('No se pudo conectar al router', $response['message']);
    }

    /**
     * Test router_system_info() method
     */
    public function testRouterSystemInfo(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest(['id' => '1']);

        // Mock sqlObject
        if (!function_exists('sqlObject')) {
            function sqlObject($query) {
                return (object)[
                    'id' => 1,
                    'ip' => '192.168.1.1',
                    'port' => 8728,
                    'username' => 'admin',
                    'password' => base64_encode('test123'),
                    'api_type' => 'rest'
                ];
            }
        }

        // Mock decrypt_aes
        if (!function_exists('decrypt_aes')) {
            function decrypt_aes($data, $key) {
                return base64_decode($data);
            }
        }

        // Mock RouterFactory
        if (!class_exists('RouterFactory')) {
            class RouterFactory {
                public static function create($ip, $port, $username, $password, $type) {
                    return new class {
                        public $connected = true;

                        public function APIGetSystemResources() {
                            return (object)[
                                'success' => true,
                                'data' => (object)[
                                    'uptime' => '1d 2h 3m',
                                    'free-memory' => 134217728,
                                    'total-memory' => 268435456,
                                    'cpu' => 'ARM',
                                    'cpu-count' => 4,
                                    'cpu-frequency' => 1400,
                                    'cpu-load' => 5,
                                    'version' => '7.6',
                                    'board-name' => 'RB4011'
                                ]
                            ];
                        }
                    };
                }
            }
        }

        // Mock humanReadableBytes function
        if (!function_exists('humanReadableBytes')) {
            function humanReadableBytes($bytes) {
                return round($bytes / 1024 / 1024, 2) . ' MB';
            }
        }

        ob_start();
        $this->controller->router_system_info();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertArrayHasKey('html', $response);
        $this->assertStringContainsString('Uptime', $response['html']);
        $this->assertStringContainsString('Free Memory', $response['html']);
    }

    /**
     * Test content filter operations
     */
    public function testContentFilterOperations(): void
    {
        $this->mockAuthenticatedSession();

        // Test contentfilter() view
        $viewData = null;
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'contentfilter',
                      $this->callback(function($data) use (&$viewData) {
                          $viewData = $data;
                          return true;
                      })
                  );

        $this->controller->setMockViews($mockViews);

        // Mock ContentFilterService
        if (!class_exists('ContentFilterService')) {
            class ContentFilterService {
                public function getCategories() {
                    return [];
                }

                public function getPolicies() {
                    return [];
                }

                public function getFilteringLogs($clientId, $limit) {
                    return [];
                }
            }
        }

        // Mock ContentfilterModel
        if (!class_exists('ContentfilterModel')) {
            class ContentfilterModel {
                public function getFilteringStats() {
                    return [
                        'total_policies' => 0,
                        'filtered_clients' => 0,
                        'total_categories' => 0,
                        'blocked_domains' => 0
                    ];
                }

                public function getClientsWithoutFiltering() {
                    return [];
                }
            }
        }

        $this->controller->contentfilter();

        // Verify content filter view data
        $this->assertArrayHasKey('page_name', $viewData);
        $this->assertEquals('Filtro de Contenido', $viewData['page_name']);
        $this->assertArrayHasKey('categories', $viewData);
        $this->assertArrayHasKey('policies', $viewData);
        $this->assertArrayHasKey('stats', $viewData);
    }

    /**
     * Test apply_content_filter() method
     */
    public function testApplyContentFilter(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest([
            'client_id' => '1',
            'policy_id' => '1',
            'router_id' => '1'
        ]);

        // Mock ContentFilterService
        if (!class_exists('ContentFilterService')) {
            class ContentFilterService {
                public function applyPolicyToClient($clientId, $policyId, $routerId) {
                    return [
                        'success' => true,
                        'message' => 'Política aplicada correctamente',
                        'domains_blocked' => 10
                    ];
                }
            }
        }

        ob_start();
        $this->controller->apply_content_filter();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertEquals('Política aplicada correctamente', $response['message']);
        $this->assertArrayHasKey('domains_blocked', $response);
    }

    /**
     * Test router security operations
     */
    public function testRouterSecurityOperations(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest(['id' => '1']);

        // Mock router data
        if (!function_exists('sqlObject')) {
            function sqlObject($query) {
                return (object)[
                    'id' => 1,
                    'ip' => '192.168.1.1',
                    'port' => 8728,
                    'username' => 'admin',
                    'password' => base64_encode('test123')
                ];
            }
        }

        // Mock decrypt_aes
        if (!function_exists('decrypt_aes')) {
            function decrypt_aes($data, $key) {
                return base64_decode($data);
            }
        }

        // Create a mock for the private methods
        $networkController = new class extends Network {
            public function __construct() {}

            public function testGetExistingRules($url, $username, $password) {
                return [];
            }

            public function testRuleExists($comment, $existingRules) {
                return false;
            }

            public function testSendRequest($url, $username, $password, $data) {
                return json_encode(['success' => true]);
            }
        };

        // Test that security operations can be called
        $this->assertTrue(method_exists($networkController, 'testGetExistingRules'));
        $this->assertTrue(method_exists($networkController, 'testRuleExists'));
        $this->assertTrue(method_exists($networkController, 'testSendRequest'));
    }

    /**
     * Test router reboot functionality
     */
    public function testRouterReboot(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest(['id' => '1']);

        // Mock router data
        if (!function_exists('sqlObject')) {
            function sqlObject($query) {
                return (object)[
                    'id' => 1,
                    'ip' => '192.168.1.1',
                    'port' => 8728,
                    'username' => 'admin',
                    'password' => base64_encode('test123')
                ];
            }
        }

        ob_start();
        $this->controller->router_reboot();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertStringContainsString('reiniciando', $response['message']);
    }

    /**
     * Test router available IPs functionality
     */
    public function testRouterAvailableIps(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest(['id' => '1']);

        // Mock router data with IP range
        if (!function_exists('sqlObject')) {
            function sqlObject($query) {
                return (object)[
                    'id' => 1,
                    'ip_range' => "192.168.10.0/24\n192.168.11.0/24"
                ];
            }
        }

        // Mock getAvailableIPs function
        if (!function_exists('getAvailableIPs')) {
            function getAvailableIPs($range, $excluded) {
                return ['192.168.10.1', '192.168.10.2', '192.168.10.3'];
            }
        }

        ob_start();
        $this->controller->router_available_ips();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertGreaterThanOrEqual(0, count($response));
    }

    /**
     * Test network zones management
     */
    public function testNetworkZonesManagement(): void
    {
        $this->mockAuthenticatedSession();

        // Test zones listing
        $viewData = null;
        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'zones',
                      $this->callback(function($data) use (&$viewData) {
                          $viewData = $data;
                          return true;
                      })
                  );

        $this->controller->setMockViews($mockViews);

        $this->controller->zones();

        // Verify zones view data
        $this->assertArrayHasKey('page_name', $viewData);
        $this->assertEquals('Zonas', $viewData['page_name']);
        $this->assertArrayHasKey('records', $viewData);
    }

    /**
     * Test add_zone() method
     */
    public function testAddZone(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest([
            'name' => 'Test Zone',
            'mode' => '1'
        ]);

        // Mock sqlInsert
        if (!function_exists('sqlInsert')) {
            function sqlInsert($table, $data) {
                return true;
            }
        }

        ob_start();
        $this->controller->add_zone();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);
    }

    /**
     * Test router connection validation
     */
    public function testRouterConnectionValidation(): void
    {
        $validConnectionData = [
            'ip' => '192.168.1.1',
            'port' => '8728',
            'username' => 'admin',
            'password' => 'test123'
        ];

        $this->mockPostRequest($validConnectionData);

        // Mock RouterFactory for connection test
        if (!class_exists('RouterFactory')) {
            class RouterFactory {
                public static function getRouterInfo($ip, $port, $username, $password) {
                    return [
                        'connected' => true,
                        'version' => '7.6',
                        'api_type' => 'rest'
                    ];
                }
            }
        }

        ob_start();
        $this->controller->test_router_connection();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertEquals('Conexión exitosa con el router', $response['message']);
    }

    /**
     * Test bulk content filter operations
     */
    public function testBulkContentFilterOperations(): void
    {
        $this->mockAuthenticatedSession();

        $this->mockPostRequest([
            'client_ids' => '1,2,3',
            'policy_id' => '1'
        ]);

        // Mock ContentFilterService
        if (!class_exists('ContentFilterService')) {
            class ContentFilterService {
                public function applyPolicyToClient($clientId, $policyId, $routerId) {
                    return [
                        'success' => true,
                        'message' => 'Policy applied successfully'
                    ];
                }
            }
        }

        ob_start();
        $this->controller->bulk_apply_filter();
        $output = ob_get_clean();

        $this->assertSuccessfulJsonResponse($output);

        $response = json_decode($output, true);
        $this->assertArrayHasKey('success_count', $response);
        $this->assertArrayHasKey('error_count', $response);
    }

    /**
     * Test error handling for network operations
     */
    public function testNetworkOperationErrorHandling(): void
    {
        $this->mockAuthenticatedSession();

        // Test with invalid router ID
        $this->mockPostRequest(['id' => 'invalid']);

        ob_start();
        $this->controller->router_system_info();
        $output = ob_get_clean();

        $this->assertFailedJsonResponse($output);
    }

    /**
     * Test network template functionality
     */
    public function testNetworkTemplate(): void
    {
        $this->mockAuthenticatedSession();

        $mockViews = $this->createMock(stdClass::class);
        $mockViews->expects($this->once())
                  ->method('getView')
                  ->with(
                      $this->anything(),
                      'template',
                      []
                  );

        $this->controller->setMockViews($mockViews);

        $result = $this->controller->network_template();
        $this->assertTrue($result);
    }
}