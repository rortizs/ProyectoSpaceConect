<?php

/**
 * Base Fixture Class
 *
 * Foundation class for all database fixtures in the ISP Management System.
 * Provides common functionality for creating and managing test data.
 */
abstract class BaseFixture
{
    /**
     * Database connection
     */
    protected $db;

    /**
     * Created fixture data
     */
    protected array $createdData = [];

    /**
     * Dependencies (other fixtures this fixture depends on)
     */
    protected array $dependencies = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Mysql();
        if (!$this->db->conection()) {
            throw new Exception('Failed to connect to database for fixtures');
        }
    }

    /**
     * Load fixture data
     */
    abstract public function load(): array;

    /**
     * Get fixture name
     */
    abstract public function getName(): string;

    /**
     * Get fixture dependencies
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Insert data into table
     */
    protected function insert(string $table, array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        if ($this->db->prepare($sql, array_values($data))) {
            $id = $this->db->lastInsertId();
            $this->createdData[] = ['table' => $table, 'id' => $id, 'data' => $data];
            return $id;
        }

        throw new Exception("Failed to insert fixture data into {$table}");
    }

    /**
     * Insert multiple records
     */
    protected function insertBatch(string $table, array $records): array
    {
        $ids = [];
        foreach ($records as $record) {
            $ids[] = $this->insert($table, $record);
        }
        return $ids;
    }

    /**
     * Update fixture data
     */
    protected function update(string $table, array $data, array $where): bool
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

        return $this->db->prepare($sql, $values);
    }

    /**
     * Get created data for specific table
     */
    public function getCreatedData(string $table = null): array
    {
        if ($table === null) {
            return $this->createdData;
        }

        return array_filter($this->createdData, function($item) use ($table) {
            return $item['table'] === $table;
        });
    }

    /**
     * Clean up created data
     */
    public function cleanup(): void
    {
        // Delete in reverse order to respect foreign key constraints
        $tables = array_reverse(array_unique(array_column($this->createdData, 'table')));

        foreach ($tables as $table) {
            $ids = array_column(
                array_filter($this->createdData, function($item) use ($table) {
                    return $item['table'] === $table;
                }),
                'id'
            );

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "DELETE FROM {$table} WHERE id IN ({$placeholders})";
                $this->db->prepare($sql, $ids);
            }
        }

        $this->createdData = [];
    }

    /**
     * Generate unique identifier
     */
    protected function generateId(): string
    {
        return uniqid('fixture_', true);
    }

    /**
     * Generate random string
     */
    protected function randomString(int $length = 10): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    /**
     * Generate random email
     */
    protected function randomEmail(): string
    {
        return strtolower($this->randomString(8)) . '@fixture.test';
    }

    /**
     * Generate random phone
     */
    protected function randomPhone(): string
    {
        return '+51' . rand(900000000, 999999999);
    }

    /**
     * Generate Peru coordinates
     */
    protected function randomCoordinates(): string
    {
        $lat = -18.0 + (rand(0, 1000) / 1000) * 18; // Peru bounds
        $lng = -81.0 + (rand(0, 1000) / 1000) * 13;
        return round($lat, 6) . ',' . round($lng, 6);
    }

    /**
     * Generate random IP address
     */
    protected function randomIpAddress(): string
    {
        return '192.168.' . rand(1, 254) . '.' . rand(1, 254);
    }

    /**
     * Check if record exists
     */
    protected function exists(string $table, array $where): bool
    {
        $whereClause = [];
        $values = [];

        foreach ($where as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE " . implode(' AND ', $whereClause);

        if ($this->db->prepare($sql, $values)) {
            $result = $this->db->getResult();
            return (int)$result['count'] > 0;
        }

        return false;
    }

    /**
     * Get existing record
     */
    protected function getExisting(string $table, array $where): ?array
    {
        $whereClause = [];
        $values = [];

        foreach ($where as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $whereClause) . " LIMIT 1";

        if ($this->db->prepare($sql, $values)) {
            return $this->db->getResult();
        }

        return null;
    }

    /**
     * Get or create record
     */
    protected function getOrCreate(string $table, array $where, array $data = []): int
    {
        $existing = $this->getExisting($table, $where);
        if ($existing) {
            return (int)$existing['id'];
        }

        return $this->insert($table, array_merge($where, $data));
    }

    /**
     * Create with error handling
     */
    protected function createSafely(string $table, array $data): ?int
    {
        try {
            return $this->insert($table, $data);
        } catch (Exception $e) {
            error_log("Fixture creation failed for {$table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate required tables exist
     */
    protected function validateTables(array $tables): bool
    {
        foreach ($tables as $table) {
            $sql = "SHOW TABLES LIKE '{$table}'";
            if (!$this->db->query($sql)) {
                throw new Exception("Required table '{$table}' does not exist");
            }
        }
        return true;
    }

    /**
     * Log fixture activity
     */
    protected function log(string $message): void
    {
        if (defined('ENABLE_TEST_LOGGING') && ENABLE_TEST_LOGGING) {
            error_log("[FIXTURE] " . $this->getName() . ": " . $message);
        }
    }
}