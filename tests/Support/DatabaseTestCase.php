<?php

require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/Traits/DatabaseTransactions.php';

/**
 * Database Test Case
 *
 * Base class for tests that interact with the database.
 * Provides database transaction management and test data utilities.
 */
abstract class DatabaseTestCase extends BaseTestCase
{
    use DatabaseTransactions;

    /**
     * Database connection instance
     */
    protected $db;

    /**
     * Test data that should be cleaned up
     */
    protected array $testDataToCleanup = [];

    /**
     * Set up database connection and transaction
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeDatabase();
        $this->beginDatabaseTransaction();
    }

    /**
     * Rollback transaction and cleanup
     */
    protected function tearDown(): void
    {
        $this->rollbackDatabaseTransaction();
        $this->cleanupTestData();
        parent::tearDown();
    }

    /**
     * Initialize database connection
     */
    protected function initializeDatabase(): void
    {
        try {
            $this->db = new Mysql();
            if (!$this->db->conection()) {
                $this->markTestSkipped('Could not connect to test database');
            }
        } catch (Exception $e) {
            $this->markTestSkipped('Database initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a raw SQL query for testing
     */
    protected function executeQuery(string $sql, array $params = []): bool
    {
        try {
            if (empty($params)) {
                return $this->db->query($sql);
            } else {
                return $this->db->prepare($sql, $params);
            }
        } catch (Exception $e) {
            $this->fail('Query execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Insert test data and mark for cleanup
     */
    protected function insertTestData(string $table, array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        try {
            if ($this->db->prepare($sql, array_values($data))) {
                $id = $this->db->lastInsertId();
                $this->testDataToCleanup[] = ['table' => $table, 'id' => $id];
                return $id;
            }
            throw new Exception('Insert failed');
        } catch (Exception $e) {
            $this->fail('Failed to insert test data: ' . $e->getMessage());
        }
    }

    /**
     * Update test data
     */
    protected function updateTestData(string $table, array $data, array $where): bool
    {
        $setClause = [];
        $values = [];

        foreach ($data as $field => $value) {
            $setClause[] = "{$field} = ?";
            $values[] = $value;
        }

        $whereClause = [];
        foreach ($where as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);

        try {
            return $this->db->prepare($sql, $values);
        } catch (Exception $e) {
            $this->fail('Failed to update test data: ' . $e->getMessage());
        }
    }

    /**
     * Fetch test data
     */
    protected function fetchTestData(string $table, array $where = [], string $fields = '*'): array
    {
        $sql = "SELECT {$fields} FROM {$table}";
        $values = [];

        if (!empty($where)) {
            $whereClause = [];
            foreach ($where as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        try {
            if ($this->db->prepare($sql, $values)) {
                return $this->db->getResults();
            }
            return [];
        } catch (Exception $e) {
            $this->fail('Failed to fetch test data: ' . $e->getMessage());
        }
    }

    /**
     * Count records in table
     */
    protected function countRecords(string $table, array $where = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $values = [];

        if (!empty($where)) {
            $whereClause = [];
            foreach ($where as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        try {
            if ($this->db->prepare($sql, $values)) {
                $result = $this->db->getResult();
                return (int)$result['count'];
            }
            return 0;
        } catch (Exception $e) {
            $this->fail('Failed to count records: ' . $e->getMessage());
        }
    }

    /**
     * Assert that a record exists in the database
     */
    protected function assertDatabaseHas(string $table, array $where, string $message = ''): void
    {
        $count = $this->countRecords($table, $where);
        $this->assertGreaterThan(0, $count,
            $message ?: "Record not found in table {$table} with conditions: " . json_encode($where));
    }

    /**
     * Assert that a record does not exist in the database
     */
    protected function assertDatabaseMissing(string $table, array $where, string $message = ''): void
    {
        $count = $this->countRecords($table, $where);
        $this->assertEquals(0, $count,
            $message ?: "Record found in table {$table} with conditions: " . json_encode($where));
    }

    /**
     * Assert that a table has a specific number of records
     */
    protected function assertDatabaseCount(string $table, int $expectedCount, array $where = [], string $message = ''): void
    {
        $actualCount = $this->countRecords($table, $where);
        $this->assertEquals($expectedCount, $actualCount,
            $message ?: "Table {$table} has {$actualCount} records, expected {$expectedCount}");
    }

    /**
     * Create a test client record
     */
    protected function createTestClient(array $overrides = []): array
    {
        $data = array_merge([
            'nombre' => 'Test Client ' . $this->randomString(5),
            'documento' => rand(10000000, 99999999),
            'telefono' => $this->randomPhone(),
            'email' => $this->randomEmail(),
            'direccion' => 'Test Address',
            'fecha_registro' => date('Y-m-d H:i:s'),
            'estado' => 1,
            'idzona' => 1,
        ], $overrides);

        $clientId = $this->insertTestData('clientes', $data);
        $data['idcliente'] = $clientId;

        return $data;
    }

    /**
     * Create a test router record
     */
    protected function createTestRouter(array $overrides = []): array
    {
        $data = array_merge([
            'nombre' => 'Test Router ' . $this->randomString(5),
            'host' => $this->randomIpAddress(),
            'port' => 8728,
            'user' => 'admin',
            'password' => 'test123',
            'version' => '6.48.1',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s'),
        ], $overrides);

        $routerId = $this->insertTestData('routers', $data);
        $data['idrouter'] = $routerId;

        return $data;
    }

    /**
     * Create a test plan record
     */
    protected function createTestPlan(array $overrides = []): array
    {
        $data = array_merge([
            'nombre' => 'Test Plan ' . $this->randomString(5),
            'precio' => rand(50, 200),
            'subida' => rand(1, 10) . 'M',
            'bajada' => rand(5, 50) . 'M',
            'moneda' => 'PEN',
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s'),
        ], $overrides);

        $planId = $this->insertTestData('planes', $data);
        $data['idplan'] = $planId;

        return $data;
    }

    /**
     * Create a test contract record
     */
    protected function createTestContract(int $clientId, int $planId, array $overrides = []): array
    {
        $data = array_merge([
            'idcliente' => $clientId,
            'idplan' => $planId,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_corte' => date('Y-m-d', strtotime('+1 month')),
            'estado' => 1,
            'fecha_creado' => date('Y-m-d H:i:s'),
        ], $overrides);

        $contractId = $this->insertTestData('contratos', $data);
        $data['idcontrato'] = $contractId;

        return $data;
    }

    /**
     * Cleanup test data (called in tearDown)
     */
    protected function cleanupTestData(): void
    {
        // Data cleanup is handled by transaction rollback
        $this->testDataToCleanup = [];
    }

    /**
     * Seed database with essential test data
     */
    protected function seedEssentialData(): void
    {
        // Create test zones if they don't exist
        $zoneExists = $this->countRecords('zonas', ['idzona' => 1]);
        if ($zoneExists === 0) {
            $this->insertTestData('zonas', [
                'idzona' => 1,
                'nombre' => 'Test Zone',
                'descripcion' => 'Test zone for unit tests',
                'estado' => 1,
                'fecha_creado' => date('Y-m-d H:i:s')
            ]);
        }

        // Create test user roles if they don't exist
        $roleExists = $this->countRecords('roles', ['idrol' => 1]);
        if ($roleExists === 0) {
            $this->insertTestData('roles', [
                'idrol' => 1,
                'nombre' => 'Administrator',
                'descripcion' => 'System administrator',
                'estado' => 1,
                'fecha_creado' => date('Y-m-d H:i:s')
            ]);
        }
    }
}