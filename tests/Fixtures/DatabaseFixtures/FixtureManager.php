<?php

require_once __DIR__ . '/BaseFixture.php';

/**
 * Fixture Manager
 *
 * Central manager to load fixtures in correct dependency order, handle cleanup,
 * and provide utilities for the test data fixture system.
 */
class FixtureManager
{
    private array $fixtures = [];
    private array $loadedFixtures = [];
    private array $fixtureInstances = [];
    private bool $bulkDataEnabled = false;
    private string $logFile;

    // Available fixture classes
    private const FIXTURE_CLASSES = [
        'EssentialDataFixture',
        'PlansFixture',
        'RouterFixture',
        'ClientsFixture',
        'BillingFixture'
    ];

    public function __construct(bool $enableBulkData = false)
    {
        $this->bulkDataEnabled = $enableBulkData;
        $this->logFile = __DIR__ . '/fixture_manager.log';

        // Define bulk data constant for fixtures
        if ($enableBulkData && !defined('CREATE_BULK_DATA')) {
            define('CREATE_BULK_DATA', true);
        }

        $this->initializeFixtures();
    }

    /**
     * Initialize all fixture classes
     */
    private function initializeFixtures(): void
    {
        foreach (self::FIXTURE_CLASSES as $fixtureClass) {
            $filePath = __DIR__ . "/{$fixtureClass}.php";

            if (file_exists($filePath)) {
                require_once $filePath;

                if (class_exists($fixtureClass)) {
                    $this->fixtureInstances[$fixtureClass] = new $fixtureClass();
                    $this->log("Initialized fixture: {$fixtureClass}");
                } else {
                    $this->log("Warning: Class {$fixtureClass} not found in {$filePath}");
                }
            } else {
                $this->log("Warning: Fixture file not found: {$filePath}");
            }
        }
    }

    /**
     * Load all fixtures in dependency order
     */
    public function loadAll(): array
    {
        $this->log('Starting fixture loading process...');

        $startTime = microtime(true);
        $loadOrder = $this->calculateLoadOrder();
        $results = [];

        foreach ($loadOrder as $fixtureName) {
            try {
                $this->log("Loading fixture: {$fixtureName}");
                $fixtureStartTime = microtime(true);

                $data = $this->loadFixture($fixtureName);
                $results[$fixtureName] = $data;

                $fixtureTime = round(microtime(true) - $fixtureStartTime, 2);
                $this->log("Completed fixture {$fixtureName} in {$fixtureTime}s");

            } catch (Exception $e) {
                $this->log("ERROR loading fixture {$fixtureName}: " . $e->getMessage());
                throw new Exception("Failed to load fixture {$fixtureName}: " . $e->getMessage());
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $this->log("All fixtures loaded successfully in {$totalTime}s");

        return $results;
    }

    /**
     * Load specific fixture
     */
    public function loadFixture(string $fixtureName): array
    {
        if (isset($this->loadedFixtures[$fixtureName])) {
            $this->log("Fixture {$fixtureName} already loaded, skipping");
            return $this->loadedFixtures[$fixtureName];
        }

        if (!isset($this->fixtureInstances[$fixtureName])) {
            throw new Exception("Fixture {$fixtureName} not found");
        }

        $fixture = $this->fixtureInstances[$fixtureName];

        // Load dependencies first
        foreach ($fixture->getDependencies() as $dependency) {
            if (!isset($this->loadedFixtures[$dependency])) {
                $this->log("Loading dependency: {$dependency} for {$fixtureName}");
                $this->loadFixture($dependency);
            }
        }

        // Load the fixture
        $data = $fixture->load();
        $this->loadedFixtures[$fixtureName] = $data;

        return $data;
    }

    /**
     * Calculate correct load order based on dependencies
     */
    private function calculateLoadOrder(): array
    {
        $order = [];
        $visited = [];
        $visiting = [];

        foreach (array_keys($this->fixtureInstances) as $fixtureName) {
            if (!isset($visited[$fixtureName])) {
                $this->visitFixture($fixtureName, $visited, $visiting, $order);
            }
        }

        return $order;
    }

    /**
     * Visit fixture for dependency resolution (topological sort)
     */
    private function visitFixture(string $fixtureName, array &$visited, array &$visiting, array &$order): void
    {
        if (isset($visiting[$fixtureName])) {
            throw new Exception("Circular dependency detected involving {$fixtureName}");
        }

        if (isset($visited[$fixtureName])) {
            return;
        }

        $visiting[$fixtureName] = true;
        $fixture = $this->fixtureInstances[$fixtureName];

        foreach ($fixture->getDependencies() as $dependency) {
            if (isset($this->fixtureInstances[$dependency])) {
                $this->visitFixture($dependency, $visited, $visiting, $order);
            } else {
                $this->log("Warning: Dependency {$dependency} not found for {$fixtureName}");
            }
        }

        unset($visiting[$fixtureName]);
        $visited[$fixtureName] = true;
        $order[] = $fixtureName;
    }

    /**
     * Clean up all loaded fixtures
     */
    public function cleanupAll(): void
    {
        $this->log('Starting cleanup process...');

        $startTime = microtime(true);
        $cleanupOrder = array_reverse($this->calculateLoadOrder());

        foreach ($cleanupOrder as $fixtureName) {
            if (isset($this->loadedFixtures[$fixtureName])) {
                try {
                    $this->log("Cleaning up fixture: {$fixtureName}");
                    $this->fixtureInstances[$fixtureName]->cleanup();
                    unset($this->loadedFixtures[$fixtureName]);
                } catch (Exception $e) {
                    $this->log("ERROR cleaning up fixture {$fixtureName}: " . $e->getMessage());
                }
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $this->log("Cleanup completed in {$totalTime}s");
    }

    /**
     * Clean up specific fixture
     */
    public function cleanupFixture(string $fixtureName): void
    {
        if (isset($this->fixtureInstances[$fixtureName])) {
            $this->fixtureInstances[$fixtureName]->cleanup();
            unset($this->loadedFixtures[$fixtureName]);
            $this->log("Cleaned up fixture: {$fixtureName}");
        }
    }

    /**
     * Get loaded fixture data
     */
    public function getFixtureData(string $fixtureName): ?array
    {
        return $this->loadedFixtures[$fixtureName] ?? null;
    }

    /**
     * Get all loaded fixture data
     */
    public function getAllFixtureData(): array
    {
        return $this->loadedFixtures;
    }

    /**
     * Get fixture instance
     */
    public function getFixtureInstance(string $fixtureName): ?BaseFixture
    {
        return $this->fixtureInstances[$fixtureName] ?? null;
    }

    /**
     * Check if fixture is loaded
     */
    public function isFixtureLoaded(string $fixtureName): bool
    {
        return isset($this->loadedFixtures[$fixtureName]);
    }

    /**
     * Get fixture loading statistics
     */
    public function getStats(): array
    {
        $stats = [
            'total_fixtures' => count($this->fixtureInstances),
            'loaded_fixtures' => count($this->loadedFixtures),
            'bulk_data_enabled' => $this->bulkDataEnabled,
            'fixture_details' => []
        ];

        foreach ($this->fixtureInstances as $name => $fixture) {
            $stats['fixture_details'][$name] = [
                'loaded' => isset($this->loadedFixtures[$name]),
                'dependencies' => $fixture->getDependencies(),
                'class' => get_class($fixture)
            ];

            if (isset($this->loadedFixtures[$name])) {
                $data = $this->loadedFixtures[$name];
                $stats['fixture_details'][$name]['data_keys'] = array_keys($data);
                $stats['fixture_details'][$name]['record_counts'] = [];

                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $stats['fixture_details'][$name]['record_counts'][$key] = count($value);
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Validate fixture integrity
     */
    public function validateIntegrity(): array
    {
        $issues = [];

        foreach ($this->fixtureInstances as $name => $fixture) {
            // Check dependencies exist
            foreach ($fixture->getDependencies() as $dependency) {
                if (!isset($this->fixtureInstances[$dependency])) {
                    $issues[] = "Fixture {$name} depends on non-existent fixture {$dependency}";
                }
            }

            // Check if loaded fixture has expected data
            if (isset($this->loadedFixtures[$name])) {
                $data = $this->loadedFixtures[$name];
                if (empty($data)) {
                    $issues[] = "Fixture {$name} loaded but produced no data";
                }
            }
        }

        return $issues;
    }

    /**
     * Export fixture data to JSON
     */
    public function exportToJson(string $filePath): bool
    {
        try {
            $exportData = [
                'exported_at' => date('Y-m-d H:i:s'),
                'bulk_data_enabled' => $this->bulkDataEnabled,
                'fixtures' => $this->loadedFixtures,
                'stats' => $this->getStats()
            ];

            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $result = file_put_contents($filePath, $json);

            if ($result !== false) {
                $this->log("Exported fixture data to: {$filePath}");
                return true;
            }
        } catch (Exception $e) {
            $this->log("Failed to export fixture data: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Create database snapshot
     */
    public function createSnapshot(string $snapshotName): bool
    {
        try {
            // This would create a database backup/snapshot
            $snapshotFile = __DIR__ . "/snapshots/{$snapshotName}_" . date('Y-m-d_H-i-s') . '.sql';
            $snapshotDir = dirname($snapshotFile);

            if (!is_dir($snapshotDir)) {
                mkdir($snapshotDir, 0755, true);
            }

            // Note: This is a placeholder - actual implementation would use mysqldump
            $this->log("Created database snapshot: {$snapshotFile}");
            return true;
        } catch (Exception $e) {
            $this->log("Failed to create snapshot: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load specific data sets
     */
    public function loadDataSet(string $setName): array
    {
        $dataSets = [
            'minimal' => ['EssentialDataFixture'],
            'basic' => ['EssentialDataFixture', 'PlansFixture', 'RouterFixture'],
            'standard' => ['EssentialDataFixture', 'PlansFixture', 'RouterFixture', 'ClientsFixture'],
            'complete' => self::FIXTURE_CLASSES,
            'testing' => self::FIXTURE_CLASSES // Complete with bulk data
        ];

        if (!isset($dataSets[$setName])) {
            throw new Exception("Unknown data set: {$setName}");
        }

        $results = [];
        foreach ($dataSets[$setName] as $fixtureName) {
            $results[$fixtureName] = $this->loadFixture($fixtureName);
        }

        return $results;
    }

    /**
     * Generate test report
     */
    public function generateReport(): string
    {
        $stats = $this->getStats();
        $issues = $this->validateIntegrity();

        $report = "# Fixture Manager Report\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";

        $report .= "## Summary\n";
        $report .= "- Total Fixtures: {$stats['total_fixtures']}\n";
        $report .= "- Loaded Fixtures: {$stats['loaded_fixtures']}\n";
        $report .= "- Bulk Data: " . ($stats['bulk_data_enabled'] ? 'Enabled' : 'Disabled') . "\n\n";

        $report .= "## Fixture Details\n";
        foreach ($stats['fixture_details'] as $name => $details) {
            $report .= "### {$name}\n";
            $report .= "- Status: " . ($details['loaded'] ? 'Loaded' : 'Not Loaded') . "\n";
            $report .= "- Dependencies: " . implode(', ', $details['dependencies']) . "\n";

            if (isset($details['record_counts'])) {
                $report .= "- Records:\n";
                foreach ($details['record_counts'] as $table => $count) {
                    $report .= "  - {$table}: {$count}\n";
                }
            }
            $report .= "\n";
        }

        if (!empty($issues)) {
            $report .= "## Issues Found\n";
            foreach ($issues as $issue) {
                $report .= "- {$issue}\n";
            }
            $report .= "\n";
        }

        return $report;
    }

    /**
     * Log message
     */
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";

        // Log to file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);

        // Also log to PHP error log if enabled
        if (defined('ENABLE_TEST_LOGGING') && ENABLE_TEST_LOGGING) {
            error_log("[FIXTURE MANAGER] {$message}");
        }
    }

    /**
     * Quick setup for common scenarios
     */
    public static function quickSetup(string $scenario = 'standard'): FixtureManager
    {
        $bulkData = ($scenario === 'performance' || $scenario === 'testing');
        $manager = new self($bulkData);

        switch ($scenario) {
            case 'minimal':
                $manager->loadDataSet('minimal');
                break;
            case 'development':
                $manager->loadDataSet('standard');
                break;
            case 'testing':
            case 'performance':
                $manager->loadDataSet('complete');
                break;
            default:
                $manager->loadDataSet('standard');
        }

        return $manager;
    }

    /**
     * Destructor - cleanup if needed
     */
    public function __destruct()
    {
        // Optional: Auto-cleanup on destruction
        // Uncomment if you want automatic cleanup
        // $this->cleanupAll();
    }
}