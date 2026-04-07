<?php

class Munired extends Controllers
{
    private $syncService;

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

    // =============================================
    // VIEWS
    // =============================================

    public function munired()
    {
        $this->departments();
    }

    public function departments()
    {
        $data['page_tag'] = "Departamentos - Red Municipal";
        $data['page_title'] = "DEPARTAMENTOS DE RED";
        $data['page_name'] = "munired";
        $data['page_functions_js'] = "munired.js";
        $data['page_section'] = "departments";
        $this->views->getView($this, "departments", $data);
    }

    public function users()
    {
        $data['page_tag'] = "Usuarios - Red Municipal";
        $data['page_title'] = "USUARIOS DE RED";
        $data['page_name'] = "munired";
        $data['page_functions_js'] = "munired.js";
        $data['page_section'] = "users";
        $this->views->getView($this, "users", $data);
    }

    public function bandwidth()
    {
        $data['page_tag'] = "Ancho de Banda - Red Municipal";
        $data['page_title'] = "CONTROL DE ANCHO DE BANDA";
        $data['page_name'] = "munired";
        $data['page_functions_js'] = "munired.js";
        $data['page_section'] = "bandwidth";
        $this->views->getView($this, "bandwidth", $data);
    }

    public function filtering()
    {
        $data['page_tag'] = "Filtrado - Red Municipal";
        $data['page_title'] = "FILTRADO DE CONTENIDO";
        $data['page_name'] = "munired";
        $data['page_functions_js'] = "munired.js";
        $data['page_section'] = "filtering";
        $this->views->getView($this, "filtering", $data);
    }

    public function config()
    {
        $data['page_tag'] = "Configuracion - Red Municipal";
        $data['page_title'] = "CONFIGURACION DE RED";
        $data['page_name'] = "munired";
        $data['page_functions_js'] = "munired.js";
        $data['page_section'] = "config";
        $this->views->getView($this, "config", $data);
    }

    // =============================================
    // DEPARTMENT ENDPOINTS
    // =============================================

    public function getDepartments()
    {
        if ($_SESSION['permits_module']['v']) {
            $router_id = !empty($_POST['router_id']) ? intval($_POST['router_id']) : null;
            $data = $this->model->getDepartments($router_id);

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['encrypt_id'] = encrypt($data[$i]['id']);
                $data[$i]['status_label'] = $data[$i]['status'] == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
                $data[$i]['qos_label'] = $this->getSyncBadge($data[$i]['qos_sync_status']);

                $options = $this->buildOptions($data[$i]['id']);
                $data[$i]['options'] = $options;
            }

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getDepartment(string $id)
    {
        if ($_SESSION['permits_module']['v']) {
            $id = intval(decrypt($id));
            if ($id > 0) {
                $data = $this->model->getDepartment($id);
                if (!empty($data)) {
                    $data['encrypt_id'] = encrypt($data['id']);
                    $answer = ['status' => 'success', 'data' => $data];
                } else {
                    $answer = ['status' => 'error', 'msg' => 'Departamento no encontrado.'];
                }
            } else {
                $answer = ['status' => 'error', 'msg' => 'ID invalido.'];
            }
            echo json_encode($answer, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function saveDepartment()
    {
        if ($_POST) {
            if (empty($_POST['name']) || empty($_POST['ip_range']) || empty($_POST['router_id'])) {
                $response = ['status' => 'error', 'msg' => 'Campos obligatorios incompletos.'];
            } else {
                $id = intval(decrypt($_POST['id'] ?? ''));

                $departmentData = [
                    'router_id' => intval($_POST['router_id']),
                    'name' => strClean($_POST['name']),
                    'description' => strClean($_POST['description'] ?? ''),
                    'ip_range' => strClean($_POST['ip_range']),
                    'priority' => intval($_POST['priority'] ?? 4),
                    'default_upload' => strClean($_POST['default_upload'] ?? '5M'),
                    'default_download' => strClean($_POST['default_download'] ?? '10M'),
                    'burst_upload' => !empty($_POST['burst_upload']) ? strClean($_POST['burst_upload']) : null,
                    'burst_download' => !empty($_POST['burst_download']) ? strClean($_POST['burst_download']) : null,
                    'burst_threshold_up' => !empty($_POST['burst_threshold_up']) ? strClean($_POST['burst_threshold_up']) : null,
                    'burst_threshold_down' => !empty($_POST['burst_threshold_down']) ? strClean($_POST['burst_threshold_down']) : null,
                    'burst_time' => !empty($_POST['burst_time']) ? strClean($_POST['burst_time']) : null,
                    'qos_max_limit' => !empty($_POST['qos_max_limit']) ? strClean($_POST['qos_max_limit']) : null,
                ];

                // Validate IP range
                $validation = $this->model->validateIpRange(
                    $departmentData['ip_range'],
                    $departmentData['router_id'],
                    $id
                );

                if (!$validation['valid']) {
                    $response = ['status' => 'error', 'msg' => $validation['error']];
                } else {
                    if ($id == 0) {
                        if ($_SESSION['permits_module']['r']) {
                            $request = $this->model->createDepartment($departmentData);
                        } else {
                            $response = ['status' => 'error', 'msg' => 'Sin permisos para crear.'];
                            echo json_encode($response, JSON_UNESCAPED_UNICODE);
                            die();
                        }
                    } else {
                        if ($_SESSION['permits_module']['a']) {
                            $request = $this->model->updateDepartment($id, $departmentData);
                        } else {
                            $response = ['status' => 'error', 'msg' => 'Sin permisos para editar.'];
                            echo json_encode($response, JSON_UNESCAPED_UNICODE);
                            die();
                        }
                    }

                    if ($request == 'success') {
                        $this->model->logAction(
                            $_SESSION['idUser'],
                            $id == 0 ? 'create_department' : 'update_department',
                            'department',
                            $id,
                            json_encode($departmentData)
                        );
                        $response = ['status' => 'success', 'msg' => $id == 0 ? 'Departamento creado exitosamente.' : 'Departamento actualizado exitosamente.'];
                    } elseif ($request == 'exists') {
                        $response = ['status' => 'error', 'msg' => 'Ya existe un departamento con ese nombre.'];
                    } else {
                        $response = ['status' => 'error', 'msg' => 'Error al procesar la operacion.'];
                    }
                }
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function deleteDepartment()
    {
        if ($_POST) {
            if ($_SESSION['permits_module']['e']) {
                $id = intval(decrypt($_POST['id']));
                $request = $this->model->deleteDepartment($id);

                if ($request == 'success') {
                    $this->model->logAction($_SESSION['idUser'], 'delete_department', 'department', $id);
                    $response = ['status' => 'success', 'msg' => 'Departamento eliminado.'];
                } elseif ($request == 'exists') {
                    $response = ['status' => 'error', 'msg' => 'No se puede eliminar: tiene usuarios asignados.'];
                } else {
                    $response = ['status' => 'error', 'msg' => 'Error al eliminar.'];
                }
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
        die();
    }

    // =============================================
    // USER ENDPOINTS
    // =============================================

    public function getUsers()
    {
        if ($_SESSION['permits_module']['v']) {
            $filters = [
                'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
                'status' => isset($_POST['status']) ? $_POST['status'] : '',
                'search' => !empty($_POST['search']) ? $_POST['search'] : '',
            ];

            $data = $this->model->getUsers($filters);

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['encrypt_id'] = encrypt($data[$i]['id']);
                $data[$i]['status_label'] = $data[$i]['status'] == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
                $data[$i]['sync_label'] = $this->getSyncBadge($data[$i]['queue_sync_status']);

                // Effective bandwidth
                $data[$i]['effective_upload'] = !empty($data[$i]['custom_upload']) ? $data[$i]['custom_upload'] : $data[$i]['default_upload'];
                $data[$i]['effective_download'] = !empty($data[$i]['custom_download']) ? $data[$i]['custom_download'] : $data[$i]['default_download'];

                $options = $this->buildUserOptions($data[$i]);
                $data[$i]['options'] = $options;
            }

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getUser(string $id)
    {
        if ($_SESSION['permits_module']['v']) {
            $id = intval(decrypt($id));
            if ($id > 0) {
                $data = $this->model->getUser($id);
                if (!empty($data)) {
                    $data['encrypt_id'] = encrypt($data['id']);
                    $answer = ['status' => 'success', 'data' => $data];
                } else {
                    $answer = ['status' => 'error', 'msg' => 'Usuario no encontrado.'];
                }
            } else {
                $answer = ['status' => 'error', 'msg' => 'ID invalido.'];
            }
            echo json_encode($answer, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function saveUser()
    {
        if ($_POST) {
            if (empty($_POST['name']) || empty($_POST['ip_address'])) {
                $response = ['status' => 'error', 'msg' => 'Campos obligatorios incompletos.'];
            } else {
                $id = intval(decrypt($_POST['id'] ?? ''));

                // Get router_id: from department if provided, otherwise first available router
                $dept_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
                $router_id = 0;

                if ($dept_id) {
                    $dept = $this->model->getDepartment($dept_id);
                    if (!empty($dept)) {
                        $router_id = intval($dept['router_id']);
                    }
                }

                if ($router_id == 0) {
                    $routers = $this->model->getRouters();
                    if (!empty($routers)) {
                        $router_id = intval($routers[0]['id']);
                    }
                }

                $userData = [
                    'department_id' => $dept_id,
                    'router_id' => $router_id,
                    'name' => strClean($_POST['name']),
                    'ip_address' => strClean($_POST['ip_address']),
                    'mac_address' => !empty($_POST['mac_address']) ? strClean($_POST['mac_address']) : null,
                    'custom_upload' => !empty($_POST['custom_upload']) ? strClean($_POST['custom_upload']) : null,
                    'custom_download' => !empty($_POST['custom_download']) ? strClean($_POST['custom_download']) : null,
                ];

                if ($id == 0) {
                    if (!$_SESSION['permits_module']['r']) {
                        echo json_encode(['status' => 'error', 'msg' => 'Sin permisos para crear.'], JSON_UNESCAPED_UNICODE);
                        die();
                    }
                    $request = $this->model->createUser($userData);
                } else {
                    if (!$_SESSION['permits_module']['a']) {
                        echo json_encode(['status' => 'error', 'msg' => 'Sin permisos para editar.'], JSON_UNESCAPED_UNICODE);
                        die();
                    }
                    $request = $this->model->updateUser($id, $userData);
                }

                switch ($request) {
                    case 'success':
                        $this->model->logAction(
                            $_SESSION['idUser'],
                            $id == 0 ? 'create_user' : 'update_user',
                            'user',
                            $id,
                            json_encode($userData)
                        );
                        $response = ['status' => 'success', 'msg' => $id == 0 ? 'Usuario creado exitosamente.' : 'Usuario actualizado.'];

                        // Auto-sync queue after save
                        session_write_close(); // Release session lock before router call
                        $this->initSyncService($userData['router_id']);
                        if ($this->syncService) {
                            $userId = $id > 0 ? $id : $this->getLastUserId();
                            if ($userId) {
                                $syncResult = $this->syncService->syncUserQueue($userId);
                                $response['sync'] = $syncResult->success ? 'synced' : $syncResult->message;
                            }
                        }
                        break;
                    case 'ip_exists':
                        $response = ['status' => 'error', 'msg' => 'La IP ya esta asignada a otro usuario.'];
                        break;
                    case 'dept_not_found':
                        $response = ['status' => 'error', 'msg' => 'Departamento no encontrado.'];
                        break;
                    case 'ip_out_of_range':
                        $response = ['status' => 'error', 'msg' => 'La IP esta fuera del rango del departamento.'];
                        break;
                    default:
                        $response = ['status' => 'error', 'msg' => 'Error al procesar la operacion.'];
                }
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function deleteUser()
    {
        if ($_POST) {
            if ($_SESSION['permits_module']['e']) {
                $id = intval(decrypt($_POST['id']));

                // Remove queue from router first
                $user = $this->model->getUser($id);
                if (!empty($user)) {
                    session_write_close(); // Release session lock before router call
                    $this->initSyncServiceFromDept($user['department_id']);
                    if ($this->syncService) {
                        $this->syncService->removeUserQueue($id);
                    }
                }

                $request = $this->model->deleteUser($id);

                if ($request == 'success') {
                    $this->model->logAction($_SESSION['idUser'], 'delete_user', 'user', $id);
                    $response = ['status' => 'success', 'msg' => 'Usuario eliminado.'];
                } else {
                    $response = ['status' => 'error', 'msg' => 'Error al eliminar.'];
                }
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
        die();
    }

    public function toggleUser()
    {
        if ($_POST) {
            if ($_SESSION['permits_module']['a']) {
                $id = intval(decrypt($_POST['id']));
                $status = intval($_POST['status']);
                $request = $this->model->toggleUser($id, $status);

                if ($request == 'success') {
                    // Toggle queue on router
                    $user = $this->model->getUser($id);
                    if (!empty($user)) {
                        session_write_close(); // Release session lock before router call
                        $this->initSyncServiceFromDept($user['department_id']);
                        if ($this->syncService) {
                            $this->syncService->toggleUserQueue($id, $status === 1);
                        }
                    }

                    $this->model->logAction(
                        $_SESSION['idUser'],
                        $status === 1 ? 'enable_user' : 'disable_user',
                        'user',
                        $id
                    );
                    $response = ['status' => 'success', 'msg' => $status === 1 ? 'Usuario habilitado.' : 'Usuario deshabilitado.'];
                } else {
                    $response = ['status' => 'error', 'msg' => 'Error al cambiar estado.'];
                }
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
        die();
    }

    // =============================================
    // EXPORT ENDPOINTS
    // =============================================

    public function exportUsers()
    {
        if (!$_SESSION['permits_module']['v']) {
            die('Sin permisos.');
        }

        $filters = [
            'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
            'status' => isset($_POST['status']) ? $_POST['status'] : '',
            'search' => !empty($_POST['search']) ? $_POST['search'] : '',
        ];

        $data = $this->model->getUsers($filters);

        // Build clean data rows (no HTML)
        $rows = [];
        foreach ($data as $u) {
            $effectiveUpload = !empty($u['custom_upload']) ? $u['custom_upload'] : ($u['effective_upload'] ?? '5M');
            $effectiveDownload = !empty($u['custom_download']) ? $u['custom_download'] : ($u['effective_download'] ?? '10M');
            $statusText = $u['status'] == 1 ? 'Activo' : 'Inactivo';

            $rows[] = [
                'name' => $u['name'],
                'department' => $u['department_name'] ?? 'Sin departamento',
                'ip' => $u['ip_address'],
                'mac' => $u['mac_address'] ?? '-',
                'upload' => $effectiveUpload,
                'download' => $effectiveDownload,
                'status' => $statusText,
            ];
        }

        $format = $_POST['format'] ?? 'excel';
        $date = date('Y-m-d');

        if ($format === 'excel') {
            $this->exportUsersCSV($rows, $date);
        } else {
            $this->exportUsersPrintHTML($rows, $date);
        }
    }

    private function exportUsersCSV(array $rows, string $date): void
    {
        $filename = "usuarios_$date.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, ['Nombre', 'Departamento', 'IP', 'MAC', 'Upload', 'Download', 'Estado'], ';');

        // Data rows
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['name'],
                $row['department'],
                $row['ip'],
                $row['mac'],
                $row['upload'],
                $row['download'],
                $row['status'],
            ], ';');
        }

        fclose($output);
        die();
    }

    private function exportUsersPrintHTML(array $rows, string $date): void
    {
        $totalUsers = count($rows);
        $activeUsers = count(array_filter($rows, function ($r) { return $r['status'] === 'Activo'; }));

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Usuarios de Red Municipal - <?= $date; ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; padding: 20px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .header h1 { font-size: 18px; margin-bottom: 4px; }
                .header p { font-size: 11px; color: #666; }
                .summary { margin-bottom: 15px; font-size: 11px; }
                .summary span { margin-right: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #999; padding: 5px 8px; text-align: left; }
                th { background-color: #f0f0f0; font-weight: bold; font-size: 11px; }
                td { font-size: 11px; }
                tr:nth-child(even) { background-color: #fafafa; }
                .status-active { color: #28a745; font-weight: bold; }
                .status-inactive { color: #dc3545; font-weight: bold; }
                .footer { margin-top: 15px; text-align: center; font-size: 10px; color: #999; }
                @media print {
                    body { padding: 10px; }
                    @page { margin: 10mm; size: landscape; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Usuarios - Red Municipal</h1>
                <p>Generado el <?= $date; ?> a las <?= date('H:i:s'); ?></p>
            </div>
            <div class="summary">
                <span><strong>Total de usuarios:</strong> <?= $totalUsers; ?></span>
                <span><strong>Activos:</strong> <?= $activeUsers; ?></span>
                <span><strong>Inactivos:</strong> <?= ($totalUsers - $activeUsers); ?></span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>IP</th>
                        <th>MAC</th>
                        <th>Upload</th>
                        <th>Download</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= ($i + 1); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['department']); ?></td>
                        <td><?= htmlspecialchars($row['ip']); ?></td>
                        <td><?= htmlspecialchars($row['mac']); ?></td>
                        <td><?= htmlspecialchars($row['upload']); ?></td>
                        <td><?= htmlspecialchars($row['download']); ?></td>
                        <td class="<?= $row['status'] === 'Activo' ? 'status-active' : 'status-inactive'; ?>">
                            <?= $row['status']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="footer">
                SpaceConect - Red Municipal &copy; <?= date('Y'); ?>
            </div>
            <script>window.onload = function() { window.print(); };</script>
        </body>
        </html>
        <?php
        die();
    }

    // =============================================
    // BANDWIDTH / QoS ENDPOINTS
    // =============================================

    public function syncDepartmentQueues()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $dept_id = intval(decrypt($_POST['dept_id']));
            session_write_close(); // Release session lock before router call
            $this->initSyncServiceFromDept($dept_id);

            if (!$this->syncService) {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo inicializar el servicio de sync.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $result = $this->syncService->syncDepartmentQueues($dept_id);

            $this->model->logAction(
                $_SESSION['idUser'],
                'sync_department_queues',
                'department',
                $dept_id,
                json_encode(['synced' => $result->synced, 'errors' => count($result->errors)]),
                $result->success ? 'success' : 'error'
            );

            $response = [
                'status' => $result->success ? 'success' : 'warning',
                'msg' => $result->message,
                'data' => ['synced' => $result->synced, 'errors' => $result->errors],
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getQoSStatus()
    {
        if ($_SESSION['permits_module']['v']) {
            $router_id = intval($_POST['router_id'] ?? 0);
            if ($router_id <= 0) {
                echo json_encode(['status' => 'error', 'msg' => 'Seleccione un router.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            session_write_close(); // Release session lock before router call
            $this->initSyncService($router_id);

            if (!$this->syncService) {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo inicializar el servicio. Verifique que la API del router este habilitada.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $result = $this->syncService->getQoSStatus();

            $response = [
                'status' => $result->success ? 'success' : 'error',
                'msg' => $result->message,
                'data' => ['trees' => $result->trees ?? []],
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    // =============================================
    // CONTENT FILTERING ENDPOINTS
    // =============================================

    public function getFilterPolicies()
    {
        if ($_SESSION['permits_module']['v']) {
            $dept_id = intval(decrypt($_POST['dept_id'] ?? ''));
            $data = $this->model->getDeptFilterPolicies($dept_id);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function saveFilterPolicy()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $dept_id = intval(decrypt($_POST['dept_id']));
            $category_ids = $_POST['category_ids'] ?? [];
            $action = strClean($_POST['action'] ?? 'block');

            if (empty($category_ids)) {
                echo json_encode(['status' => 'error', 'msg' => 'Seleccione al menos una categoria.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $request = $this->model->saveDeptFilterPolicy($dept_id, $category_ids, $action);

            if ($request == 'success') {
                $this->model->logAction(
                    $_SESSION['idUser'],
                    'update_filter_policy',
                    'filter',
                    $dept_id,
                    json_encode(['categories' => $category_ids, 'action' => $action])
                );
                $response = ['status' => 'success', 'msg' => 'Politica de filtrado actualizada.'];
            } else {
                $response = ['status' => 'error', 'msg' => 'Error al guardar politica.'];
            }

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getWhitelist()
    {
        if ($_SESSION['permits_module']['v']) {
            $dept_id = !empty($_POST['dept_id']) ? intval(decrypt($_POST['dept_id'])) : null;
            $data = $this->model->getDeptWhitelist($dept_id);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function addWhitelistDomain()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $dept_id = !empty($_POST['dept_id']) ? intval(decrypt($_POST['dept_id'])) : null;
            $domain = strClean($_POST['domain'] ?? '');

            if (empty($domain)) {
                echo json_encode(['status' => 'error', 'msg' => 'Ingrese un dominio.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $request = $this->model->addWhitelistDomain($dept_id, $domain, $_SESSION['idUser']);

            if ($request == 'success') {
                $this->model->logAction(
                    $_SESSION['idUser'],
                    'add_whitelist',
                    'filter',
                    $dept_id,
                    json_encode(['domain' => $domain])
                );
                $response = ['status' => 'success', 'msg' => "Dominio $domain agregado a la whitelist."];
            } else {
                $response = ['status' => 'error', 'msg' => 'Error al agregar dominio.'];
            }

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function removeWhitelistDomain()
    {
        if ($_POST && $_SESSION['permits_module']['e']) {
            $id = intval(decrypt($_POST['id']));
            $this->model->removeWhitelistDomain($id);
            $this->model->logAction($_SESSION['idUser'], 'remove_whitelist', 'filter', $id);
            echo json_encode(['status' => 'success', 'msg' => 'Dominio eliminado de la whitelist.'], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function syncFiltering()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $router_id = intval($_POST['router_id']);
            session_write_close(); // Release session lock before router call
            $this->initSyncService($router_id);

            if (!$this->syncService) {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo inicializar el servicio de sync.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $result = $this->syncService->syncContentFiltering();

            $this->model->logAction(
                $_SESSION['idUser'],
                'sync_filtering',
                'filter',
                null,
                json_encode(['blocked' => $result->blocked, 'whitelisted' => $result->whitelisted]),
                $result->success ? 'success' : 'error'
            );

            $response = [
                'status' => $result->success ? 'success' : 'warning',
                'msg' => $result->message,
                'data' => ['blocked' => $result->blocked, 'whitelisted' => $result->whitelisted, 'errors' => $result->errors],
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getCategories()
    {
        if ($_SESSION['permits_module']['v']) {
            $data = $this->model->getCategories();
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    // =============================================
    // FULL SYNC
    // =============================================

    public function syncAll()
    {
        if ($_POST && $_SESSION['permits_module']['a']) {
            $router_id = intval($_POST['router_id']);
            session_write_close(); // Release session lock before router call
            $this->initSyncService($router_id);

            if (!$this->syncService) {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo inicializar el servicio de sync.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $result = $this->syncService->syncAll();

            $this->model->logAction(
                $_SESSION['idUser'],
                'sync_all',
                'system',
                null,
                $result->message,
                $result->success ? 'success' : 'error'
            );

            $response = [
                'status' => $result->success ? 'success' : 'warning',
                'msg' => $result->message,
                'data' => $result->results,
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    // =============================================
    // ROUTER HELPERS
    // =============================================

    public function getRouters()
    {
        if ($_SESSION['permits_module']['v']) {
            $data = $this->model->getRouters();
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function getAvailableIPs()
    {
        if ($_POST && $_SESSION['permits_module']['v']) {
            $dept_id = intval(decrypt($_POST['dept_id']));
            $dept = $this->model->getDepartment($dept_id);

            if (empty($dept) || empty($dept['ip_range'])) {
                echo json_encode(['status' => 'error', 'msg' => 'Departamento sin rango IP.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            // Get all usable IPs from simple range (e.g., 192.168.88.10-192.168.88.50)
            $parsed = parseSimpleRange($dept['ip_range']);
            if ($parsed === null) {
                echo json_encode(['status' => 'error', 'msg' => 'Formato de rango IP invalido.'], JSON_UNESCAPED_UNICODE);
                die();
            }
            $allIps = getUsableIpsFromRange($parsed['start'], $parsed['end']);

            // Get used IPs
            $users = $this->model->getUsersByDepartment($dept_id);
            $usedIps = array_column($users, 'ip_address');

            // Filter available
            $available = array_values(array_diff($allIps, $usedIps));

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'total' => count($allIps),
                    'used' => count($usedIps),
                    'available' => $available,
                ],
            ], JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    // =============================================
    // PRIVATE HELPERS
    // =============================================

    private function initSyncService(int $router_id): void
    {
        if (!$this->syncService) {
            require_once('Services/MuniSyncService.php');
            $this->syncService = new MuniSyncService($router_id);
        }
    }

    private function initSyncServiceFromDept(int $dept_id): void
    {
        $dept = $this->model->getDepartment($dept_id);
        if (!empty($dept) && !empty($dept['router_id'])) {
            $this->initSyncService(intval($dept['router_id']));
        }
    }

    private function getLastUserId(): int
    {
        $mysql = new Mysql();
        $result = $mysql->select("SELECT MAX(id) AS last_id FROM muni_users");
        return intval($result['last_id'] ?? 0);
    }

    private function getSyncBadge(string $status = null): string
    {
        switch ($status) {
            case 'synced':
                return '<span class="badge badge-success"><i class="fas fa-check"></i> Sincronizado</span>';
            case 'pending':
                return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>';
            case 'error':
                return '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>';
            case 'disabled':
                return '<span class="badge badge-secondary"><i class="fas fa-ban"></i> Deshabilitado</span>';
            default:
                return '<span class="badge badge-secondary">N/A</span>';
        }
    }

    private function buildOptions(int $id): string
    {
        $encId = encrypt($id);
        $update = '';
        $delete = '';

        if ($_SESSION['permits_module']['a']) {
            $update = '<a href="javascript:;" class="blue" data-toggle="tooltip" title="Editar" onclick="editDepartment(\'' . $encId . '\')"><i class="fa fa-pencil-alt"></i></a> ';
        }
        if ($_SESSION['permits_module']['e']) {
            $delete = '<a href="javascript:;" class="red" data-toggle="tooltip" title="Eliminar" onclick="deleteDepartment(\'' . $encId . '\')"><i class="far fa-trash-alt"></i></a>';
        }

        return '<div class="action-buttons">' . $update . $delete . '</div>';
    }

    private function buildUserOptions(array $user): string
    {
        $encId = encrypt($user['id']);
        $html = '<div class="action-buttons">';

        if ($_SESSION['permits_module']['a']) {
            $html .= '<a href="javascript:;" class="blue" title="Editar" onclick="editUser(\'' . $encId . '\')"><i class="fa fa-pencil-alt"></i></a> ';

            // Toggle button
            if ($user['status'] == 1) {
                $html .= '<a href="javascript:;" class="orange" title="Deshabilitar" onclick="toggleUser(\'' . $encId . '\', 0)"><i class="fas fa-user-slash"></i></a> ';
            } else {
                $html .= '<a href="javascript:;" class="green" title="Habilitar" onclick="toggleUser(\'' . $encId . '\', 1)"><i class="fas fa-user-check"></i></a> ';
            }
        }

        if ($_SESSION['permits_module']['e']) {
            $html .= '<a href="javascript:;" class="red" title="Eliminar" onclick="deleteUser(\'' . $encId . '\')"><i class="far fa-trash-alt"></i></a>';
        }

        $html .= '</div>';
        return $html;
    }
}
