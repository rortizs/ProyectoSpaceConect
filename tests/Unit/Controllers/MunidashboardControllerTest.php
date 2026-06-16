<?php

require_once __DIR__ . '/BaseControllerTest.php';
require_once __DIR__ . '/../../../Libraries/Core/Controllers.php';
require_once __DIR__ . '/../../../Libraries/Core/Conexion.php';
require_once __DIR__ . '/../../../Libraries/Core/Mysql.php';
require_once __DIR__ . '/../../../Models/MunidashboardModel.php';
require_once __DIR__ . '/../../../Controllers/Munidashboard.php';

if (!defined('MUNI')) {
    define('MUNI', 21);
}

class MunidashboardControllerTest extends BaseControllerTest
{
    /**
     * @group critical
     */
    public function testGetManagementKpisReturnsPermissionErrorWhenViewPermissionMissing(): void
    {
        $_SESSION = ['login' => true, 'permits_module' => ['v' => false]];
        $_POST = ['router_id' => '7'];
        $controller = new TestableMunidashboardController(new FakeMunidashboardModel([]));

        ob_start();
        $controller->getManagementKpis();
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('No tiene permisos para ver estos indicadores.', $response['msg']);
    }

    /**
     * @group error-handling
     */
    public function testGetManagementKpisRequiresValidRouterId(): void
    {
        $_SESSION = ['login' => true, 'permits_module' => ['v' => true]];
        $_POST = ['router_id' => 'not-valid'];
        $controller = new TestableMunidashboardController(new FakeMunidashboardModel([]));

        ob_start();
        $controller->getManagementKpis();
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Seleccione un router.', $response['msg']);
    }

    /**
     * @group critical
     */
    public function testGetManagementKpisReturnsDbFirstPayloadWhenRouterStatsUnavailable(): void
    {
        $_SESSION = ['login' => true, 'permits_module' => ['v' => true]];
        $_POST = ['router_id' => '7'];
        $model = new FakeMunidashboardModel([
            'router_id' => 7,
            'source' => ['catalog' => 'available', 'router' => 'unavailable'],
            'kpis' => [
                'assigned_service' => ['value' => 4, 'label' => 'Personal con servicio asignado'],
                'observed_consumption' => ['value' => 'Sin información suficiente', 'label' => 'Usuarios con consumo en observación', 'evidence' => 'current_only'],
                'departments_attention' => ['value' => 1, 'label' => 'Áreas que requieren atención'],
                'ip_compliance' => ['percent' => 75, 'value' => '75%', 'label' => 'Cumplimiento de IP asignada'],
                'queue_sync_compliance' => ['percent' => 50, 'value' => '50%', 'label' => 'Configuración aplicada correctamente'],
            ],
            'departments' => [],
            'messages' => [],
            'metadata' => ['uses_generated_history' => false],
        ]);
        $controller = new TestableMunidashboardController($model, new FakeBandwidthService(false));

        ob_start();
        $controller->getManagementKpis();
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals(7, $model->requestedRouterId);
        $this->assertEquals('unavailable', $response['data']['source']['router']);
        $this->assertEquals('Sin información suficiente', $response['data']['kpis']['observed_consumption']['value']);
        $this->assertFalse($response['data']['metadata']['uses_generated_history']);
    }

    /**
     * @group business-logic
     */
    public function testGetManagementKpisMergesLiveQueueStatsWhenAvailable(): void
    {
        $_SESSION = ['login' => true, 'permits_module' => ['v' => true]];
        $_POST = ['router_id' => '7'];
        $model = new FakeMunidashboardModel([
            'router_id' => 7,
            'source' => ['catalog' => 'available', 'router' => 'unavailable'],
            'kpis' => ['observed_consumption' => ['value' => 'Sin información suficiente', 'evidence' => 'current_only']],
            'departments' => [],
            'messages' => [],
            'metadata' => ['uses_generated_history' => false],
        ]);
        $controller = new TestableMunidashboardController($model, new FakeBandwidthService(true));

        ob_start();
        $controller->getManagementKpis();
        $response = json_decode(ob_get_clean(), true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('available', $response['data']['source']['router']);
        $this->assertEquals(1, $response['data']['kpis']['observed_consumption']['value']);
        $this->assertEquals('current_only', $response['data']['kpis']['observed_consumption']['evidence']);
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

class TestableMunidashboardController extends Munidashboard
{
    private $fakeService;

    public function __construct($model, $fakeService = null)
    {
        $this->model = $model;
        $this->fakeService = $fakeService ?: new FakeBandwidthService(false);
    }

    protected function createMuniSyncService(int $routerId)
    {
        return $this->fakeService;
    }
}

class FakeMunidashboardModel
{
    private array $payload;
    public ?int $requestedRouterId = null;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getManagementKpiSummary(?int $routerId): array
    {
        $this->requestedRouterId = $routerId;
        return $this->payload;
    }

    public function mergeManagementKpisWithBandwidth(array $payload, array $queues): array
    {
        $model = new TestableMunidashboardControllerModel();
        return $model->mergeManagementKpisWithBandwidth($payload, $queues);
    }
}

class TestableMunidashboardControllerModel extends MunidashboardModel
{
    public function __construct()
    {
    }
}

class FakeBandwidthService
{
    private bool $success;

    public function __construct(bool $success)
    {
        $this->success = $success;
    }

    public function getBandwidthStats(): object
    {
        return (object) [
            'success' => $this->success,
            'queues' => $this->success ? [['download_bytes' => 1024, 'upload_bytes' => 0, 'download_rate' => 2048, 'upload_rate' => 0, 'rate' => '0/2048', 'disabled' => false]] : [],
            'message' => $this->success ? '' : 'No se pudo conectar al router',
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit(MunidashboardControllerTest::runStandalone());
}
