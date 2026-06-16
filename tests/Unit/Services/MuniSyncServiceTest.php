<?php

require_once __DIR__ . '/../../Support/BaseTestCase.php';
require_once __DIR__ . '/../../../Services/BaseService.php';
require_once __DIR__ . '/../../../Services/MuniSyncService.php';
require_once __DIR__ . '/../../../Libraries/Core/Conexion.php';
require_once __DIR__ . '/../../../Libraries/Core/Mysql.php';
require_once __DIR__ . '/../../../Models/MunidashboardModel.php';

class MuniSyncServiceTest extends BaseTestCase
{
    /**
     * @group critical
     */
    public function testBandwidthStatsExposeRouterOsCurrentRateForManagementKpiMerge(): void
    {
        $service = $this->makeServiceWithRouterData([
            [
                'name' => 'muni-finanzas-1',
                'target' => '10.10.0.11/32',
                'max-limit' => '5M/10M',
                'bytes' => '512/2048',
                'rate' => '0/4096',
                'disabled' => 'false',
            ],
            [
                'name' => 'muni-finanzas-2',
                'target' => '10.10.0.12/32',
                'max-limit' => '5M/10M',
                'bytes' => '1024/8192',
                'rate' => '0/0',
                'disabled' => 'false',
            ],
        ]);

        $stats = $service->getBandwidthStats();
        $model = new TestableMuniSyncDashboardModel();
        $payload = $model->buildManagementKpiPayload([], 7);
        $merged = $model->mergeManagementKpisWithBandwidth($payload, $stats->queues);
        $queueWithTraffic = $this->findQueueByUserId($stats->queues, 1);

        $this->assertTrue($stats->success);
        $this->assertEquals(0, $queueWithTraffic['upload_rate']);
        $this->assertEquals(4096, $queueWithTraffic['download_rate']);
        $this->assertEquals(1, $merged['kpis']['observed_consumption']['value']);
        $this->assertEquals('current_only', $merged['kpis']['observed_consumption']['evidence']);
    }

    /**
     * @group critical
     */
    public function testBandwidthStatsMissingRouterOsRateDoesNotFabricateZeroCurrentConsumption(): void
    {
        $service = $this->makeServiceWithRouterData([
            [
                'name' => 'muni-finanzas-1',
                'target' => '10.10.0.11/32',
                'max-limit' => '5M/10M',
                'bytes' => '512/2048',
                'disabled' => 'false',
            ],
        ]);

        $stats = $service->getBandwidthStats();
        $model = new TestableMuniSyncDashboardModel();
        $payload = $model->buildManagementKpiPayload([], 7);
        $merged = $model->mergeManagementKpisWithBandwidth($payload, $stats->queues);
        $queue = $this->findQueueByUserId($stats->queues, 1);

        $this->assertTrue($stats->success);
        $this->assertFalse(array_key_exists('upload_rate', $queue));
        $this->assertFalse(array_key_exists('download_rate', $queue));
        $this->assertFalse(array_key_exists('rate', $queue));
        $this->assertEquals('Sin información suficiente', $merged['kpis']['observed_consumption']['value']);
        $this->assertEquals('current_rate_unavailable', $merged['metadata']['observed_consumption_unavailable_reason']);
    }

    private function findQueueByUserId(array $queues, int $userId): array
    {
        foreach ($queues as $queue) {
            if ((int) ($queue['user_id'] ?? 0) === $userId) {
                return $queue;
            }
        }

        throw new RuntimeException('Queue not found for user ' . $userId);
    }

    private function makeServiceWithRouterData(array $queues): MuniSyncService
    {
        $reflection = new ReflectionClass(MuniSyncService::class);
        /** @var MuniSyncService $service */
        $service = $reflection->newInstanceWithoutConstructor();

        $this->setPrivateProperty($service, 'routerId', 7);
        $this->setPrivateProperty($service, 'router', new FakeMuniSyncRouter($queues));
        $this->setPrivateProperty($service, 'model', new FakeMuniSyncMuniredModel());

        return $service;
    }

    private function setPrivateProperty(object $object, string $property, $value): void
    {
        $reflection = new ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}

class FakeMuniSyncRouter
{
    public bool $connected = true;
    private array $queues;

    public function __construct(array $queues)
    {
        $this->queues = $queues;
    }

    public function APIListQueuesSimple(): object
    {
        return (object) [
            'success' => true,
            'data' => $this->queues,
        ];
    }
}

class FakeMuniSyncMuniredModel
{
    public function getUsers(array $filters): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Finanzas 1',
                'department_name' => 'Finanzas',
                'ip_address' => '10.10.0.11',
            ],
            [
                'id' => 2,
                'name' => 'Finanzas 2',
                'department_name' => 'Finanzas',
                'ip_address' => '10.10.0.12',
            ],
        ];
    }
}

class TestableMuniSyncDashboardModel extends MunidashboardModel
{
    public function __construct()
    {
    }
}
