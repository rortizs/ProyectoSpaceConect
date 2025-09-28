<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Clients Fixture
 *
 * Creates comprehensive client data with various scenarios including active, suspended,
 * cancelled clients with different plans, locations, and lifecycle states.
 */
class ClientsFixture extends BaseFixture
{
    protected array $dependencies = ['EssentialDataFixture', 'PlansFixture', 'RouterFixture'];

    // Client states
    const STATE_ACTIVE = 1;
    const STATE_SUSPENDED = 2;
    const STATE_CANCELLED = 3;
    const STATE_PENDING = 4;

    public function getName(): string
    {
        return 'Clients';
    }

    public function load(): array
    {
        $this->log('Loading clients data...');

        $data = [];

        // Validate required tables exist
        $this->validateTables(['clients', 'contracts', 'detail_contracts']);

        // Create clients with different scenarios
        $data['clients'] = $this->createClients();

        // Create contracts for clients
        $data['contracts'] = $this->createContracts($data['clients']);

        $this->log('Clients loaded successfully');

        return $data;
    }

    /**
     * Create diverse client data
     */
    private function createClients(): array
    {
        $clients = [
            // Active residential clients
            [
                'names' => 'Juan Carlos',
                'surnames' => 'García López',
                'documentid' => 1, // DNI
                'document' => '12345678',
                'mobile' => '987654321',
                'mobile_optional' => '987654322',
                'email' => 'juan.garcia@email.com',
                'address' => 'Av. Los Jardines 123, Miraflores',
                'reference' => 'Casa color blanco, portón negro',
                'note' => 'Cliente preferencial, pago puntual',
                'latitud' => '-12.119294',
                'longitud' => '-77.033673',
                'state' => self::STATE_ACTIVE,
                'net_router' => 4,
                'net_name' => 'cliente_juan_garcia',
                'net_password' => 'password123',
                'net_localaddress' => '192.168.100.2',
                'net_ip' => '192.168.100.2',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],
            [
                'names' => 'María Elena',
                'surnames' => 'Rodríguez Silva',
                'documentid' => 1,
                'document' => '23456789',
                'mobile' => '987654323',
                'mobile_optional' => '',
                'email' => 'maria.rodriguez@email.com',
                'address' => 'Jr. Las Flores 456, San Isidro',
                'reference' => 'Edificio azul, departamento 301',
                'note' => 'Cliente desde 2020, sin problemas',
                'latitud' => '-12.096219',
                'longitud' => '-77.037132',
                'state' => self::STATE_ACTIVE,
                'net_router' => 4,
                'net_name' => 'cliente_maria_rodriguez',
                'net_password' => 'maria2024',
                'net_localaddress' => '192.168.100.3',
                'net_ip' => '192.168.100.3',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // Business clients
            [
                'names' => 'Carlos Alberto',
                'surnames' => 'Mendoza Vargas',
                'documentid' => 2, // RUC
                'document' => '20123456789',
                'mobile' => '987654324',
                'mobile_optional' => '014567890',
                'email' => 'carlos.mendoza@empresa.com',
                'address' => 'Av. El Sol 789, Centro Empresarial Torre A',
                'reference' => 'Oficina 1205, edificio corporativo',
                'note' => 'Cliente empresarial - contactar solo en horario comercial',
                'latitud' => '-12.046374',
                'longitud' => '-77.042793',
                'state' => self::STATE_ACTIVE,
                'net_router' => 5,
                'net_name' => 'empresa_mendoza',
                'net_password' => 'empresaSegura2024!',
                'net_localaddress' => '192.168.200.10',
                'net_ip' => '192.168.200.10',
                'nap_cliente_id' => 2,
                'ap_cliente_id' => 2,
                'zonaid' => 2
            ],

            // Suspended clients
            [
                'names' => 'Ana Lucía',
                'surnames' => 'Torres Morales',
                'documentid' => 1,
                'document' => '34567890',
                'mobile' => '987654325',
                'mobile_optional' => '',
                'email' => 'ana.torres@email.com',
                'address' => 'Calle Los Pinos 321, La Molina',
                'reference' => 'Casa esquina, cerca al parque',
                'note' => 'Suspendida por falta de pago - 2 meses',
                'latitud' => '-12.076842',
                'longitud' => '-76.943617',
                'state' => self::STATE_SUSPENDED,
                'net_router' => 4,
                'net_name' => 'cliente_ana_torres',
                'net_password' => 'ana123',
                'net_localaddress' => '192.168.100.4',
                'net_ip' => '192.168.100.4',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],
            [
                'names' => 'Roberto',
                'surnames' => 'Salinas Huamán',
                'documentid' => 1,
                'document' => '45678901',
                'mobile' => '987654326',
                'mobile_optional' => '',
                'email' => 'roberto.salinas@email.com',
                'address' => 'Av. Los Eucaliptos 654, Surco',
                'reference' => 'Casa de dos pisos, puerta verde',
                'note' => 'Suspendido temporalmente por problemas técnicos',
                'latitud' => '-12.135021',
                'longitud' => '-77.009490',
                'state' => self::STATE_SUSPENDED,
                'net_router' => 5,
                'net_name' => 'cliente_roberto_salinas',
                'net_password' => 'roberto456',
                'net_localaddress' => '192.168.200.11',
                'net_ip' => '192.168.200.11',
                'nap_cliente_id' => 2,
                'ap_cliente_id' => 2,
                'zonaid' => 2
            ],

            // Cancelled clients
            [
                'names' => 'Patricia',
                'surnames' => 'Vega Chávez',
                'documentid' => 1,
                'document' => '56789012',
                'mobile' => '987654327',
                'mobile_optional' => '',
                'email' => 'patricia.vega@email.com',
                'address' => 'Jr. San Martín 987, Pueblo Libre',
                'reference' => 'Casa antigua, cerca a la iglesia',
                'note' => 'Cancelado por mudanza - marzo 2024',
                'latitud' => '-12.075000',
                'longitud' => '-77.063333',
                'state' => self::STATE_CANCELLED,
                'net_router' => 4,
                'net_name' => 'cliente_patricia_vega',
                'net_password' => 'patricia789',
                'net_localaddress' => '192.168.100.5',
                'net_ip' => '192.168.100.5',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // Pending clients
            [
                'names' => 'Luis Fernando',
                'surnames' => 'Castro Ruiz',
                'documentid' => 1,
                'document' => '67890123',
                'mobile' => '987654328',
                'mobile_optional' => '014567891',
                'email' => 'luis.castro@email.com',
                'address' => 'Av. La Marina 147, San Miguel',
                'reference' => 'Condominio Los Álamos, casa 15',
                'note' => 'Pendiente de instalación - programado para la próxima semana',
                'latitud' => '-12.077500',
                'longitud' => '-77.091667',
                'state' => self::STATE_PENDING,
                'net_router' => 4,
                'net_name' => 'cliente_luis_castro',
                'net_password' => 'luis2024',
                'net_localaddress' => '192.168.100.6',
                'net_ip' => '192.168.100.6',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // Premium clients
            [
                'names' => 'Diana Carolina',
                'surnames' => 'Herrera Paredes',
                'documentid' => 1,
                'document' => '78901234',
                'mobile' => '987654329',
                'mobile_optional' => '987654330',
                'email' => 'diana.herrera@email.com',
                'address' => 'Av. Benavides 2589, Miraflores',
                'reference' => 'Torre residencial, piso 18',
                'note' => 'Cliente premium - plan empresarial en casa',
                'latitud' => '-12.134722',
                'longitud' => '-77.025278',
                'state' => self::STATE_ACTIVE,
                'net_router' => 5,
                'net_name' => 'cliente_diana_herrera',
                'net_password' => 'dianaSecure2024!',
                'net_localaddress' => '192.168.200.20',
                'net_ip' => '192.168.200.20',
                'nap_cliente_id' => 2,
                'ap_cliente_id' => 2,
                'zonaid' => 2
            ],

            // Long-term clients
            [
                'names' => 'Miguel Ángel',
                'surnames' => 'Ramírez Flores',
                'documentid' => 1,
                'document' => '89012345',
                'mobile' => '987654331',
                'mobile_optional' => '',
                'email' => 'miguel.ramirez@email.com',
                'address' => 'Calle Las Magnolias 852, Jesús María',
                'reference' => 'Casa de un piso, jardín frontal',
                'note' => 'Cliente desde 2018 - nunca ha tenido problemas de pago',
                'latitud' => '-12.067778',
                'longitud' => '-77.049444',
                'state' => self::STATE_ACTIVE,
                'net_router' => 4,
                'net_name' => 'cliente_miguel_ramirez',
                'net_password' => 'miguel2018',
                'net_localaddress' => '192.168.100.7',
                'net_ip' => '192.168.100.7',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // Student clients
            [
                'names' => 'Sofía Alejandra',
                'surnames' => 'Guerrero Sánchez',
                'documentid' => 1,
                'document' => '90123456',
                'mobile' => '987654332',
                'mobile_optional' => '',
                'email' => 'sofia.guerrero@estudiante.edu.pe',
                'address' => 'Jr. Los Estudiantes 159, Pueblo Libre',
                'reference' => 'Residencia universitaria, cuarto 204',
                'note' => 'Estudiante universitaria - plan especial',
                'latitud' => '-12.075556',
                'longitud' => '-77.063056',
                'state' => self::STATE_ACTIVE,
                'net_router' => 4,
                'net_name' => 'cliente_sofia_guerrero',
                'net_password' => 'sofia123',
                'net_localaddress' => '192.168.100.8',
                'net_ip' => '192.168.100.8',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // International client
            [
                'names' => 'Alessandro',
                'surnames' => 'Rossi Martinez',
                'documentid' => 3, // Passport
                'document' => 'IT123456789',
                'mobile' => '987654333',
                'mobile_optional' => '+390123456789',
                'email' => 'alessandro.rossi@email.com',
                'address' => 'Av. Principal 741, Barranco',
                'reference' => 'Departamento temporal, edificio moderno',
                'note' => 'Cliente extranjero - contrato temporal 6 meses',
                'latitud' => '-12.146944',
                'longitud' => '-77.019167',
                'state' => self::STATE_ACTIVE,
                'net_router' => 4,
                'net_name' => 'cliente_alessandro_rossi',
                'net_password' => 'alessandro2024',
                'net_localaddress' => '192.168.100.9',
                'net_ip' => '192.168.100.9',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 1
            ],

            // Corporate headquarters
            [
                'names' => 'TechSolutions',
                'surnames' => 'Corp S.A.C.',
                'documentid' => 2, // RUC
                'document' => '20987654321',
                'mobile' => '987654334',
                'mobile_optional' => '014567892',
                'email' => 'admin@techsolutions.com.pe',
                'address' => 'Av. Javier Prado 5555, San Isidro',
                'reference' => 'Centro empresarial Torre B, pisos 8-10',
                'note' => 'Sede principal - múltiples conexiones',
                'latitud' => '-12.090000',
                'longitud' => '-77.030000',
                'state' => self::STATE_ACTIVE,
                'net_router' => 5,
                'net_name' => 'techsolutions_corp',
                'net_password' => 'TechSecure2024!@#',
                'net_localaddress' => '192.168.200.50',
                'net_ip' => '192.168.200.50',
                'nap_cliente_id' => 2,
                'ap_cliente_id' => 2,
                'zonaid' => 2
            ],

            // Problem client (for testing edge cases)
            [
                'names' => 'Cliente',
                'surnames' => 'Problemático Test',
                'documentid' => 1,
                'document' => '00000001',
                'mobile' => '900000001',
                'mobile_optional' => '',
                'email' => 'problema@test.com',
                'address' => 'Dirección de prueba 999',
                'reference' => 'Cliente para pruebas de errores',
                'note' => 'Cliente ficticio para testing - múltiples reclamos',
                'latitud' => '-12.000000',
                'longitud' => '-77.000000',
                'state' => self::STATE_SUSPENDED,
                'net_router' => 4,
                'net_name' => 'cliente_problema',
                'net_password' => 'problema123',
                'net_localaddress' => '192.168.100.99',
                'net_ip' => '192.168.100.99',
                'nap_cliente_id' => 1,
                'ap_cliente_id' => 1,
                'zonaid' => 3
            ]
        ];

        $createdClients = [];
        foreach ($clients as $client) {
            try {
                $id = $this->insert('clients', $client);
                $createdClients[] = array_merge($client, ['id' => $id]);
                $this->log("Created client: {$client['names']} {$client['surnames']} (ID: {$id})");
            } catch (Exception $e) {
                $this->log("Failed to create client {$client['names']}: " . $e->getMessage());
            }
        }

        return $createdClients;
    }

    /**
     * Create contracts for clients
     */
    private function createContracts(array $clients): array
    {
        $createdContracts = [];

        foreach ($clients as $client) {
            $clientId = $client['id'];
            $state = $client['state'];

            // Determine contract characteristics based on client type
            $contractData = $this->getContractDataForClient($client);

            $contract = [
                'userid' => 1, // Admin user
                'clientid' => $clientId,
                'internal_code' => 'CON-' . str_pad($clientId, 6, '0', STR_PAD_LEFT),
                'payday' => $contractData['payday'],
                'create_invoice' => 1,
                'days_grace' => $contractData['days_grace'],
                'discount' => $contractData['discount'],
                'discount_price' => $contractData['discount_price'],
                'months_discount' => $contractData['months_discount'],
                'remaining_discount' => $contractData['remaining_discount'],
                'contract_date' => $contractData['contract_date'],
                'suspension_date' => $contractData['suspension_date'],
                'finish_date' => $contractData['finish_date'],
                'state' => $this->getContractState($state)
            ];

            try {
                $contractId = $this->insert('contracts', $contract);
                $createdContracts[] = array_merge($contract, ['id' => $contractId]);

                // Create contract details (services)
                $this->createContractDetails($contractId, $client);

                $this->log("Created contract for client {$clientId}: CON-" . str_pad($clientId, 6, '0', STR_PAD_LEFT));
            } catch (Exception $e) {
                $this->log("Failed to create contract for client {$clientId}: " . $e->getMessage());
            }
        }

        return $createdContracts;
    }

    /**
     * Get contract data based on client characteristics
     */
    private function getContractDataForClient(array $client): array
    {
        $isBusinessClient = $client['documentid'] == 2; // RUC
        $isPremiumClient = strpos($client['note'], 'premium') !== false;
        $isStudentClient = strpos($client['email'], 'estudiante') !== false;

        // Contract date variations
        $contractDates = [
            '2020-01-15', '2021-03-20', '2022-06-10', '2023-01-25',
            '2023-08-15', '2024-01-10', '2024-03-15', '2024-06-01'
        ];
        $contractDate = $contractDates[array_rand($contractDates)];

        // Payment day (1-28 of month)
        $payday = $isBusinessClient ? 30 : rand(1, 28);

        // Grace period
        $daysGrace = $isPremiumClient ? 10 : ($isBusinessClient ? 15 : 5);

        // Discounts
        $discount = 0;
        $discountPrice = 0.00;
        $monthsDiscount = 0;
        $remainingDiscount = 0;

        if ($isStudentClient) {
            $discount = 20; // 20% student discount
            $discountPrice = 10.00;
            $monthsDiscount = 12;
            $remainingDiscount = rand(3, 12);
        } elseif ($isPremiumClient) {
            $discount = 10; // 10% premium loyalty discount
            $discountPrice = 15.00;
            $monthsDiscount = 6;
            $remainingDiscount = rand(1, 6);
        }

        // Dates
        $suspensionDate = date('Y-m-d', strtotime($contractDate . ' + 1 year'));
        $finishDate = date('Y-m-d', strtotime($contractDate . ' + 2 years'));

        return [
            'payday' => $payday,
            'days_grace' => $daysGrace,
            'discount' => $discount,
            'discount_price' => $discountPrice,
            'months_discount' => $monthsDiscount,
            'remaining_discount' => $remainingDiscount,
            'contract_date' => $contractDate . ' 10:00:00',
            'suspension_date' => $suspensionDate,
            'finish_date' => $finishDate
        ];
    }

    /**
     * Get contract state based on client state
     */
    private function getContractState(int $clientState): int
    {
        switch ($clientState) {
            case self::STATE_ACTIVE:
                return 1; // Active contract
            case self::STATE_SUSPENDED:
                return 2; // Suspended contract
            case self::STATE_CANCELLED:
                return 3; // Cancelled contract
            case self::STATE_PENDING:
                return 0; // Pending contract
            default:
                return 1;
        }
    }

    /**
     * Create contract details (services assigned to contract)
     */
    private function createContractDetails(int $contractId, array $client): void
    {
        // Determine service based on client type
        $serviceId = $this->determineServiceForClient($client);
        $price = $this->getPriceForService($serviceId, $client);

        $contractDetail = [
            'contractid' => $contractId,
            'serviceid' => $serviceId,
            'price' => $price,
            'registration_date' => date('Y-m-d H:i:s'),
            'state' => 1
        ];

        try {
            $this->insert('detail_contracts', $contractDetail);
        } catch (Exception $e) {
            $this->log("Failed to create contract detail for contract {$contractId}: " . $e->getMessage());
        }
    }

    /**
     * Determine appropriate service for client
     */
    private function determineServiceForClient(array $client): int
    {
        $isBusinessClient = $client['documentid'] == 2;
        $isPremiumClient = strpos($client['note'], 'premium') !== false;
        $isStudentClient = strpos($client['email'], 'estudiante') !== false;

        if ($isStudentClient) {
            return 1; // Basic plan for students
        } elseif ($isPremiumClient) {
            return 3; // Premium plan
        } elseif ($isBusinessClient) {
            return 2; // Business plan
        } else {
            return 1; // Standard residential
        }
    }

    /**
     * Get price for service with client-specific adjustments
     */
    private function getPriceForService(int $serviceId, array $client): float
    {
        $basePrices = [
            1 => 50.00,  // Basic
            2 => 120.00, // Business
            3 => 200.00  // Premium
        ];

        $price = $basePrices[$serviceId] ?? 50.00;

        // Apply student discount
        if (strpos($client['email'], 'estudiante') !== false) {
            $price *= 0.8; // 20% discount
        }

        return round($price, 2);
    }

    /**
     * Get clients by state
     */
    public function getClientsByState(int $state): array
    {
        return array_filter($this->getCreatedData('clients'), function($item) use ($state) {
            return $item['data']['state'] === $state;
        });
    }

    /**
     * Get active clients
     */
    public function getActiveClients(): array
    {
        return $this->getClientsByState(self::STATE_ACTIVE);
    }

    /**
     * Get suspended clients
     */
    public function getSuspendedClients(): array
    {
        return $this->getClientsByState(self::STATE_SUSPENDED);
    }

    /**
     * Get business clients
     */
    public function getBusinessClients(): array
    {
        return array_filter($this->getCreatedData('clients'), function($item) {
            return $item['data']['documentid'] === 2; // RUC
        });
    }

    /**
     * Get clients by zone
     */
    public function getClientsByZone(int $zoneId): array
    {
        return array_filter($this->getCreatedData('clients'), function($item) use ($zoneId) {
            return $item['data']['zonaid'] === $zoneId;
        });
    }

    /**
     * Create bulk clients for performance testing
     */
    public function createBulkClients(int $count): array
    {
        if (!defined('CREATE_BULK_DATA') || !CREATE_BULK_DATA) {
            return [];
        }

        $bulkClients = [];
        $names = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Patricia', 'Miguel', 'Rosa', 'José', 'Carmen'];
        $surnames = ['García', 'López', 'Martínez', 'González', 'Rodríguez', 'Fernández', 'Sánchez', 'Morales'];

        for ($i = 1; $i <= $count; $i++) {
            $name = $names[array_rand($names)];
            $surname = $surnames[array_rand($surnames)];
            $document = str_pad($i + 10000000, 8, '0', STR_PAD_LEFT);

            $client = [
                'names' => $name,
                'surnames' => $surname . ' Bulk',
                'documentid' => 1,
                'document' => $document,
                'mobile' => '9' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'mobile_optional' => '',
                'email' => strtolower($name) . '.' . strtolower($surname) . $i . '@bulk.test',
                'address' => "Av. Bulk Testing {$i}, Lima",
                'reference' => "Casa bulk {$i}",
                'note' => 'Cliente bulk para testing',
                'latitud' => number_format(-12.0 + (rand(-100, 100) / 1000), 6),
                'longitud' => number_format(-77.0 + (rand(-100, 100) / 1000), 6),
                'state' => [1, 1, 1, 2, 3][array_rand([1, 1, 1, 2, 3])], // Mostly active
                'net_router' => [4, 5][array_rand([4, 5])],
                'net_name' => 'bulk_client_' . $i,
                'net_password' => 'bulk' . $i,
                'net_localaddress' => '192.168.' . (100 + ($i % 3)) . '.' . (10 + $i),
                'net_ip' => '192.168.' . (100 + ($i % 3)) . '.' . (10 + $i),
                'nap_cliente_id' => ($i % 2) + 1,
                'ap_cliente_id' => ($i % 2) + 1,
                'zonaid' => ($i % 3) + 1
            ];

            try {
                $id = $this->insert('clients', $client);
                $bulkClients[] = array_merge($client, ['id' => $id]);

                // Create contract for bulk client
                $this->createSimpleContract($id, $client);

            } catch (Exception $e) {
                $this->log("Failed to create bulk client {$i}: " . $e->getMessage());
            }
        }

        return $bulkClients;
    }

    /**
     * Create simple contract for bulk testing
     */
    private function createSimpleContract(int $clientId, array $client): void
    {
        $contract = [
            'userid' => 1,
            'clientid' => $clientId,
            'internal_code' => 'BULK-' . str_pad($clientId, 6, '0', STR_PAD_LEFT),
            'payday' => rand(1, 28),
            'create_invoice' => 1,
            'days_grace' => 5,
            'discount' => 0,
            'discount_price' => 0.00,
            'months_discount' => 0,
            'remaining_discount' => 0,
            'contract_date' => date('Y-m-d H:i:s', strtotime('-' . rand(30, 365) . ' days')),
            'suspension_date' => date('Y-m-d', strtotime('+1 year')),
            'finish_date' => date('Y-m-d', strtotime('+2 years')),
            'state' => $this->getContractState($client['state'])
        ];

        try {
            $contractId = $this->insert('contracts', $contract);

            // Add service detail
            $this->insert('detail_contracts', [
                'contractid' => $contractId,
                'serviceid' => 1, // Basic service
                'price' => 50.00,
                'registration_date' => date('Y-m-d H:i:s'),
                'state' => 1
            ]);
        } catch (Exception $e) {
            $this->log("Failed to create bulk contract for client {$clientId}: " . $e->getMessage());
        }
    }
}