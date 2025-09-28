<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Essential Data Fixture
 *
 * Creates essential system data required for the ISP Management System to function.
 * This includes system configurations, roles, permissions, and basic reference data.
 */
class EssentialDataFixture extends BaseFixture
{
    public function getName(): string
    {
        return 'Essential Data';
    }

    public function load(): array
    {
        $this->log('Loading essential system data...');

        $data = [];

        // Create system roles
        $data['roles'] = $this->createRoles();

        // Create business configuration
        $data['business'] = $this->createBusinessConfig();

        // Create network zones
        $data['zones'] = $this->createNetworkZones();

        // Create voucher types
        $data['vouchers'] = $this->createVoucherTypes();

        // Create voucher series
        $data['voucher_series'] = $this->createVoucherSeries();

        // Create system user
        $data['admin_user'] = $this->createAdminUser();

        // Create content filter categories
        $data['content_categories'] = $this->createContentFilterCategories();

        $this->log('Essential data loaded successfully');

        return $data;
    }

    /**
     * Create system roles
     */
    private function createRoles(): array
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'Administrador',
                'description' => 'Administrador del sistema',
                'permissions' => json_encode([
                    'dashboard' => ['r', 'w'],
                    'clients' => ['r', 'w', 'd'],
                    'bills' => ['r', 'w', 'd'],
                    'payments' => ['r', 'w'],
                    'network' => ['r', 'w'],
                    'settings' => ['r', 'w']
                ]),
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Técnico',
                'description' => 'Personal técnico',
                'permissions' => json_encode([
                    'dashboard' => ['r'],
                    'clients' => ['r', 'w'],
                    'network' => ['r', 'w'],
                    'installations' => ['r', 'w']
                ]),
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Cobranza',
                'description' => 'Personal de cobranza',
                'permissions' => json_encode([
                    'dashboard' => ['r'],
                    'clients' => ['r'],
                    'bills' => ['r'],
                    'payments' => ['r', 'w']
                ]),
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdRoles = [];
        foreach ($roles as $role) {
            if (!$this->exists('roles', ['id' => $role['id']])) {
                $this->insert('roles', $role);
                $createdRoles[] = $role;
            }
        }

        return $createdRoles;
    }

    /**
     * Create business configuration
     */
    private function createBusinessConfig(): array
    {
        $business = [
            'id' => 1,
            'name' => 'ISP Test Company',
            'ruc' => '20123456789',
            'address' => 'Av. Principal 123, Lima, Perú',
            'phone' => '+51 1 234-5678',
            'email' => 'info@isptest.com',
            'website' => 'https://isptest.com',
            'logo' => 'default_logo.png',
            'timezone' => 'America/Lima',
            'currency' => 'PEN',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'billing_day' => 1,
            'grace_period' => 5,
            'suspension_day' => 10,
            'status' => 1,
            'date_created' => date('Y-m-d H:i:s')
        ];

        if (!$this->exists('business', ['id' => 1])) {
            $this->insert('business', $business);
        }

        return $business;
    }

    /**
     * Create network zones
     */
    private function createNetworkZones(): array
    {
        $zones = [
            [
                'id' => 1,
                'name' => 'Zona Centro',
                'description' => 'Zona céntrica de la ciudad',
                'coordinates' => '-12.046374,-77.042793',
                'radius' => 2000,
                'color' => '#FF5733',
                'mode' => 1, // Simple Queues
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Zona Norte',
                'description' => 'Zona norte de la ciudad',
                'coordinates' => '-12.000000,-77.000000',
                'radius' => 3000,
                'color' => '#33FF57',
                'mode' => 2, // PPPoE
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Zona Sur',
                'description' => 'Zona sur de la ciudad',
                'coordinates' => '-12.100000,-77.100000',
                'radius' => 2500,
                'color' => '#3357FF',
                'mode' => 1, // Simple Queues
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdZones = [];
        foreach ($zones as $zone) {
            if (!$this->exists('network_zones', ['id' => $zone['id']])) {
                $this->insert('network_zones', $zone);
                $createdZones[] = $zone;
            }
        }

        return $createdZones;
    }

    /**
     * Create voucher types
     */
    private function createVoucherTypes(): array
    {
        $vouchers = [
            [
                'id' => 1,
                'voucher' => 'Factura',
                'description' => 'Factura electrónica',
                'prefix' => 'F',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'voucher' => 'Boleta',
                'description' => 'Boleta de venta',
                'prefix' => 'B',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'voucher' => 'Recibo',
                'description' => 'Recibo por servicios',
                'prefix' => 'R',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdVouchers = [];
        foreach ($vouchers as $voucher) {
            if (!$this->exists('vouchers', ['id' => $voucher['id']])) {
                $this->insert('vouchers', $voucher);
                $createdVouchers[] = $voucher;
            }
        }

        return $createdVouchers;
    }

    /**
     * Create voucher series
     */
    private function createVoucherSeries(): array
    {
        $series = [
            [
                'id' => 1,
                'voucherid' => 1,
                'serie' => '001',
                'correlative' => 1,
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'voucherid' => 2,
                'serie' => '001',
                'correlative' => 1,
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'voucherid' => 3,
                'serie' => '001',
                'correlative' => 1,
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdSeries = [];
        foreach ($series as $serie) {
            if (!$this->exists('voucher_series', ['id' => $serie['id']])) {
                $this->insert('voucher_series', $serie);
                $createdSeries[] = $serie;
            }
        }

        return $createdSeries;
    }

    /**
     * Create admin user
     */
    private function createAdminUser(): array
    {
        $adminUser = [
            'id' => 1,
            'name' => 'Administrador',
            'surname' => 'Sistema',
            'email' => 'admin@isptest.com',
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'document' => '12345678',
            'phone' => '+51999999999',
            'roleid' => 1,
            'status' => 1,
            'date_created' => date('Y-m-d H:i:s')
        ];

        if (!$this->exists('users', ['id' => 1])) {
            $this->insert('users', $adminUser);
        }

        return $adminUser;
    }

    /**
     * Create content filter categories
     */
    private function createContentFilterCategories(): array
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Social Media',
                'description' => 'Redes sociales (Facebook, Instagram, Twitter)',
                'is_active' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Streaming',
                'description' => 'Servicios de streaming (YouTube, Netflix, Spotify)',
                'is_active' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Gaming',
                'description' => 'Juegos online y plataformas de gaming',
                'is_active' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Adult Content',
                'description' => 'Contenido para adultos',
                'is_active' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'name' => 'Education',
                'description' => 'Sitios educativos y de aprendizaje',
                'is_active' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdCategories = [];
        foreach ($categories as $category) {
            if (!$this->exists('content_filter_categories', ['id' => $category['id']])) {
                $this->insert('content_filter_categories', $category);
                $createdCategories[] = $category;

                // Add some sample domains for each category
                $this->createSampleDomains($category['id'], $category['name']);
            }
        }

        return $createdCategories;
    }

    /**
     * Create sample domains for content filter categories
     */
    private function createSampleDomains(int $categoryId, string $categoryName): void
    {
        $domainMap = [
            'Social Media' => ['facebook.com', 'instagram.com', 'twitter.com', 'tiktok.com', 'snapchat.com'],
            'Streaming' => ['youtube.com', 'netflix.com', 'spotify.com', 'twitch.tv', 'amazon.com'],
            'Gaming' => ['steam.com', 'ea.com', 'blizzard.com', 'riotgames.com', 'epicgames.com'],
            'Adult Content' => ['adult-site1.com', 'adult-site2.com', 'adult-site3.com'],
            'Education' => ['coursera.org', 'edx.org', 'khanacademy.org', 'udemy.com', 'mit.edu']
        ];

        if (isset($domainMap[$categoryName])) {
            foreach ($domainMap[$categoryName] as $domain) {
                if (!$this->exists('content_filter_domains', ['domain' => $domain, 'category_id' => $categoryId])) {
                    $this->insert('content_filter_domains', [
                        'category_id' => $categoryId,
                        'domain' => $domain,
                        'is_active' => 1,
                        'date_created' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
}