<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Router Fixture
 *
 * Creates network routers with different configurations, zones, and connection types.
 * Includes MikroTik routers with various network setups and performance characteristics.
 */
class RouterFixture extends BaseFixture
{
    protected array $dependencies = ['EssentialDataFixture'];

    public function getName(): string
    {
        return 'Routers';
    }

    public function load(): array
    {
        $this->log('Loading network routers...');

        $data = [];

        // Validate required tables exist
        $this->validateTables(['network_routers', 'network_zones']);

        // Create network routers
        $data['routers'] = $this->createNetworkRouters();

        // Create additional network infrastructure
        $data['access_points'] = $this->createAccessPoints();

        $this->log('Routers loaded successfully');

        return $data;
    }

    /**
     * Create network routers
     */
    private function createNetworkRouters(): array
    {
        $routers = [
            // Production Routers
            [
                'name' => 'Router Principal Centro',
                'ip' => '192.168.1.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('RouterMain2024!'),
                'ip_range' => '192.168.100.0/24',
                'zoneid' => 1, // Zona Centro
                'identity' => 'RouterOS CCR1009-7G-1C-1S+',
                'board_name' => 'CCR1009-7G-1C-1S+',
                'version' => '7.13.5',
                'status' => 'online'
            ],
            [
                'name' => 'Router Norte Sectorial',
                'ip' => '192.168.2.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('RouterNorth2024!'),
                'ip_range' => '192.168.200.0/24',
                'zoneid' => 2, // Zona Norte
                'identity' => 'RouterOS hAP ac²',
                'board_name' => 'hAP ac²',
                'version' => '7.13.5',
                'status' => 'online'
            ],
            [
                'name' => 'Router Sur Distribucion',
                'ip' => '192.168.3.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('RouterSouth2024!'),
                'ip_range' => '192.168.300.0/24',
                'zoneid' => 3, // Zona Sur
                'identity' => 'RouterOS CRS328-24P-4S+',
                'board_name' => 'CRS328-24P-4S+',
                'version' => '7.13.5',
                'status' => 'online'
            ],

            // Edge Routers
            [
                'name' => 'Edge Router A',
                'ip' => '10.0.1.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('EdgeRouter2024!'),
                'ip_range' => '10.1.0.0/16',
                'zoneid' => 1,
                'identity' => 'RouterOS CCR2004-16G-2S+',
                'board_name' => 'CCR2004-16G-2S+',
                'version' => '7.13.5',
                'status' => 'online'
            ],
            [
                'name' => 'Edge Router B',
                'ip' => '10.0.2.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('EdgeRouter2024!'),
                'ip_range' => '10.2.0.0/16',
                'zoneid' => 2,
                'identity' => 'RouterOS CCR2004-16G-2S+',
                'board_name' => 'CCR2004-16G-2S+',
                'version' => '7.13.5',
                'status' => 'online'
            ],

            // Access Routers
            [
                'name' => 'Access Point Centro 01',
                'ip' => '192.168.1.10',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('AccessPoint2024!'),
                'ip_range' => '192.168.110.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS SXT LTE6',
                'board_name' => 'SXT LTE6',
                'version' => '7.13.5',
                'status' => 'online'
            ],
            [
                'name' => 'Access Point Centro 02',
                'ip' => '192.168.1.11',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('AccessPoint2024!'),
                'ip_range' => '192.168.111.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS SXT LTE6',
                'board_name' => 'SXT LTE6',
                'version' => '7.13.5',
                'status' => 'online'
            ],

            // Test/Development Routers
            [
                'name' => 'Router Test Lab',
                'ip' => '172.16.1.1',
                'port' => 8728,
                'username' => 'testadmin',
                'password' => $this->encryptPassword('TestRouter2024!'),
                'ip_range' => '172.16.10.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS CHR',
                'board_name' => 'CHR',
                'version' => '7.13.5',
                'status' => 'testing'
            ],
            [
                'name' => 'Router Desarrollo',
                'ip' => '172.16.2.1',
                'port' => 8728,
                'username' => 'devadmin',
                'password' => $this->encryptPassword('DevRouter2024!'),
                'ip_range' => '172.16.20.0/24',
                'zoneid' => 2,
                'identity' => 'RouterOS CHR',
                'board_name' => 'CHR',
                'version' => '7.14-rc1',
                'status' => 'development'
            ],

            // Backup/Redundant Routers
            [
                'name' => 'Router Backup Centro',
                'ip' => '192.168.1.2',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('BackupRouter2024!'),
                'ip_range' => '192.168.101.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS hAP ac³',
                'board_name' => 'hAP ac³',
                'version' => '7.13.5',
                'status' => 'standby'
            ],

            // Problem Routers (for testing error scenarios)
            [
                'name' => 'Router Mantenimiento',
                'ip' => '192.168.99.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('MaintenanceRouter2024!'),
                'ip_range' => '192.168.990.0/24',
                'zoneid' => 3,
                'identity' => 'RouterOS RB750Gr3',
                'board_name' => 'RB750Gr3',
                'version' => '6.49.10',
                'status' => 'maintenance'
            ],
            [
                'name' => 'Router Offline',
                'ip' => '192.168.99.2',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('OfflineRouter2024!'),
                'ip_range' => '192.168.991.0/24',
                'zoneid' => 3,
                'identity' => 'RouterOS RB750Gr3',
                'board_name' => 'RB750Gr3',
                'version' => '6.49.10',
                'status' => 'offline'
            ],

            // High-capacity Routers
            [
                'name' => 'Core Router Principal',
                'ip' => '10.0.0.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('CoreRouter2024!'),
                'ip_range' => '10.0.0.0/8',
                'zoneid' => 1,
                'identity' => 'RouterOS CCR2116-12G-4S+',
                'board_name' => 'CCR2116-12G-4S+',
                'version' => '7.13.5',
                'status' => 'online'
            ],

            // Remote/Branch Routers
            [
                'name' => 'Router Sucursal Este',
                'ip' => '203.0.113.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('BranchEast2024!'),
                'ip_range' => '203.0.113.0/24',
                'zoneid' => 2,
                'identity' => 'RouterOS hEX S',
                'board_name' => 'hEX S',
                'version' => '7.13.5',
                'status' => 'online'
            ],
            [
                'name' => 'Router Sucursal Oeste',
                'ip' => '203.0.114.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword('BranchWest2024!'),
                'ip_range' => '203.0.114.0/24',
                'zoneid' => 3,
                'identity' => 'RouterOS hEX S',
                'board_name' => 'hEX S',
                'version' => '7.13.5',
                'status' => 'online'
            ]
        ];

        $createdRouters = [];
        foreach ($routers as $router) {
            try {
                $id = $this->insert('network_routers', $router);
                $createdRouters[] = array_merge($router, ['id' => $id]);
                $this->log("Created router: {$router['name']} (ID: {$id})");
            } catch (Exception $e) {
                $this->log("Failed to create router {$router['name']}: " . $e->getMessage());
            }
        }

        return $createdRouters;
    }

    /**
     * Create access points
     */
    private function createAccessPoints(): array
    {
        $accessPoints = [
            [
                'nombre' => 'AP Sectorial Norte A',
                'ip' => '192.168.2.100',
                'version' => '7.13.5'
            ],
            [
                'nombre' => 'AP Sectorial Norte B',
                'ip' => '192.168.2.101',
                'version' => '7.13.5'
            ],
            [
                'nombre' => 'AP Centro Comercial',
                'ip' => '192.168.1.100',
                'version' => '7.13.5'
            ],
            [
                'nombre' => 'AP Residencial Sur',
                'ip' => '192.168.3.100',
                'version' => '7.13.5'
            ],
            [
                'nombre' => 'AP Industrial Este',
                'ip' => '192.168.4.100',
                'version' => '7.13.5'
            ]
        ];

        $createdAPs = [];
        foreach ($accessPoints as $ap) {
            try {
                $id = $this->insert('ap_clientes', $ap);
                $createdAPs[] = array_merge($ap, ['id' => $id]);
                $this->log("Created access point: {$ap['nombre']} (ID: {$id})");
            } catch (Exception $e) {
                $this->log("Failed to create access point {$ap['nombre']}: " . $e->getMessage());
            }
        }

        return $createdAPs;
    }

    /**
     * Encrypt password for router storage
     */
    private function encryptPassword(string $password): string
    {
        // Simple base64 encoding for demo purposes
        // In production, use proper encryption
        return base64_encode($password);
    }

    /**
     * Get routers by zone
     */
    public function getRoutersByZone(int $zoneId): array
    {
        return array_filter($this->getCreatedData('network_routers'), function($item) use ($zoneId) {
            return $item['data']['zoneid'] === $zoneId;
        });
    }

    /**
     * Get routers by status
     */
    public function getRoutersByStatus(string $status): array
    {
        return array_filter($this->getCreatedData('network_routers'), function($item) use ($status) {
            return $item['data']['status'] === $status;
        });
    }

    /**
     * Get online routers only
     */
    public function getOnlineRouters(): array
    {
        return $this->getRoutersByStatus('online');
    }

    /**
     * Get routers by board type
     */
    public function getRoutersByBoard(string $boardName): array
    {
        return array_filter($this->getCreatedData('network_routers'), function($item) use ($boardName) {
            return stripos($item['data']['board_name'], $boardName) !== false;
        });
    }

    /**
     * Get router statistics
     */
    public function getRouterStats(): array
    {
        $routers = $this->getCreatedData('network_routers');

        $stats = [
            'total' => count($routers),
            'by_status' => [],
            'by_zone' => [],
            'by_board' => []
        ];

        foreach ($routers as $router) {
            $data = $router['data'];

            // By status
            $status = $data['status'];
            $stats['by_status'][$status] = ($stats['by_status'][$status] ?? 0) + 1;

            // By zone
            $zone = $data['zoneid'];
            $stats['by_zone'][$zone] = ($stats['by_zone'][$zone] ?? 0) + 1;

            // By board
            $board = $data['board_name'];
            $stats['by_board'][$board] = ($stats['by_board'][$board] ?? 0) + 1;
        }

        return $stats;
    }

    /**
     * Create test scenarios
     */
    public function createTestScenarios(): array
    {
        $scenarios = [];

        // Create bulk routers for performance testing
        if (defined('CREATE_BULK_DATA') && CREATE_BULK_DATA) {
            $scenarios['bulk_routers'] = $this->createBulkRouters(20);
        }

        // Create connection test scenarios
        $scenarios['connection_tests'] = $this->createConnectionTestRouters();

        return $scenarios;
    }

    /**
     * Create bulk routers for performance testing
     */
    private function createBulkRouters(int $count): array
    {
        $bulkRouters = [];
        $boards = ['hAP ac²', 'hEX S', 'RB750Gr3', 'CRS328-24P-4S+'];
        $statuses = ['online', 'offline', 'maintenance'];

        for ($i = 1; $i <= $count; $i++) {
            $router = [
                'name' => "Bulk Router {$i:03d}",
                'ip' => "172.20.{$i}.1",
                'port' => 8728,
                'username' => 'admin',
                'password' => $this->encryptPassword("BulkRouter{$i}!"),
                'ip_range' => "172.20.{$i}.0/24",
                'zoneid' => (($i - 1) % 3) + 1,
                'identity' => 'RouterOS ' . $boards[array_rand($boards)],
                'board_name' => $boards[array_rand($boards)],
                'version' => '7.13.5',
                'status' => $statuses[array_rand($statuses)]
            ];

            try {
                $id = $this->insert('network_routers', $router);
                $bulkRouters[] = array_merge($router, ['id' => $id]);
            } catch (Exception $e) {
                $this->log("Failed to create bulk router {$i}: " . $e->getMessage());
            }
        }

        return $bulkRouters;
    }

    /**
     * Create routers for connection testing
     */
    private function createConnectionTestRouters(): array
    {
        $testRouters = [
            [
                'name' => 'Test Connection Valid',
                'ip' => '127.0.0.1',
                'port' => 8728,
                'username' => 'test',
                'password' => $this->encryptPassword('test123'),
                'ip_range' => '192.168.99.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS CHR',
                'board_name' => 'CHR',
                'version' => '7.13.5',
                'status' => 'testing'
            ],
            [
                'name' => 'Test Connection Invalid IP',
                'ip' => '999.999.999.999',
                'port' => 8728,
                'username' => 'test',
                'password' => $this->encryptPassword('test123'),
                'ip_range' => '192.168.98.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS CHR',
                'board_name' => 'CHR',
                'version' => '7.13.5',
                'status' => 'error'
            ],
            [
                'name' => 'Test Connection Wrong Port',
                'ip' => '192.168.1.1',
                'port' => 9999,
                'username' => 'test',
                'password' => $this->encryptPassword('test123'),
                'ip_range' => '192.168.97.0/24',
                'zoneid' => 1,
                'identity' => 'RouterOS CHR',
                'board_name' => 'CHR',
                'version' => '7.13.5',
                'status' => 'error'
            ]
        ];

        $createdTestRouters = [];
        foreach ($testRouters as $router) {
            try {
                $id = $this->insert('network_routers', $router);
                $createdTestRouters[] = array_merge($router, ['id' => $id]);
            } catch (Exception $e) {
                $this->log("Failed to create test router {$router['name']}: " . $e->getMessage());
            }
        }

        return $createdTestRouters;
    }
}