<?php

require_once __DIR__ . '/../Support/BaseTestCase.php';

class MunidashboardKpiTest extends BaseTestCase
{
    private string $rootPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootPath = dirname(__DIR__, 2);
    }

    /**
     * @group e2e
     */
    public function testExecutiveKpiShellRendersSpanishLabelsAboveTechnicalTable(): void
    {
        $view = $this->readProjectFile('Views/Munidashboard/index.php');

        $this->assertContainsText('id="managementKpiSection"', $view);
        $this->assertContainsText('Personal con servicio asignado', $view);
        $this->assertContainsText('Usuarios con consumo en observación', $view);
        $this->assertContainsText('Áreas que requieren atención', $view);
        $this->assertContainsText('Cumplimiento de IP asignada', $view);
        $this->assertContainsText('Configuración aplicada correctamente', $view);
        $this->assertContainsText('Datos actuales', $view);
        $this->assertContainsText('id="managementKpiFallback"', $view);
        $this->assertContainsText('id="topConsumersTable"', $view);

        $this->assertLessThan(
            strpos($view, 'id="topConsumersTable"'),
            strpos($view, 'id="managementKpiSection"'),
            'Executive KPI section must render before the technical Simple Queue table.'
        );
    }

    /**
     * @group e2e
     */
    public function testDashboardJavascriptFetchesAndRendersManagementKpisWithTimeoutFallback(): void
    {
        $script = $this->readProjectFile('Assets/js/functions/munidashboard.js');

        $this->assertContainsText('/munidashboard/getManagementKpis', $script);
        $this->assertContainsText('loadManagementKpis', $script);
        $this->assertContainsText('renderManagementKpis', $script);
        $this->assertContainsText('renderManagementKpiFallback', $script);
        $this->assertContainsText('timeout: 12000', $script);
        $this->assertContainsText('Sin información suficiente', $script);
        $this->assertContainsText('El router no respondió a tiempo', $script);
    }

    /**
     * @group e2e
     */
    public function testDashboardStylesSupportCompactKpisFallbacksAndPrint(): void
    {
        $styles = $this->readProjectFile('Assets/css/munidashboard.css');

        $this->assertContainsText('.muni-kpi-grid', $styles);
        $this->assertContainsText('.muni-kpi-card', $styles);
        $this->assertContainsText('.muni-kpi-status', $styles);
        $this->assertContainsText('.muni-kpi-fallback', $styles);
        $this->assertContainsText('.muni-department-evidence', $styles);
        $this->assertContainsText('.muni-department-evidence__reason', $styles);
        $this->assertContainsText('@media print', $styles);
        $this->assertContainsText('managementKpiSection', $styles);
    }

    /**
     * @group e2e
     */
    public function testDepartmentEvidenceUsesReviewCopyWithoutSyntheticTrendClaims(): void
    {
        $view = $this->readProjectFile('Views/Munidashboard/index.php');
        $script = $this->readProjectFile('Assets/js/functions/munidashboard.js');
        $combined = strtolower($view . "\n" . $script);

        $this->assertContainsText('id="departmentEvidenceSection"', $view);
        $this->assertContainsText('id="departmentEvidenceList"', $view);
        $this->assertContainsText('renderDepartmentEvidence', $script);
        $this->assertContainsText('Revisión administrativa', $script);
        $this->assertContainsText('Lectura momentánea', $script);

        $this->assertStringNotContainsString('tendencia', $combined);
        $this->assertStringNotContainsString('semanal', $combined);
        $this->assertStringNotContainsString('mensual', $combined);
        $this->assertStringNotContainsString('lentitud reiterada', $combined);
        $this->assertStringNotContainsString('incumplimiento confirmado', $combined);
    }

    private function readProjectFile(string $relativePath): string
    {
        $path = $this->rootPath . '/' . $relativePath;
        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Could not read project file: ' . $relativePath);
        }

        return $content;
    }

    private function assertContainsText(string $needle, string $haystack, string $message = ''): void
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack, $message);
            return;
        }

        if (strpos($haystack, $needle) === false) {
            throw new Exception($message ?: "Expected text not found: {$needle}");
        }
    }
}
