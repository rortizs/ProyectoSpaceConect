<?php
/**
 * MikroTik Integration Test Validation Script
 *
 * Validates that all MikroTik integration test files are properly structured,
 * dependencies are met, and tests can be executed successfully.
 */

require_once __DIR__ . '/bootstrap.php';

class MikroTikTestValidator
{
    private $testDirectory;
    private $validationResults;

    public function __construct()
    {
        $this->testDirectory = __DIR__ . '/Integration/MikroTik';
        $this->validationResults = [];
    }

    /**
     * Run comprehensive validation
     */
    public function validateAll(): bool
    {
        echo "MikroTik Integration Test Validation\n";
        echo str_repeat("=", 50) . "\n\n";

        $allValid = true;

        $allValid &= $this->validateEnvironment();
        $allValid &= $this->validateTestFiles();
        $allValid &= $this->validateDependencies();
        $allValid &= $this->validateTestStructure();
        $allValid &= $this->validateBootstrap();

        $this->printSummary($allValid);

        return $allValid;
    }

    /**
     * Validate environment setup
     */
    private function validateEnvironment(): bool
    {
        echo "Validating Environment...\n";

        $checks = [
            'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'Test Directory Exists' => is_dir($this->testDirectory),
            'Bootstrap File Exists' => file_exists(__DIR__ . '/bootstrap.php'),
            'Config File Exists' => file_exists(__DIR__ . '/config/test_config.php'),
            'PHPUnit Config Exists' => file_exists(__DIR__ . '/phpunit.xml'),
            'Support Directory Exists' => is_dir(__DIR__ . '/Support'),
            'Logs Directory Writable' => is_writable(__DIR__ . '/logs') || mkdir(__DIR__ . '/logs', 0755, true)
        ];

        $allValid = true;
        foreach ($checks as $check => $result) {
            $status = $result ? '✅' : '❌';
            echo "  $check: $status\n";
            if (!$result) {
                $allValid = false;
            }
        }

        echo "\n";
        return $allValid;
    }

    /**
     * Validate test files exist and are readable
     */
    private function validateTestFiles(): bool
    {
        echo "Validating Test Files...\n";

        $expectedFiles = [
            'RouterConnectionTest.php',
            'ClientProvisioningTest.php',
            'ContentFilteringTest.php',
            'ClientLifecycleTest.php',
            'BandwidthManagementTest.php',
            'NetworkSecurityTest.php',
            'ErrorHandlingAndFailoverTest.php',
            'PerformanceAndMonitoringTest.php'
        ];

        $allValid = true;
        foreach ($expectedFiles as $file) {
            $filePath = $this->testDirectory . '/' . $file;
            $exists = file_exists($filePath);
            $readable = $exists && is_readable($filePath);
            $validPHP = $exists && $this->validatePHPSyntax($filePath);

            $status = ($exists && $readable && $validPHP) ? '✅' : '❌';
            echo "  $file: $status";

            if (!$exists) {
                echo " (Not found)";
                $allValid = false;
            } elseif (!$readable) {
                echo " (Not readable)";
                $allValid = false;
            } elseif (!$validPHP) {
                echo " (Syntax error)";
                $allValid = false;
            }

            echo "\n";
        }

        echo "\n";
        return $allValid;
    }

    /**
     * Validate PHP syntax of a file
     */
    private function validatePHPSyntax(string $filePath): bool
    {
        $output = [];
        $returnCode = 0;
        exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Validate dependencies
     */
    private function validateDependencies(): bool
    {
        echo "Validating Dependencies...\n";

        $checks = [
            'PHPUnit Available' => $this->isPHPUnitAvailable(),
            'MikroTik Libraries' => $this->validateMikroTikLibraries(),
            'Support Classes' => $this->validateSupportClasses(),
            'Required Extensions' => $this->validateRequiredExtensions()
        ];

        $allValid = true;
        foreach ($checks as $check => $result) {
            $status = $result ? '✅' : '❌';
            echo "  $check: $status\n";
            if (!$result) {
                $allValid = false;
            }
        }

        echo "\n";
        return $allValid;
    }

    /**
     * Check if PHPUnit is available
     */
    private function isPHPUnitAvailable(): bool
    {
        $possiblePaths = [
            '/usr/local/bin/phpunit',
            '/usr/bin/phpunit',
            __DIR__ . '/../vendor/bin/phpunit'
        ];

        foreach ($possiblePaths as $path) {
            if (is_executable($path)) {
                return true;
            }
        }

        // Try which command
        $which = shell_exec('which phpunit 2>/dev/null');
        $which = $which ? trim($which) : '';
        return !empty($which) && is_executable($which);
    }

    /**
     * Validate MikroTik library files exist
     */
    private function validateMikroTikLibraries(): bool
    {
        $libraryPath = __DIR__ . '/../Libraries/MikroTik';
        $requiredFiles = [
            'RouterFactory.php',
            'Router.php',
            'RouterLegacy.php'
        ];

        foreach ($requiredFiles as $file) {
            if (!file_exists($libraryPath . '/' . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate support classes exist
     */
    private function validateSupportClasses(): bool
    {
        $supportPath = __DIR__ . '/Support';
        $requiredFiles = [
            'BaseTestCase.php',
            'MikroTikTestCase.php',
            'DatabaseTestCase.php'
        ];

        foreach ($requiredFiles as $file) {
            if (!file_exists($supportPath . '/' . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate required PHP extensions
     */
    private function validateRequiredExtensions(): bool
    {
        $requiredExtensions = [
            'mysqli',
            'curl',
            'json',
            'mbstring'
        ];

        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate test structure and class definitions
     */
    private function validateTestStructure(): bool
    {
        echo "Validating Test Structure...\n";

        $testFiles = glob($this->testDirectory . '/*Test.php');
        $allValid = true;

        foreach ($testFiles as $testFile) {
            $fileName = basename($testFile);
            $className = str_replace('.php', '', $fileName);

            // Check if file contains class definition
            $content = file_get_contents($testFile);
            $hasClass = strpos($content, "class $className") !== false;
            $extendsTestCase = strpos($content, 'extends MikroTikTestCase') !== false;
            $hasTestMethods = preg_match('/public function test\w+\(\)/', $content);

            $status = ($hasClass && $extendsTestCase && $hasTestMethods) ? '✅' : '❌';
            echo "  $fileName: $status";

            if (!$hasClass) {
                echo " (Missing class definition)";
                $allValid = false;
            } elseif (!$extendsTestCase) {
                echo " (Doesn't extend MikroTikTestCase)";
                $allValid = false;
            } elseif (!$hasTestMethods) {
                echo " (No test methods found)";
                $allValid = false;
            }

            echo "\n";
        }

        echo "\n";
        return $allValid;
    }

    /**
     * Validate bootstrap functionality
     */
    private function validateBootstrap(): bool
    {
        echo "Validating Bootstrap...\n";

        try {
            // Test if bootstrap can be loaded without errors
            ob_start();
            $bootstrapPath = __DIR__ . '/bootstrap.php';
            include_once $bootstrapPath;
            $output = ob_get_clean();

            // Check if required constants are defined
            $checks = [
                'TESTING_MODE defined' => defined('TESTING_MODE'),
                'Test config loaded' => defined('DB_HOST_TEST') || defined('MIKROTIK_TEST_HOST'),
                'Autoloader available' => class_exists('BaseTestCase', false) || function_exists('spl_autoload_register'),
                'Database class available' => class_exists('Mysql', false)
            ];

            $allValid = true;
            foreach ($checks as $check => $result) {
                $status = $result ? '✅' : '❌';
                echo "  $check: $status\n";
                if (!$result) {
                    $allValid = false;
                }
            }

            echo "\n";
            return $allValid;

        } catch (Exception $e) {
            echo "  Bootstrap loading: ❌ (Error: " . $e->getMessage() . ")\n\n";
            return false;
        }
    }

    /**
     * Print validation summary
     */
    private function printSummary(bool $allValid): void
    {
        echo str_repeat("=", 50) . "\n";
        echo "VALIDATION SUMMARY\n";
        echo str_repeat("=", 50) . "\n";

        if ($allValid) {
            echo "✅ All validations passed! The MikroTik integration test suite is ready to run.\n\n";
            echo "Next steps:\n";
            echo "1. Run all tests: php run_mikrotik_tests.php\n";
            echo "2. Run specific suite: php run_mikrotik_tests.php --suite=RouterConnection\n";
            echo "3. Run by group: php run_mikrotik_tests.php --group=security\n";
        } else {
            echo "❌ Some validations failed. Please fix the issues above before running tests.\n\n";
            echo "Common fixes:\n";
            echo "1. Install PHPUnit: composer require --dev phpunit/phpunit\n";
            echo "2. Install required extensions: php-mysqli, php-curl, php-json\n";
            echo "3. Check file permissions and paths\n";
            echo "4. Verify MikroTik library files exist\n";
        }

        echo "\n";
    }

    /**
     * Generate diagnostic report
     */
    public function generateDiagnosticReport(): void
    {
        $reportFile = __DIR__ . '/logs/validation_report.txt';

        $report = [];
        $report[] = "MikroTik Integration Test Validation Report";
        $report[] = str_repeat("=", 50);
        $report[] = "Generated: " . date('Y-m-d H:i:s');
        $report[] = "PHP Version: " . PHP_VERSION;
        $report[] = "System: " . php_uname();
        $report[] = "";

        // Environment info
        $report[] = "ENVIRONMENT:";
        $report[] = "Test Directory: " . $this->testDirectory;
        $report[] = "Bootstrap File: " . (__DIR__ . '/bootstrap.php');
        $report[] = "Working Directory: " . getcwd();
        $report[] = "";

        // Extensions
        $report[] = "LOADED EXTENSIONS:";
        $extensions = get_loaded_extensions();
        sort($extensions);
        foreach ($extensions as $ext) {
            $report[] = "  - $ext";
        }
        $report[] = "";

        // File listing
        $report[] = "TEST FILES:";
        $testFiles = glob($this->testDirectory . '/*.php');
        foreach ($testFiles as $file) {
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            $report[] = "  - " . basename($file) . " ($size bytes, modified: $modified)";
        }

        if (!file_exists(dirname($reportFile))) {
            mkdir(dirname($reportFile), 0755, true);
        }

        file_put_contents($reportFile, implode("\n", $report));
        echo "Diagnostic report generated: $reportFile\n";
    }
}

// Run validation if executed directly
if (php_sapi_name() === 'cli') {
    $validator = new MikroTikTestValidator();

    $options = getopt('', ['report', 'help']);

    if (isset($options['help'])) {
        echo "MikroTik Integration Test Validator\n\n";
        echo "Usage:\n";
        echo "  php validate_mikrotik_tests.php           Run validation\n";
        echo "  php validate_mikrotik_tests.php --report  Generate diagnostic report\n";
        echo "  php validate_mikrotik_tests.php --help    Show this help\n";
        exit(0);
    }

    $isValid = $validator->validateAll();

    if (isset($options['report'])) {
        $validator->generateDiagnosticReport();
    }

    exit($isValid ? 0 : 1);
}