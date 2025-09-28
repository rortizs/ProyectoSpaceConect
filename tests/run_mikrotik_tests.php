<?php
/**
 * MikroTik Integration Test Runner
 *
 * Comprehensive test runner for MikroTik router integration tests.
 * Executes all integration test suites and generates detailed reports.
 */

require_once __DIR__ . '/bootstrap.php';

class MikroTikTestRunner
{
    private $testSuites;
    private $results;
    private $startTime;
    private $outputDir;

    public function __construct()
    {
        $this->testSuites = [
            'RouterConnection' => [
                'file' => 'Integration/MikroTik/RouterConnectionTest.php',
                'description' => 'Router connection, authentication, and RouterFactory functionality',
                'groups' => ['connection']
            ],
            'ClientProvisioning' => [
                'file' => 'Integration/MikroTik/ClientProvisioningTest.php',
                'description' => 'Complete client provisioning workflow including PPPoE and Queue management',
                'groups' => ['provisioning']
            ],
            'ContentFiltering' => [
                'file' => 'Integration/MikroTik/ContentFilteringTest.php',
                'description' => 'DNS blocking, firewall rules, and content filtering policies',
                'groups' => ['content-filtering']
            ],
            'ClientLifecycle' => [
                'file' => 'Integration/MikroTik/ClientLifecycleTest.php',
                'description' => 'Client activation, suspension, restoration, and disconnection workflows',
                'groups' => ['lifecycle']
            ],
            'BandwidthManagement' => [
                'file' => 'Integration/MikroTik/BandwidthManagementTest.php',
                'description' => 'Queue management, QoS, and bandwidth allocation',
                'groups' => ['bandwidth']
            ],
            'NetworkSecurity' => [
                'file' => 'Integration/MikroTik/NetworkSecurityTest.php',
                'description' => 'Firewall rules, access control, and network security policies',
                'groups' => ['security']
            ],
            'ErrorHandlingAndFailover' => [
                'file' => 'Integration/MikroTik/ErrorHandlingAndFailoverTest.php',
                'description' => 'Error handling, failover scenarios, and recovery mechanisms',
                'groups' => ['error-handling']
            ],
            'PerformanceAndMonitoring' => [
                'file' => 'Integration/MikroTik/PerformanceAndMonitoringTest.php',
                'description' => 'Performance metrics, monitoring, and load testing',
                'groups' => ['performance', 'monitoring']
            ]
        ];

        $this->results = [];
        $this->outputDir = __DIR__ . '/logs/mikrotik_tests_' . date('Y-m-d_H-i-s');

        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Run all MikroTik integration tests
     */
    public function runAllTests()
    {
        $this->startTime = microtime(true);

        $this->printHeader();
        $this->runTestSuites();
        $this->generateReports();
        $this->printSummary();
    }

    /**
     * Run specific test suite
     */
    public function runTestSuite(string $suiteName)
    {
        if (!isset($this->testSuites[$suiteName])) {
            echo "Error: Test suite '$suiteName' not found.\n";
            return false;
        }

        $this->startTime = microtime(true);

        echo "Running MikroTik Integration Test Suite: $suiteName\n";
        echo str_repeat("=", 80) . "\n\n";

        $result = $this->executeTestSuite($suiteName, $this->testSuites[$suiteName]);
        $this->results[$suiteName] = $result;

        $this->printTestSuiteResult($suiteName, $result);
        $this->generateTestSuiteReport($suiteName, $result);

        return $result['success'];
    }

    /**
     * Run tests by group
     */
    public function runTestGroup(string $groupName)
    {
        $this->startTime = microtime(true);

        echo "Running MikroTik Integration Tests - Group: $groupName\n";
        echo str_repeat("=", 80) . "\n\n";

        $groupSuites = [];
        foreach ($this->testSuites as $suiteName => $suiteInfo) {
            if (in_array($groupName, $suiteInfo['groups'])) {
                $groupSuites[$suiteName] = $suiteInfo;
            }
        }

        if (empty($groupSuites)) {
            echo "Error: No test suites found for group '$groupName'.\n";
            return false;
        }

        foreach ($groupSuites as $suiteName => $suiteInfo) {
            $result = $this->executeTestSuite($suiteName, $suiteInfo);
            $this->results[$suiteName] = $result;
            $this->printTestSuiteResult($suiteName, $result);
        }

        $this->generateReports();
        $this->printSummary();
    }

    /**
     * Print test runner header
     */
    private function printHeader()
    {
        echo "\n";
        echo str_repeat("=", 100) . "\n";
        echo "                          MIKROTIK INTEGRATION TEST SUITE\n";
        echo "                     Comprehensive Router Integration Testing\n";
        echo str_repeat("=", 100) . "\n";
        echo "Start Time: " . date('Y-m-d H:i:s') . "\n";
        echo "Test Suites: " . count($this->testSuites) . "\n";
        echo "Output Directory: " . $this->outputDir . "\n";
        echo str_repeat("=", 100) . "\n\n";
    }

    /**
     * Run all test suites
     */
    private function runTestSuites()
    {
        foreach ($this->testSuites as $suiteName => $suiteInfo) {
            echo "Running Test Suite: $suiteName\n";
            echo "Description: " . $suiteInfo['description'] . "\n";
            echo "Groups: " . implode(', ', $suiteInfo['groups']) . "\n";
            echo str_repeat("-", 80) . "\n";

            $result = $this->executeTestSuite($suiteName, $suiteInfo);
            $this->results[$suiteName] = $result;

            $this->printTestSuiteResult($suiteName, $result);
            echo "\n";
        }
    }

    /**
     * Execute a single test suite
     */
    private function executeTestSuite(string $suiteName, array $suiteInfo): array
    {
        $startTime = microtime(true);

        try {
            // Use PHPUnit to run the specific test file
            $testFile = __DIR__ . '/' . $suiteInfo['file'];

            if (!file_exists($testFile)) {
                throw new Exception("Test file not found: $testFile");
            }

            // Build PHPUnit command
            $phpunitPath = $this->findPHPUnit();
            $configFile = __DIR__ . '/phpunit.xml';

            $command = sprintf(
                '%s --configuration %s --testdox --colors=always %s 2>&1',
                $phpunitPath,
                escapeshellarg($configFile),
                escapeshellarg($testFile)
            );

            // Execute the test
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            // Parse results
            $testResults = $this->parseTestOutput($output);

            return [
                'success' => $returnCode === 0,
                'duration' => $duration,
                'output' => $output,
                'tests_run' => $testResults['tests_run'],
                'tests_passed' => $testResults['tests_passed'],
                'tests_failed' => $testResults['tests_failed'],
                'tests_skipped' => $testResults['tests_skipped'],
                'failures' => $testResults['failures'],
                'errors' => $testResults['errors']
            ];

        } catch (Exception $e) {
            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            return [
                'success' => false,
                'duration' => $duration,
                'output' => ["Error: " . $e->getMessage()],
                'tests_run' => 0,
                'tests_passed' => 0,
                'tests_failed' => 1,
                'tests_skipped' => 0,
                'failures' => [$e->getMessage()],
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Find PHPUnit executable
     */
    private function findPHPUnit(): string
    {
        $possiblePaths = [
            '/usr/local/bin/phpunit',
            '/usr/bin/phpunit',
            __DIR__ . '/../vendor/bin/phpunit',
            'phpunit' // Global installation
        ];

        foreach ($possiblePaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // Try which command
        $which = trim(shell_exec('which phpunit 2>/dev/null'));
        if (!empty($which) && is_executable($which)) {
            return $which;
        }

        throw new Exception('PHPUnit not found. Please install PHPUnit or add it to your PATH.');
    }

    /**
     * Parse test output to extract results
     */
    private function parseTestOutput(array $output): array
    {
        $results = [
            'tests_run' => 0,
            'tests_passed' => 0,
            'tests_failed' => 0,
            'tests_skipped' => 0,
            'failures' => [],
            'errors' => []
        ];

        foreach ($output as $line) {
            // Parse PHPUnit result line
            if (preg_match('/Tests: (\d+), Assertions: \d+/', $line, $matches)) {
                $results['tests_run'] = (int)$matches[1];
            }

            if (preg_match('/(\d+) passed/', $line, $matches)) {
                $results['tests_passed'] = (int)$matches[1];
            }

            if (preg_match('/(\d+) failed/', $line, $matches)) {
                $results['tests_failed'] = (int)$matches[1];
            }

            if (preg_match('/(\d+) skipped/', $line, $matches)) {
                $results['tests_skipped'] = (int)$matches[1];
            }

            // Collect failures and errors
            if (strpos($line, 'FAIL') !== false || strpos($line, 'ERROR') !== false) {
                $results['failures'][] = $line;
            }
        }

        // If no explicit counts found, estimate from test run
        if ($results['tests_run'] > 0 && $results['tests_passed'] === 0 && $results['tests_failed'] === 0) {
            $results['tests_passed'] = $results['tests_run'] - count($results['failures']);
            $results['tests_failed'] = count($results['failures']);
        }

        return $results;
    }

    /**
     * Print test suite result
     */
    private function printTestSuiteResult(string $suiteName, array $result)
    {
        $status = $result['success'] ? '✅ PASSED' : '❌ FAILED';
        $duration = number_format($result['duration'], 2);

        echo "Result: $status\n";
        echo "Duration: {$duration}s\n";
        echo "Tests Run: {$result['tests_run']}\n";
        echo "Passed: {$result['tests_passed']}\n";
        echo "Failed: {$result['tests_failed']}\n";
        echo "Skipped: {$result['tests_skipped']}\n";

        if (!empty($result['failures'])) {
            echo "Failures:\n";
            foreach ($result['failures'] as $failure) {
                echo "  - $failure\n";
            }
        }
    }

    /**
     * Generate comprehensive test reports
     */
    private function generateReports()
    {
        $this->generateSummaryReport();
        $this->generateDetailedReport();
        $this->generateJUnitReport();
        $this->generateCoverageReport();
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport()
    {
        $reportFile = $this->outputDir . '/summary_report.txt';

        $totalDuration = microtime(true) - $this->startTime;
        $totalTests = array_sum(array_column($this->results, 'tests_run'));
        $totalPassed = array_sum(array_column($this->results, 'tests_passed'));
        $totalFailed = array_sum(array_column($this->results, 'tests_failed'));
        $totalSkipped = array_sum(array_column($this->results, 'tests_skipped'));
        $successRate = $totalTests > 0 ? ($totalPassed / $totalTests) * 100 : 0;

        $report = [];
        $report[] = "MIKROTIK INTEGRATION TEST SUMMARY REPORT";
        $report[] = str_repeat("=", 50);
        $report[] = "Generated: " . date('Y-m-d H:i:s');
        $report[] = "Total Duration: " . number_format($totalDuration, 2) . "s";
        $report[] = "";
        $report[] = "OVERALL RESULTS:";
        $report[] = "Total Tests: $totalTests";
        $report[] = "Passed: $totalPassed";
        $report[] = "Failed: $totalFailed";
        $report[] = "Skipped: $totalSkipped";
        $report[] = "Success Rate: " . number_format($successRate, 2) . "%";
        $report[] = "";
        $report[] = "TEST SUITE BREAKDOWN:";

        foreach ($this->results as $suiteName => $result) {
            $status = $result['success'] ? 'PASSED' : 'FAILED';
            $duration = number_format($result['duration'], 2);
            $report[] = sprintf("%-25s %s (%ss)", $suiteName, $status, $duration);
        }

        file_put_contents($reportFile, implode("\n", $report));
        echo "Summary report generated: $reportFile\n";
    }

    /**
     * Generate detailed report
     */
    private function generateDetailedReport()
    {
        $reportFile = $this->outputDir . '/detailed_report.html';

        $html = $this->generateHTMLReport();
        file_put_contents($reportFile, $html);
        echo "Detailed HTML report generated: $reportFile\n";
    }

    /**
     * Generate JUnit XML report
     */
    private function generateJUnitReport()
    {
        $reportFile = $this->outputDir . '/junit_report.xml';

        $xml = $this->generateJUnitXML();
        file_put_contents($reportFile, $xml);
        echo "JUnit XML report generated: $reportFile\n";
    }

    /**
     * Generate coverage report placeholder
     */
    private function generateCoverageReport()
    {
        $reportFile = $this->outputDir . '/coverage_summary.txt';

        $coverage = [
            "CODE COVERAGE SUMMARY",
            str_repeat("=", 30),
            "Note: Code coverage requires Xdebug or PCOV extension",
            "To enable coverage, run with: php -d xdebug.mode=coverage",
            "",
            "Covered Components:",
            "- RouterFactory: Connection management and API detection",
            "- Router Classes: Both legacy and REST API implementations",
            "- ContentFilterService: Content filtering operations",
            "- Network Controller: Router management endpoints",
            "- Client Lifecycle: Provisioning and lifecycle management",
            "",
            "Integration Points Tested:",
            "- Database operations with router configurations",
            "- Error handling and failover scenarios",
            "- Performance under load conditions",
            "- Security policy enforcement"
        ];

        file_put_contents($reportFile, implode("\n", $coverage));
        echo "Coverage summary generated: $reportFile\n";
    }

    /**
     * Generate HTML report
     */
    private function generateHTMLReport(): string
    {
        $totalTests = array_sum(array_column($this->results, 'tests_run'));
        $totalPassed = array_sum(array_column($this->results, 'tests_passed'));
        $totalFailed = array_sum(array_column($this->results, 'tests_failed'));
        $successRate = $totalTests > 0 ? ($totalPassed / $totalTests) * 100 : 0;

        $html = '<!DOCTYPE html>
<html>
<head>
    <title>MikroTik Integration Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        .summary { background: #e8f5e8; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .suite { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .passed { background: #d4edda; }
        .failed { background: #f8d7da; }
        .metrics { display: flex; gap: 20px; }
        .metric { text-align: center; padding: 10px; background: #f8f9fa; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MikroTik Integration Test Report</h1>
        <p>Generated: ' . date('Y-m-d H:i:s') . '</p>
    </div>

    <div class="summary">
        <h2>Test Summary</h2>
        <div class="metrics">
            <div class="metric">
                <h3>' . $totalTests . '</h3>
                <p>Total Tests</p>
            </div>
            <div class="metric">
                <h3>' . $totalPassed . '</h3>
                <p>Passed</p>
            </div>
            <div class="metric">
                <h3>' . $totalFailed . '</h3>
                <p>Failed</p>
            </div>
            <div class="metric">
                <h3>' . number_format($successRate, 1) . '%</h3>
                <p>Success Rate</p>
            </div>
        </div>
    </div>';

        foreach ($this->results as $suiteName => $result) {
            $suiteClass = $result['success'] ? 'suite passed' : 'suite failed';
            $status = $result['success'] ? '✅ PASSED' : '❌ FAILED';

            $html .= "<div class=\"$suiteClass\">
                <h3>$suiteName $status</h3>
                <p><strong>Description:</strong> " . $this->testSuites[$suiteName]['description'] . "</p>
                <p><strong>Duration:</strong> " . number_format($result['duration'], 2) . "s</p>
                <p><strong>Tests:</strong> {$result['tests_run']} |
                   <strong>Passed:</strong> {$result['tests_passed']} |
                   <strong>Failed:</strong> {$result['tests_failed']}</p>";

            if (!empty($result['failures'])) {
                $html .= "<h4>Failures:</h4><ul>";
                foreach ($result['failures'] as $failure) {
                    $html .= "<li>" . htmlspecialchars($failure) . "</li>";
                }
                $html .= "</ul>";
            }

            $html .= "</div>";
        }

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Generate JUnit XML
     */
    private function generateJUnitXML(): string
    {
        $totalTests = array_sum(array_column($this->results, 'tests_run'));
        $totalFailures = array_sum(array_column($this->results, 'tests_failed'));
        $totalTime = array_sum(array_column($this->results, 'duration'));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= sprintf(
            '<testsuites name="MikroTik Integration Tests" tests="%d" failures="%d" time="%.2f">' . "\n",
            $totalTests,
            $totalFailures,
            $totalTime
        );

        foreach ($this->results as $suiteName => $result) {
            $xml .= sprintf(
                '  <testsuite name="%s" tests="%d" failures="%d" time="%.2f">' . "\n",
                $suiteName,
                $result['tests_run'],
                $result['tests_failed'],
                $result['duration']
            );

            for ($i = 0; $i < $result['tests_run']; $i++) {
                $testName = "test_" . ($i + 1);
                $xml .= "    <testcase name=\"$testName\" classname=\"$suiteName\"";

                if ($i < $result['tests_failed'] && !empty($result['failures'])) {
                    $failure = isset($result['failures'][$i]) ? $result['failures'][$i] : 'Test failed';
                    $xml .= ">\n      <failure>" . htmlspecialchars($failure) . "</failure>\n    </testcase>\n";
                } else {
                    $xml .= "/>\n";
                }
            }

            $xml .= "  </testsuite>\n";
        }

        $xml .= "</testsuites>\n";
        return $xml;
    }

    /**
     * Print final summary
     */
    private function printSummary()
    {
        $totalDuration = microtime(true) - $this->startTime;
        $totalTests = array_sum(array_column($this->results, 'tests_run'));
        $totalPassed = array_sum(array_column($this->results, 'tests_passed'));
        $totalFailed = array_sum(array_column($this->results, 'tests_failed'));
        $successRate = $totalTests > 0 ? ($totalPassed / $totalTests) * 100 : 0;
        $overallSuccess = $totalFailed === 0;

        echo "\n" . str_repeat("=", 100) . "\n";
        echo "                               FINAL SUMMARY\n";
        echo str_repeat("=", 100) . "\n";
        echo "Overall Result: " . ($overallSuccess ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED') . "\n";
        echo "Total Duration: " . number_format($totalDuration, 2) . "s\n";
        echo "Test Suites: " . count($this->results) . "\n";
        echo "Total Tests: $totalTests\n";
        echo "Passed: $totalPassed\n";
        echo "Failed: $totalFailed\n";
        echo "Success Rate: " . number_format($successRate, 2) . "%\n";
        echo "Reports Generated: " . $this->outputDir . "\n";
        echo str_repeat("=", 100) . "\n\n";

        if (!$overallSuccess) {
            echo "Failed Test Suites:\n";
            foreach ($this->results as $suiteName => $result) {
                if (!$result['success']) {
                    echo "  - $suiteName\n";
                }
            }
            echo "\n";
        }
    }

    /**
     * Generate test suite specific report
     */
    private function generateTestSuiteReport(string $suiteName, array $result)
    {
        $reportFile = $this->outputDir . "/{$suiteName}_report.txt";

        $report = [];
        $report[] = "TEST SUITE REPORT: $suiteName";
        $report[] = str_repeat("=", 50);
        $report[] = "Description: " . $this->testSuites[$suiteName]['description'];
        $report[] = "Groups: " . implode(', ', $this->testSuites[$suiteName]['groups']);
        $report[] = "Duration: " . number_format($result['duration'], 2) . "s";
        $report[] = "Status: " . ($result['success'] ? 'PASSED' : 'FAILED');
        $report[] = "";
        $report[] = "DETAILED OUTPUT:";
        $report[] = implode("\n", $result['output']);

        file_put_contents($reportFile, implode("\n", $report));
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $runner = new MikroTikTestRunner();

    $options = getopt('', ['suite:', 'group:', 'help']);

    if (isset($options['help'])) {
        echo "MikroTik Integration Test Runner\n\n";
        echo "Usage:\n";
        echo "  php run_mikrotik_tests.php                 Run all test suites\n";
        echo "  php run_mikrotik_tests.php --suite=NAME    Run specific test suite\n";
        echo "  php run_mikrotik_tests.php --group=NAME    Run specific test group\n";
        echo "  php run_mikrotik_tests.php --help          Show this help\n\n";
        echo "Available Test Suites:\n";
        foreach ($runner->testSuites as $name => $info) {
            echo "  - $name: " . $info['description'] . "\n";
        }
        echo "\nAvailable Groups:\n";
        $allGroups = [];
        foreach ($runner->testSuites as $info) {
            $allGroups = array_merge($allGroups, $info['groups']);
        }
        foreach (array_unique($allGroups) as $group) {
            echo "  - $group\n";
        }
        exit(0);
    }

    if (isset($options['suite'])) {
        $runner->runTestSuite($options['suite']);
    } elseif (isset($options['group'])) {
        $runner->runTestGroup($options['group']);
    } else {
        $runner->runAllTests();
    }
}