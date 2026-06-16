<?php

require_once __DIR__ . '/../../Support/BaseTestCase.php';
require_once __DIR__ . '/../../../Libraries/Core/Conexion.php';
require_once __DIR__ . '/../../../Libraries/Core/Mysql.php';
require_once __DIR__ . '/../../../Models/MunidashboardModel.php';

class MunidashboardModelTest extends BaseTestCase
{
    /**
     * @group critical
     */
    public function testBuildManagementKpiPayloadCalculatesCatalogKpisFromCurrentRows(): void
    {
        $model = new TestableMunidashboardModel();

        $payload = $model->buildManagementKpiPayload([
            [
                'id' => 1,
                'router_id' => 7,
                'department_id' => 10,
                'department_name' => 'Finanzas',
                'ip_range' => '10.10.0.10-10.10.0.20',
                'ip_address' => '10.10.0.11',
                'custom_upload' => '5M',
                'custom_download' => '10M',
                'queue_name' => 'muni-finanzas-1',
                'queue_sync_status' => 'synced',
                'status' => 1,
            ],
            [
                'id' => 2,
                'router_id' => 7,
                'department_id' => 10,
                'department_name' => 'Finanzas',
                'ip_range' => '10.10.0.10-10.10.0.20',
                'ip_address' => '10.10.0.30',
                'custom_upload' => '',
                'custom_download' => '10M',
                'queue_name' => '',
                'queue_sync_status' => 'pending',
                'status' => 1,
            ],
        ], 7);

        $this->assertEquals(1, $payload['kpis']['assigned_service']['value']);
        $this->assertEquals(1, $payload['kpis']['departments_attention']['value']);
        $this->assertEquals(50, $payload['kpis']['ip_compliance']['percent']);
        $this->assertEquals(50, $payload['kpis']['queue_sync_compliance']['percent']);
        $this->assertEquals('catalog', $payload['kpis']['ip_compliance']['evidence']);
        $this->assertEquals('unavailable', $payload['source']['router']);
    }

    /**
     * @group edge-cases
     */
    public function testBuildManagementKpiPayloadUsesInsufficientEvidenceForMalformedIpRange(): void
    {
        $model = new TestableMunidashboardModel();

        $payload = $model->buildManagementKpiPayload([
            [
                'id' => 3,
                'router_id' => 7,
                'department_id' => 20,
                'department_name' => 'Archivo',
                'ip_range' => 'not-a-range',
                'ip_address' => '10.20.0.11',
                'custom_upload' => '5M',
                'custom_download' => '10M',
                'queue_name' => 'muni-archivo-1',
                'queue_sync_status' => 'synced',
                'status' => 1,
            ],
        ], 7);

        $this->assertEquals(null, $payload['kpis']['ip_compliance']['percent']);
        $this->assertEquals('Sin información suficiente', $payload['kpis']['ip_compliance']['value']);
        $this->assertEquals(0, $payload['kpis']['departments_attention']['value']);
        $this->assertEquals(1, $payload['metadata']['insufficient_ip_evidence']);
    }

    /**
     * @group business-logic
     */
    public function testGetManagementKpiSummaryFiltersRowsByRouterUsingPreparedSource(): void
    {
        $model = new TestableMunidashboardModel([
            ['id' => 1, 'router_id' => 7, 'ip_address' => '10.7.0.10', 'custom_upload' => '5M', 'custom_download' => '10M', 'queue_name' => 'r7', 'queue_sync_status' => 'synced', 'status' => 1],
            ['id' => 2, 'router_id' => 8, 'ip_address' => '10.8.0.10', 'custom_upload' => '5M', 'custom_download' => '10M', 'queue_name' => 'r8', 'queue_sync_status' => 'synced', 'status' => 1],
        ]);

        $payload = $model->getManagementKpiSummary(7);

        $this->assertEquals(7, $payload['router_id']);
        $this->assertEquals([7], $model->requestedRouterIds);
        $this->assertEquals(1, $payload['metadata']['active_users']);
        $this->assertEquals(1, $payload['kpis']['assigned_service']['value']);
    }

    /**
     * @group critical
     */
    public function testMergeManagementKpisWithBandwidthUsesExplicitCurrentRateFieldsOnly(): void
    {
        $model = new TestableMunidashboardModel();
        $payload = $model->buildManagementKpiPayload([], 7);

        $merged = $model->mergeManagementKpisWithBandwidth($payload, [
            ['user_id' => 1, 'download_rate' => 0, 'upload_rate' => 0, 'disabled' => false],
            ['user_id' => 2, 'download_rate' => 2048, 'upload_rate' => 512, 'disabled' => false],
            ['user_id' => 3, 'download_rate' => 4096, 'upload_rate' => 1024, 'disabled' => true],
        ]);

        $this->assertEquals('available', $merged['source']['router']);
        $this->assertEquals(1, $merged['kpis']['observed_consumption']['value']);
        $this->assertEquals('current_only', $merged['kpis']['observed_consumption']['evidence']);
        $this->assertFalse($merged['metadata']['uses_generated_history']);
    }

    /**
     * @group critical
     */
    public function testMergeManagementKpisWithBandwidthDoesNotTreatCumulativeBytesAsCurrentConsumption(): void
    {
        $model = new TestableMunidashboardModel();
        $payload = $model->buildManagementKpiPayload([], 7);

        $merged = $model->mergeManagementKpisWithBandwidth($payload, [
            ['user_id' => 1, 'download_bytes' => 2048, 'upload_bytes' => 512, 'disabled' => false],
            ['user_id' => 2, 'download_bytes' => 4096, 'upload_bytes' => 1024, 'disabled' => true],
        ]);

        $this->assertEquals('available', $merged['source']['router']);
        $this->assertEquals('Sin información suficiente', $merged['kpis']['observed_consumption']['value']);
        $this->assertEquals('current_only', $merged['kpis']['observed_consumption']['evidence']);
        $this->assertEquals(null, $merged['metadata']['observed_consumption_queues']);
        $this->assertEquals('current_rate_unavailable', $merged['metadata']['observed_consumption_unavailable_reason']);
        $this->assertFalse($merged['metadata']['uses_generated_history']);
    }

    public static function runStandalone(): int
    {
        $failed = 0;
        foreach (get_class_methods(__CLASS__) as $method) {
            if (strpos($method, 'test') !== 0) {
                continue;
            }

            $test = new self();
            try {
                $ref = new ReflectionMethod($test, $method);
                $ref->invoke($test);
                echo __CLASS__ . "::$method PASSED\n";
            } catch (Throwable $e) {
                $failed++;
                echo __CLASS__ . "::$method FAILED: " . $e->getMessage() . "\n";
            }
        }

        return $failed === 0 ? 0 : 1;
    }
}

class TestableMunidashboardModel extends MunidashboardModel
{
    private array $rows;
    public array $requestedRouterIds = [];

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    protected function fetchManagementUsers(?int $routerId): array
    {
        $this->requestedRouterIds[] = $routerId;

        return array_values(array_filter($this->rows, function (array $row) use ($routerId): bool {
            return $routerId === null || (int) ($row['router_id'] ?? 0) === $routerId;
        }));
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit(MunidashboardModelTest::runStandalone());
}
