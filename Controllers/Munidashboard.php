<?php

class Munidashboard extends Controllers
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['login'])) {
            header('Location: ' . base_url() . '/login');
            die();
        }
        consent_permission(MUNI);
    }

    // Vista principal del dashboard municipal
    public function munidashboard()
    {
        $this->index();
    }

    public function index()
    {
        $data['page_tag'] = "Dashboard - Red Municipal";
        $data['page_title'] = "DASHBOARD RED MUNICIPAL";
        $data['page_name'] = "munidashboard";
        $data['page_functions_js'] = "munidashboard.js";

        $this->views->getView($this, "index", $data);
    }

    // =============================================
    // API ENDPOINTS
    // =============================================

    public function getStats()
    {
        if ($_SESSION['permits_module']['v']) {
            $router_id = !empty($_POST['router_id']) ? intval($_POST['router_id']) : null;
            $stats = $this->model->getStats($router_id);

            echo json_encode([
                'status' => 'success',
                'data' => $stats,
            ], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getAlerts()
    {
        if ($_SESSION['permits_module']['v']) {
            $limit = intval($_POST['limit'] ?? 10);
            $alerts = $this->model->getAlertsRecent($limit);

            echo json_encode([
                'status' => 'success',
                'data' => $alerts,
            ], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getDepartmentSummary()
    {
        if ($_SESSION['permits_module']['v']) {
            $router_id = !empty($_POST['router_id']) ? intval($_POST['router_id']) : null;
            $summary = $this->model->getDepartmentSummary($router_id);

            echo json_encode([
                'status' => 'success',
                'data' => $summary,
            ], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getRouterStatus()
    {
        if ($_SESSION['permits_module']['v']) {
            $router_id = intval($_POST['router_id'] ?? 0);

            if ($router_id == 0) {
                echo json_encode(['status' => 'error', 'msg' => 'Seleccione un router.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            require_once('Services/MuniSyncService.php');
            $syncService = new MuniSyncService($router_id);
            $result = $syncService->getRouterStatus();

            echo json_encode([
                'status' => $result->success ? 'success' : 'error',
                'connected' => $result->connected ?? false,
                'data' => $result->data ?? null,
                'msg' => $result->message ?? '',
            ], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function quickBlock()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $domain = strClean($_POST['domain'] ?? '');
            $router_id = intval($_POST['router_id'] ?? 0);

            if (empty($domain) || $router_id == 0) {
                echo json_encode(['status' => 'error', 'msg' => 'Dominio y router son requeridos.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            require_once('Services/MuniSyncService.php');
            require_once('Libraries/MikroTik/RouterFactory.php');

            $router = RouterFactory::createFromDatabase($router_id);

            if (!$router || !$router->connected) {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo conectar al router.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $result = $router->APIAddDNSBlock($domain, '0.0.0.0');

            // Log the action
            $muniredModel = new MuniredModel();
            $muniredModel->logAction(
                $_SESSION['idUser'],
                'quick_block_domain',
                'filter',
                null,
                json_encode(['domain' => $domain, 'router_id' => $router_id]),
                $result->success ? 'success' : 'error'
            );

            if ($result->success) {
                echo json_encode(['status' => 'success', 'msg' => "Dominio $domain bloqueado exitosamente."], JSON_UNESCAPED_UNICODE);
            } else {
                $msg = $result->message ?? 'Error desconocido';
                echo json_encode(['status' => 'error', 'msg' => "Error: $msg"], JSON_UNESCAPED_UNICODE);
            }
        }
        die();
    }
}
