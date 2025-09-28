<?php

/**
 * Creates Test Data Trait
 *
 * Provides methods for creating test data entities consistently across tests.
 */
trait CreatesTestData
{
    /**
     * Create test customer data
     */
    protected function createCustomerData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test Customer ' . $this->randomString(5),
            'documento' => (string)rand(10000000, 99999999),
            'telefono' => $this->randomPhone(),
            'email' => $this->randomEmail(),
            'direccion' => 'Av. Test ' . rand(100, 999),
            'distrito' => 'Test District',
            'provincia' => 'Test Province',
            'departamento' => 'Test Department',
            'referencia' => 'Test Reference',
            'coordenadas' => $this->randomCoordinates(),
            'fecha_registro' => date('Y-m-d H:i:s'),
            'estado' => 1,
            'idzona' => 1
        ], $overrides);
    }

    /**
     * Create test plan data
     */
    protected function createPlanData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test Plan ' . $this->randomString(5),
            'descripcion' => 'Test internet plan',
            'precio' => rand(50, 200),
            'subida' => rand(1, 10) . 'M',
            'bajada' => rand(5, 50) . 'M',
            'moneda' => 'PEN',
            'tipo' => 'residential',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test contract data
     */
    protected function createContractData(int $customerId, int $planId, array $overrides = []): array
    {
        return array_merge([
            'idcliente' => $customerId,
            'idplan' => $planId,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_corte' => date('Y-m-d', strtotime('+1 month')),
            'dia_corte' => rand(1, 28),
            'precio_personalizado' => null,
            'observaciones' => 'Test contract',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test bill data
     */
    protected function createBillData(int $contractId, array $overrides = []): array
    {
        return array_merge([
            'idcontrato' => $contractId,
            'numero_factura' => 'F' . date('Ym') . rand(1000, 9999),
            'monto' => rand(50, 200),
            'fecha_emision' => date('Y-m-d'),
            'fecha_vencimiento' => date('Y-m-d', strtotime('+15 days')),
            'moneda' => 'PEN',
            'estado' => 'pending',
            'tipo' => 'monthly',
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test payment data
     */
    protected function createPaymentData(int $billId, array $overrides = []): array
    {
        return array_merge([
            'idfactura' => $billId,
            'monto' => rand(50, 200),
            'metodo_pago' => 'cash',
            'numero_transaccion' => 'TXN' . rand(100000, 999999),
            'fecha_pago' => date('Y-m-d H:i:s'),
            'observaciones' => 'Test payment',
            'estado' => 'completed',
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test router data
     */
    protected function createRouterData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test Router ' . $this->randomString(5),
            'host' => $this->randomIpAddress(),
            'port' => 8728,
            'user' => 'admin',
            'password' => 'test123',
            'version' => '6.48.1',
            'board' => 'RB951G-2HnD',
            'descripcion' => 'Test router for unit testing',
            'ubicacion' => 'Test Location',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test zone data
     */
    protected function createZoneData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test Zone ' . $this->randomString(5),
            'descripcion' => 'Test zone for unit testing',
            'coordenadas' => $this->randomCoordinates(),
            'radio' => rand(500, 2000),
            'color' => '#' . substr(md5(rand()), 0, 6),
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test user data
     */
    protected function createUserData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test User ' . $this->randomString(5),
            'email' => $this->randomEmail(),
            'usuario' => 'testuser' . $this->randomString(5),
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'documento' => (string)rand(10000000, 99999999),
            'telefono' => $this->randomPhone(),
            'rol' => 1,
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test installation data
     */
    protected function createInstallationData(int $contractId, array $overrides = []): array
    {
        return array_merge([
            'idcontrato' => $contractId,
            'fecha_programada' => date('Y-m-d', strtotime('+3 days')),
            'fecha_instalacion' => null,
            'tecnico_asignado' => 1,
            'observaciones' => 'Test installation',
            'materiales' => json_encode([
                'cable' => '100m',
                'conectores' => '2',
                'router' => '1'
            ]),
            'coordenadas' => $this->randomCoordinates(),
            'estado' => 'scheduled',
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test incident data
     */
    protected function createIncidentData(int $customerId, array $overrides = []): array
    {
        return array_merge([
            'idcliente' => $customerId,
            'titulo' => 'Test Incident ' . $this->randomString(5),
            'descripcion' => 'Test incident description',
            'tipo' => 'technical',
            'prioridad' => 'medium',
            'tecnico_asignado' => 1,
            'fecha_reporte' => date('Y-m-d H:i:s'),
            'fecha_solucion' => null,
            'solucion' => null,
            'estado' => 'open',
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test product data
     */
    protected function createProductData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Test Product ' . $this->randomString(5),
            'descripcion' => 'Test product description',
            'precio' => rand(10, 100),
            'stock' => rand(10, 100),
            'categoria' => 'equipment',
            'codigo' => 'PROD' . rand(1000, 9999),
            'proveedor' => 'Test Supplier',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create test voucher data
     */
    protected function createVoucherData(array $overrides = []): array
    {
        return array_merge([
            'codigo' => 'VOU' . $this->randomString(8),
            'tipo' => 'discount',
            'valor' => rand(10, 50),
            'descripcion' => 'Test voucher',
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
            'usos_maximos' => rand(10, 100),
            'usos_actuales' => 0,
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    /**
     * Create network configuration data
     */
    protected function createNetworkConfigData(array $overrides = []): array
    {
        return array_merge([
            'client_ip' => $this->randomIpAddress(),
            'pppoe_user' => 'testuser' . $this->randomString(5),
            'pppoe_password' => $this->randomString(8),
            'profile' => 'default',
            'queue_name' => 'queue-' . $this->randomString(5),
            'target_address' => $this->randomIpAddress() . '/32',
            'max_limit_up' => rand(1, 10) . 'M',
            'max_limit_down' => rand(5, 50) . 'M',
            'burst_limit_up' => rand(2, 20) . 'M',
            'burst_limit_down' => rand(10, 100) . 'M',
            'burst_threshold_up' => '80%',
            'burst_threshold_down' => '80%',
            'burst_time' => '30s'
        ], $overrides);
    }

    /**
     * Generate random coordinates (Peru-based)
     */
    protected function randomCoordinates(): string
    {
        $lat = -12.0 - (rand(0, 1000) / 1000) * 6; // Peru latitude range
        $lng = -77.0 - (rand(0, 1000) / 1000) * 10; // Peru longitude range
        return round($lat, 6) . ',' . round($lng, 6);
    }

    /**
     * Create batch of test customers
     */
    protected function createTestCustomers(int $count = 5): array
    {
        $customers = [];
        for ($i = 0; $i < $count; $i++) {
            $customers[] = $this->createCustomerData();
        }
        return $customers;
    }

    /**
     * Create batch of test plans
     */
    protected function createTestPlans(int $count = 3): array
    {
        $plans = [];
        $speeds = ['5M', '10M', '20M', '50M', '100M'];
        $prices = [50, 75, 100, 150, 200];

        for ($i = 0; $i < $count; $i++) {
            $plans[] = $this->createPlanData([
                'bajada' => $speeds[$i % count($speeds)],
                'precio' => $prices[$i % count($prices)]
            ]);
        }
        return $plans;
    }

    /**
     * Create complete customer with contract and bill
     */
    protected function createCustomerWithContract(array $customerOverrides = [], array $planOverrides = [], array $contractOverrides = []): array
    {
        // Create customer
        $customerData = $this->createCustomerData($customerOverrides);
        $customerId = $this->insertTestData('clientes', $customerData);

        // Create plan
        $planData = $this->createPlanData($planOverrides);
        $planId = $this->insertTestData('planes', $planData);

        // Create contract
        $contractData = $this->createContractData($customerId, $planId, $contractOverrides);
        $contractId = $this->insertTestData('contratos', $contractData);

        return [
            'customer' => array_merge($customerData, ['idcliente' => $customerId]),
            'plan' => array_merge($planData, ['idplan' => $planId]),
            'contract' => array_merge($contractData, ['idcontrato' => $contractId])
        ];
    }

    /**
     * Create test environment with essential data
     */
    protected function createTestEnvironment(): array
    {
        // Create zone
        $zoneData = $this->createZoneData();
        $zoneId = $this->insertTestData('zonas', $zoneData);

        // Create router
        $routerData = $this->createRouterData();
        $routerId = $this->insertTestData('routers', $routerData);

        // Create user
        $userData = $this->createUserData();
        $userId = $this->insertTestData('usuarios', $userData);

        // Create plan
        $planData = $this->createPlanData();
        $planId = $this->insertTestData('planes', $planData);

        return [
            'zone' => array_merge($zoneData, ['idzona' => $zoneId]),
            'router' => array_merge($routerData, ['idrouter' => $routerId]),
            'user' => array_merge($userData, ['idusuario' => $userId]),
            'plan' => array_merge($planData, ['idplan' => $planId])
        ];
    }

    /**
     * Generate test data variations for edge cases
     */
    protected function getEdgeCaseTestData(): array
    {
        return [
            'empty_strings' => [
                'nombre' => '',
                'email' => '',
                'telefono' => '',
                'direccion' => ''
            ],
            'null_values' => [
                'observaciones' => null,
                'referencia' => null,
                'coordenadas' => null
            ],
            'max_length_strings' => [
                'nombre' => str_repeat('A', 255),
                'direccion' => str_repeat('B', 500),
                'observaciones' => str_repeat('C', 1000)
            ],
            'special_characters' => [
                'nombre' => "Test & Customer's Name (2024)",
                'direccion' => "Av. José María Arguedas #123 - Dpto. 4B",
                'email' => 'test+user@domain-name.co.uk'
            ],
            'unicode_characters' => [
                'nombre' => 'José María Arguedas Ñoño',
                'direccion' => 'Calle Cañón del Pato 123, Señor de Sipán'
            ]
        ];
    }
}