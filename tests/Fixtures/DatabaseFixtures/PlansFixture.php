<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Plans Fixture
 *
 * Creates internet service plans with different speeds, prices, and types.
 * Includes residential, business, and premium plans with various configurations.
 */
class PlansFixture extends BaseFixture
{
    protected array $dependencies = ['EssentialDataFixture'];

    public function getName(): string
    {
        return 'Plans';
    }

    public function load(): array
    {
        $this->log('Loading internet service plans...');

        $data = [];

        // Create service categories if needed
        $data['categories'] = $this->createServiceCategories();

        // Create internet service plans
        $data['plans'] = $this->createServicePlans();

        $this->log('Plans loaded successfully');

        return $data;
    }

    /**
     * Create service categories
     */
    private function createServiceCategories(): array
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Internet Residencial',
                'description' => 'Planes de internet para uso doméstico',
                'icon' => 'fas fa-home',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Internet Empresarial',
                'description' => 'Planes de internet para empresas',
                'icon' => 'fas fa-building',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Internet Premium',
                'description' => 'Planes de alta velocidad y capacidad',
                'icon' => 'fas fa-crown',
                'status' => 1,
                'date_created' => date('Y-m-d H:i:s')
            ]
        ];

        $createdCategories = [];
        foreach ($categories as $category) {
            $id = $this->getOrCreate('service_categories', ['id' => $category['id']], $category);
            $createdCategories[] = array_merge($category, ['id' => $id]);
        }

        return $createdCategories;
    }

    /**
     * Create internet service plans
     */
    private function createServicePlans(): array
    {
        $plans = [
            // Residential Plans
            [
                'internal_code' => 'RES-BASIC-001',
                'service' => 'Internet Básico',
                'type' => 1, // Residential
                'rise' => 2,
                'rise_type' => 'MBPS',
                'descent' => 5,
                'descent_type' => 'MBPS',
                'price' => 35.00,
                'details' => json_encode([
                    'category' => 'residential',
                    'features' => ['Navegación web', 'Redes sociales', 'Email'],
                    'ideal_for' => 'Uso básico doméstico',
                    'max_devices' => 3,
                    'support' => '24/7',
                    'installation' => 'Gratis'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'RES-STD-002',
                'service' => 'Internet Estándar',
                'type' => 1,
                'rise' => 5,
                'rise_type' => 'MBPS',
                'descent' => 10,
                'descent_type' => 'MBPS',
                'price' => 50.00,
                'details' => json_encode([
                    'category' => 'residential',
                    'features' => ['Streaming HD', 'Gaming online', 'Video llamadas'],
                    'ideal_for' => 'Familia pequeña',
                    'max_devices' => 5,
                    'support' => '24/7',
                    'installation' => 'Gratis'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'RES-ADV-003',
                'service' => 'Internet Avanzado',
                'type' => 1,
                'rise' => 10,
                'rise_type' => 'MBPS',
                'descent' => 20,
                'descent_type' => 'MBPS',
                'price' => 75.00,
                'details' => json_encode([
                    'category' => 'residential',
                    'features' => ['Streaming 4K', 'Gaming profesional', 'Teletrabajo'],
                    'ideal_for' => 'Familia numerosa',
                    'max_devices' => 10,
                    'support' => '24/7',
                    'installation' => 'Gratis'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],

            // Business Plans
            [
                'internal_code' => 'BIZ-BASIC-004',
                'service' => 'Empresarial Básico',
                'type' => 2, // Business
                'rise' => 10,
                'rise_type' => 'MBPS',
                'descent' => 20,
                'descent_type' => 'MBPS',
                'price' => 120.00,
                'details' => json_encode([
                    'category' => 'business',
                    'features' => ['IP estática', 'Soporte prioritario', 'Backup automático'],
                    'ideal_for' => 'Pequeña empresa',
                    'max_devices' => 15,
                    'support' => '24/7 prioritario',
                    'installation' => 'Gratis',
                    'sla' => '99.5%'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'BIZ-STD-005',
                'service' => 'Empresarial Estándar',
                'type' => 2,
                'rise' => 20,
                'rise_type' => 'MBPS',
                'descent' => 40,
                'descent_type' => 'MBPS',
                'price' => 200.00,
                'details' => json_encode([
                    'category' => 'business',
                    'features' => ['IP estática', 'VPN incluida', 'Firewall empresarial'],
                    'ideal_for' => 'Mediana empresa',
                    'max_devices' => 30,
                    'support' => '24/7 prioritario',
                    'installation' => 'Gratis',
                    'sla' => '99.7%'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'BIZ-PRO-006',
                'service' => 'Empresarial Profesional',
                'type' => 2,
                'rise' => 50,
                'rise_type' => 'MBPS',
                'descent' => 100,
                'descent_type' => 'MBPS',
                'price' => 400.00,
                'details' => json_encode([
                    'category' => 'business',
                    'features' => ['IPs estáticas múltiples', 'Servidor dedicado', 'Monitoreo 24/7'],
                    'ideal_for' => 'Gran empresa',
                    'max_devices' => 100,
                    'support' => '24/7 dedicado',
                    'installation' => 'Gratis',
                    'sla' => '99.9%'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],

            // Premium Plans
            [
                'internal_code' => 'PREM-ULTRA-007',
                'service' => 'Ultra Premium',
                'type' => 3, // Premium
                'rise' => 100,
                'rise_type' => 'MBPS',
                'descent' => 200,
                'descent_type' => 'MBPS',
                'price' => 800.00,
                'details' => json_encode([
                    'category' => 'premium',
                    'features' => ['Fibra óptica dedicada', 'Latencia ultra baja', 'Bandwidth garantizado'],
                    'ideal_for' => 'Aplicaciones críticas',
                    'max_devices' => 'Ilimitado',
                    'support' => '24/7 ingeniero dedicado',
                    'installation' => 'Gratis + configuración avanzada',
                    'sla' => '99.95%'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'PREM-GIGA-008',
                'service' => 'Gigabit Premium',
                'type' => 3,
                'rise' => 500,
                'rise_type' => 'MBPS',
                'descent' => 1000,
                'descent_type' => 'MBPS',
                'price' => 1500.00,
                'details' => json_encode([
                    'category' => 'premium',
                    'features' => ['Conexión simétrica', 'Red redundante', 'CDN personalizado'],
                    'ideal_for' => 'Data centers, streaming profesional',
                    'max_devices' => 'Ilimitado',
                    'support' => '24/7 equipo especializado',
                    'installation' => 'Gratis + implementación personalizada',
                    'sla' => '99.99%'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],

            // Special/Promotional Plans
            [
                'internal_code' => 'PROMO-001',
                'service' => 'Plan Estudiante',
                'type' => 1,
                'rise' => 3,
                'rise_type' => 'MBPS',
                'descent' => 8,
                'descent_type' => 'MBPS',
                'price' => 25.00,
                'details' => json_encode([
                    'category' => 'promotional',
                    'features' => ['Precio especial estudiantes', 'Horario de alta velocidad nocturno'],
                    'ideal_for' => 'Estudiantes universitarios',
                    'max_devices' => 3,
                    'support' => '24/7',
                    'installation' => 'Gratis',
                    'conditions' => 'Válido con credencial estudiantil'
                ]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ],
            [
                'internal_code' => 'LEGACY-001',
                'service' => 'Plan Legacy (Descontinuado)',
                'type' => 1,
                'rise' => 1,
                'rise_type' => 'MBPS',
                'descent' => 2,
                'descent_type' => 'MBPS',
                'price' => 20.00,
                'details' => json_encode([
                    'category' => 'legacy',
                    'features' => ['Plan descontinuado', 'Solo para clientes existentes'],
                    'ideal_for' => 'Clientes antiguos',
                    'max_devices' => 2,
                    'support' => 'Limitado'
                ]),
                'routers' => '5',
                'registration_date' => date('Y-m-d H:i:s', strtotime('-2 years')),
                'state' => 0 // Inactive
            ]
        ];

        $createdPlans = [];
        foreach ($plans as $plan) {
            try {
                $id = $this->insert('services', $plan);
                $createdPlans[] = array_merge($plan, ['id' => $id]);
                $this->log("Created plan: {$plan['service']} (ID: {$id})");
            } catch (Exception $e) {
                $this->log("Failed to create plan {$plan['service']}: " . $e->getMessage());
            }
        }

        return $createdPlans;
    }

    /**
     * Get plans by type
     */
    public function getPlansByType(int $type): array
    {
        return array_filter($this->getCreatedData('services'), function($item) use ($type) {
            return $item['data']['type'] === $type;
        });
    }

    /**
     * Get residential plans
     */
    public function getResidentialPlans(): array
    {
        return $this->getPlansByType(1);
    }

    /**
     * Get business plans
     */
    public function getBusinessPlans(): array
    {
        return $this->getPlansByType(2);
    }

    /**
     * Get premium plans
     */
    public function getPremiumPlans(): array
    {
        return $this->getPlansByType(3);
    }

    /**
     * Get active plans only
     */
    public function getActivePlans(): array
    {
        return array_filter($this->getCreatedData('services'), function($item) {
            return $item['data']['state'] === 1;
        });
    }

    /**
     * Get plan by internal code
     */
    public function getPlanByCode(string $code): ?array
    {
        $plans = array_filter($this->getCreatedData('services'), function($item) use ($code) {
            return $item['data']['internal_code'] === $code;
        });

        return !empty($plans) ? array_values($plans)[0] : null;
    }

    /**
     * Create additional test scenarios
     */
    public function createTestScenarios(): array
    {
        $scenarios = [];

        // Create bulk plans for performance testing
        if (defined('CREATE_BULK_DATA') && CREATE_BULK_DATA) {
            $scenarios['bulk_plans'] = $this->createBulkPlans(50);
        }

        // Create invalid data for error testing
        $scenarios['invalid_plans'] = $this->createInvalidPlans();

        return $scenarios;
    }

    /**
     * Create bulk plans for performance testing
     */
    private function createBulkPlans(int $count): array
    {
        $bulkPlans = [];

        for ($i = 1; $i <= $count; $i++) {
            $speeds = [1, 2, 5, 10, 20, 50, 100];
            $downloadSpeed = $speeds[array_rand($speeds)];
            $uploadSpeed = $downloadSpeed / 2;

            $plan = [
                'internal_code' => "BULK-{$i:03d}",
                'service' => "Plan Bulk {$i}",
                'type' => rand(1, 3),
                'rise' => $uploadSpeed,
                'rise_type' => 'MBPS',
                'descent' => $downloadSpeed,
                'descent_type' => 'MBPS',
                'price' => $downloadSpeed * 5.50,
                'details' => json_encode(['bulk_test' => true, 'plan_number' => $i]),
                'routers' => '4,5',
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ];

            try {
                $id = $this->insert('services', $plan);
                $bulkPlans[] = array_merge($plan, ['id' => $id]);
            } catch (Exception $e) {
                $this->log("Failed to create bulk plan {$i}: " . $e->getMessage());
            }
        }

        return $bulkPlans;
    }

    /**
     * Create invalid plans for error testing
     */
    private function createInvalidPlans(): array
    {
        // These are intentionally problematic to test error handling
        $invalidPlans = [
            [
                'name' => 'Missing required fields',
                'data' => [
                    'service' => 'Incomplete Plan',
                    // Missing internal_code, type, etc.
                ],
                'expected_error' => 'Missing required fields'
            ],
            [
                'name' => 'Invalid price format',
                'data' => [
                    'internal_code' => 'INVALID-001',
                    'service' => 'Invalid Price Plan',
                    'type' => 1,
                    'rise' => 5,
                    'rise_type' => 'MBPS',
                    'descent' => 10,
                    'descent_type' => 'MBPS',
                    'price' => 'invalid_price',
                    'routers' => '4,5',
                    'registration_date' => date('Y-m-d H:i:s'),
                    'state' => 1
                ],
                'expected_error' => 'Invalid price format'
            ]
        ];

        // These won't be inserted, just documented for testing
        return $invalidPlans;
    }
}