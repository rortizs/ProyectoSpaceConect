<?php

/**
 * Test Data Factory
 *
 * Centralized factory for creating test data with realistic and consistent values.
 */
class TestDataFactory
{
    /**
     * Faker instance for generating realistic data
     */
    private static $faker;

    /**
     * Initialize faker instance
     */
    private static function getFaker()
    {
        if (!self::$faker && class_exists('Faker\Factory')) {
            self::$faker = \Faker\Factory::create('es_PE'); // Peru locale
        }
        return self::$faker;
    }

    /**
     * Create customer factory
     */
    public static function customer(array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'nombre' => $faker ? $faker->name : 'Test Customer ' . uniqid(),
            'documento' => $faker ? $faker->unique()->numerify('########') : (string)rand(10000000, 99999999),
            'telefono' => $faker ? $faker->phoneNumber : '+51' . rand(900000000, 999999999),
            'email' => $faker ? $faker->unique()->safeEmail : 'test' . uniqid() . '@example.com',
            'direccion' => $faker ? $faker->address : 'Test Address ' . rand(100, 999),
            'distrito' => $faker ? $faker->city : 'Test District',
            'provincia' => $faker ? $faker->state : 'Test Province',
            'departamento' => $faker ? $faker->state : 'Lima',
            'referencia' => $faker ? $faker->sentence(3) : 'Near test landmark',
            'coordenadas' => self::peruCoordinates(),
            'fecha_registro' => date('Y-m-d H:i:s'),
            'estado' => 1,
            'idzona' => 1
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create plan factory
     */
    public static function plan(array $attributes = []): array
    {
        $faker = self::getFaker();
        $speeds = ['5M', '10M', '20M', '50M', '100M', '200M'];
        $uploadSpeeds = ['1M', '2M', '5M', '10M', '20M', '50M'];
        $prices = [50, 75, 100, 150, 200, 300];

        $speedIndex = array_rand($speeds);

        $defaults = [
            'nombre' => $faker ? $faker->words(2, true) . ' Plan' : 'Test Plan ' . uniqid(),
            'descripcion' => $faker ? $faker->sentence() : 'Test internet plan',
            'precio' => $prices[$speedIndex] ?? rand(50, 300),
            'subida' => $uploadSpeeds[$speedIndex] ?? '5M',
            'bajada' => $speeds[$speedIndex] ?? '20M',
            'moneda' => 'PEN',
            'tipo' => $faker ? $faker->randomElement(['residential', 'business', 'premium']) : 'residential',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create contract factory
     */
    public static function contract(int $customerId, int $planId, array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'idcliente' => $customerId,
            'idplan' => $planId,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_corte' => date('Y-m-d', strtotime('+1 month')),
            'dia_corte' => $faker ? $faker->numberBetween(1, 28) : rand(1, 28),
            'precio_personalizado' => null,
            'observaciones' => $faker ? $faker->sentence() : 'Test contract',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create bill factory
     */
    public static function bill(int $contractId, array $attributes = []): array
    {
        $faker = self::getFaker();
        $billNumber = 'F' . date('Ym') . sprintf('%04d', rand(1, 9999));

        $defaults = [
            'idcontrato' => $contractId,
            'numero_factura' => $billNumber,
            'monto' => $faker ? $faker->randomFloat(2, 50, 300) : rand(50, 300),
            'fecha_emision' => date('Y-m-d'),
            'fecha_vencimiento' => date('Y-m-d', strtotime('+15 days')),
            'moneda' => 'PEN',
            'estado' => $faker ? $faker->randomElement(['pending', 'paid', 'overdue', 'cancelled']) : 'pending',
            'tipo' => $faker ? $faker->randomElement(['monthly', 'installation', 'additional']) : 'monthly',
            'observaciones' => $faker ? $faker->sentence() : null,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create payment factory
     */
    public static function payment(int $billId, array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'idfactura' => $billId,
            'monto' => $faker ? $faker->randomFloat(2, 50, 300) : rand(50, 300),
            'metodo_pago' => $faker ? $faker->randomElement(['cash', 'transfer', 'card', 'mobile']) : 'cash',
            'numero_transaccion' => 'TXN' . sprintf('%06d', rand(1, 999999)),
            'fecha_pago' => date('Y-m-d H:i:s'),
            'observaciones' => $faker ? $faker->sentence() : 'Test payment',
            'estado' => $faker ? $faker->randomElement(['completed', 'pending', 'failed']) : 'completed',
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create router factory
     */
    public static function router(array $attributes = []): array
    {
        $faker = self::getFaker();
        $versions = ['6.47.10', '6.48.1', '6.49.2', '7.1.1', '7.2.1'];
        $boards = ['RB951G-2HnD', 'RB952Ui-5ac2nD', 'RB2011UiAS-RM', 'CCR1009-7G-1C-1S+'];

        $defaults = [
            'nombre' => $faker ? $faker->words(2, true) . ' Router' : 'Test Router ' . uniqid(),
            'host' => self::privateIpAddress(),
            'port' => 8728,
            'user' => 'admin',
            'password' => $faker ? $faker->password(8, 12) : 'test123',
            'version' => $faker ? $faker->randomElement($versions) : '6.48.1',
            'board' => $faker ? $faker->randomElement($boards) : 'RB951G-2HnD',
            'descripcion' => $faker ? $faker->sentence() : 'Test router',
            'ubicacion' => $faker ? $faker->address : 'Test Location',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create zone factory
     */
    public static function zone(array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'nombre' => $faker ? $faker->words(2, true) . ' Zone' : 'Test Zone ' . uniqid(),
            'descripcion' => $faker ? $faker->sentence() : 'Test zone',
            'coordenadas' => self::peruCoordinates(),
            'radio' => $faker ? $faker->numberBetween(500, 3000) : rand(500, 3000),
            'color' => $faker ? $faker->hexColor : '#' . substr(md5(rand()), 0, 6),
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create user factory
     */
    public static function user(array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'nombre' => $faker ? $faker->name : 'Test User ' . uniqid(),
            'email' => $faker ? $faker->unique()->safeEmail : 'testuser' . uniqid() . '@example.com',
            'usuario' => $faker ? $faker->unique()->userName : 'testuser' . uniqid(),
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'documento' => $faker ? $faker->unique()->numerify('########') : (string)rand(10000000, 99999999),
            'telefono' => $faker ? $faker->phoneNumber : '+51' . rand(900000000, 999999999),
            'rol' => $faker ? $faker->numberBetween(1, 3) : 1,
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create installation factory
     */
    public static function installation(int $contractId, array $attributes = []): array
    {
        $faker = self::getFaker();

        $defaults = [
            'idcontrato' => $contractId,
            'fecha_programada' => date('Y-m-d', strtotime('+' . rand(1, 7) . ' days')),
            'fecha_instalacion' => null,
            'tecnico_asignado' => $faker ? $faker->numberBetween(1, 10) : 1,
            'observaciones' => $faker ? $faker->sentence() : 'Test installation',
            'materiales' => json_encode([
                'cable' => rand(50, 200) . 'm',
                'conectores' => rand(2, 8),
                'router' => rand(0, 1) ? '1' : '0'
            ]),
            'coordenadas' => self::peruCoordinates(),
            'estado' => $faker ? $faker->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']) : 'scheduled',
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create incident factory
     */
    public static function incident(int $customerId, array $attributes = []): array
    {
        $faker = self::getFaker();
        $types = ['technical', 'billing', 'support', 'maintenance'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $defaults = [
            'idcliente' => $customerId,
            'titulo' => $faker ? $faker->sentence(4) : 'Test Incident ' . uniqid(),
            'descripcion' => $faker ? $faker->paragraph() : 'Test incident description',
            'tipo' => $faker ? $faker->randomElement($types) : 'technical',
            'prioridad' => $faker ? $faker->randomElement($priorities) : 'medium',
            'tecnico_asignado' => $faker ? $faker->numberBetween(1, 10) : 1,
            'fecha_reporte' => date('Y-m-d H:i:s'),
            'fecha_solucion' => null,
            'solucion' => null,
            'estado' => $faker ? $faker->randomElement(['open', 'in_progress', 'resolved', 'closed']) : 'open',
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create product factory
     */
    public static function product(array $attributes = []): array
    {
        $faker = self::getFaker();
        $categories = ['equipment', 'cable', 'connector', 'tool', 'accessory'];

        $defaults = [
            'nombre' => $faker ? $faker->words(3, true) : 'Test Product ' . uniqid(),
            'descripcion' => $faker ? $faker->sentence() : 'Test product description',
            'precio' => $faker ? $faker->randomFloat(2, 10, 500) : rand(10, 500),
            'stock' => $faker ? $faker->numberBetween(0, 100) : rand(0, 100),
            'categoria' => $faker ? $faker->randomElement($categories) : 'equipment',
            'codigo' => 'PROD' . sprintf('%04d', rand(1, 9999)),
            'proveedor' => $faker ? $faker->company : 'Test Supplier',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create voucher factory
     */
    public static function voucher(array $attributes = []): array
    {
        $faker = self::getFaker();
        $types = ['discount', 'free_month', 'upgrade', 'installation'];

        $defaults = [
            'codigo' => 'VOU' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'tipo' => $faker ? $faker->randomElement($types) : 'discount',
            'valor' => $faker ? $faker->numberBetween(10, 100) : rand(10, 100),
            'descripcion' => $faker ? $faker->sentence() : 'Test voucher',
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => date('Y-m-d', strtotime('+' . rand(30, 90) . ' days')),
            'usos_maximos' => $faker ? $faker->numberBetween(1, 100) : rand(1, 100),
            'usos_actuales' => 0,
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s')
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Generate Peru coordinates
     */
    public static function peruCoordinates(): string
    {
        // Peru bounds: approximately
        $lat = -18.0 + (rand(0, 1000) / 1000) * 18; // -18 to 0
        $lng = -81.0 + (rand(0, 1000) / 1000) * 13; // -81 to -68
        return round($lat, 6) . ',' . round($lng, 6);
    }

    /**
     * Generate private IP address
     */
    public static function privateIpAddress(): string
    {
        $ranges = [
            ['192.168', 1, 254],
            ['10', 1, 254],
            ['172.16', 1, 254]
        ];

        $range = $ranges[array_rand($ranges)];
        return $range[0] . '.' . rand($range[1], $range[2]) . '.' . rand(1, 254);
    }

    /**
     * Generate MAC address
     */
    public static function macAddress(): string
    {
        return sprintf(
            '%02X:%02X:%02X:%02X:%02X:%02X',
            rand(0, 255), rand(0, 255), rand(0, 255),
            rand(0, 255), rand(0, 255), rand(0, 255)
        );
    }

    /**
     * Create batch of entities
     */
    public static function createBatch(string $type, int $count, array $attributes = []): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = self::$type($attributes);
        }
        return $items;
    }

    /**
     * Create related entities (customer -> contract -> bill)
     */
    public static function createRelatedEntities(array $options = []): array
    {
        $planCount = $options['plans'] ?? 3;
        $customerCount = $options['customers'] ?? 5;
        $contractsPerCustomer = $options['contracts_per_customer'] ?? 1;
        $billsPerContract = $options['bills_per_contract'] ?? 2;

        $result = [
            'plans' => [],
            'customers' => [],
            'contracts' => [],
            'bills' => []
        ];

        // Create plans
        for ($i = 0; $i < $planCount; $i++) {
            $result['plans'][] = self::plan();
        }

        // Create customers with contracts and bills
        for ($i = 0; $i < $customerCount; $i++) {
            $customer = self::customer();
            $result['customers'][] = $customer;

            // Create contracts for customer
            for ($j = 0; $j < $contractsPerCustomer; $j++) {
                $planIndex = rand(0, count($result['plans']) - 1);
                $contract = self::contract($i + 1, $planIndex + 1);
                $result['contracts'][] = $contract;

                // Create bills for contract
                for ($k = 0; $k < $billsPerContract; $k++) {
                    $billDate = date('Y-m-d', strtotime('-' . ($billsPerContract - $k - 1) . ' months'));
                    $bill = self::bill(count($result['contracts']), [
                        'fecha_emision' => $billDate,
                        'fecha_vencimiento' => date('Y-m-d', strtotime($billDate . ' +15 days'))
                    ]);
                    $result['bills'][] = $bill;
                }
            }
        }

        return $result;
    }
}