<?php 
    head($data);
?>

<div id="divLoading">
    <div>
        <img src="<?= base_style(); ?>/images/loading.svg" alt="Loading">
    </div>
</div>

<!-- Page Content Wrapper -->
<div id="contentAjax"> 
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-network-wired"></i> <?= $data['page_title']; ?></h1>
                <p>Gestión avanzada de QoS con Queue Tree</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= $data['page_title']; ?></li>
            </ul>
        </div>

        <!-- Navigation Tabs -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <ul class="nav nav-tabs" id="queueTreeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="policies-tab" data-toggle="tab" href="#policies" role="tab" aria-controls="policies" aria-selected="true">
                                <i class="fas fa-list"></i> Políticas QoS
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="assignments-tab" data-toggle="tab" href="#assignments" role="tab" aria-controls="assignments" aria-selected="false">
                                <i class="fas fa-users"></i> Asignaciones
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="templates-tab" data-toggle="tab" href="#templates" role="tab" aria-controls="templates" aria-selected="false">
                                <i class="fas fa-layer-group"></i> Templates
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="monitoring-tab" data-toggle="tab" href="#monitoring" role="tab" aria-controls="monitoring" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Monitoreo
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-content" id="queueTreeTabContent">
            
            <!-- Tab: Políticas QoS -->
            <div class="tab-pane fade show active" id="policies" role="tabpanel" aria-labelledby="policies-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-header">
                                <h3 class="tile-title">Políticas Queue Tree</h3>
                                <div class="tile-elements">
                                    <button class="btn btn-primary" type="button" onclick="openPolicyModal()">
                                        <i class="fas fa-plus"></i> Nueva Política
                                    </button>
                                    <button class="btn btn-success" type="button" onclick="syncAllPolicies()">
                                        <i class="fas fa-sync"></i> Sincronizar Todo
                                    </button>
                                </div>
                            </div>
                            <div class="tile-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="tablePolicies">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Router</th>
                                                <th>Target</th>
                                                <th>Límite</th>
                                                <th>Burst</th>
                                                <th>Prioridad</th>
                                                <th>Clientes</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Asignaciones -->
            <div class="tab-pane fade" id="assignments" role="tabpanel" aria-labelledby="assignments-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-header">
                                <h3 class="tile-title">Asignaciones de QoS por Cliente</h3>
                                <div class="tile-elements">
                                    <button class="btn btn-primary" type="button" onclick="openAssignModal()">
                                        <i class="fas fa-user-plus"></i> Asignar Política
                                    </button>
                                    <button class="btn btn-warning" type="button" onclick="syncAssignments()">
                                        <i class="fas fa-cloud-upload-alt"></i> Sincronizar MikroTik
                                    </button>
                                </div>
                            </div>
                            <div class="tile-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="tableAssignments">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>IP</th>
                                                <th>Política</th>
                                                <th>Upload</th>
                                                <th>Download</th>
                                                <th>Prioridad</th>
                                                <th>Estado</th>
                                                <th>Sync</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Templates -->
            <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-header">
                                <h3 class="tile-title">Templates Predefinidos</h3>
                                <div class="tile-elements">
                                    <span class="badge bg-info">Planes listos para usar</span>
                                </div>
                            </div>
                            <div class="tile-body">
                                <div class="row" id="templatesContainer">
                                    <!-- Templates se cargan dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Monitoreo -->
            <div class="tab-pane fade" id="monitoring" role="tabpanel" aria-labelledby="monitoring-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-header">
                                <h3 class="tile-title">Monitoreo de Queue Tree</h3>
                                <div class="tile-elements">
                                    <button class="btn btn-info" type="button" onclick="refreshMonitoring()">
                                        <i class="fas fa-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="tile-body">
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="widget-small primary coloured-icon">
                                            <i class="icon fas fa-list fa-3x"></i>
                                            <div class="info">
                                                <h4>Políticas</h4>
                                                <p><b id="totalPolicies">0</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="widget-small info coloured-icon">
                                            <i class="icon fas fa-users fa-3x"></i>
                                            <div class="info">
                                                <h4>Clientes</h4>
                                                <p><b id="totalAssignments">0</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="widget-small warning coloured-icon">
                                            <i class="icon fas fa-sync fa-3x"></i>
                                            <div class="info">
                                                <h4>Pendientes</h4>
                                                <p><b id="pendingSync">0</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="widget-small danger coloured-icon">
                                            <i class="icon fas fa-exclamation-triangle fa-3x"></i>
                                            <div class="info">
                                                <h4>Errores</h4>
                                                <p><b id="syncErrors">0</b></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Estado de Sincronización</h5>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="syncStatusChart" width="400" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Distribución por Prioridad</h5>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="priorityChart" width="400" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Modales -->
<?php 
    require_once('Views/Resources/modals/modalQueueTreePolicy.php');
    require_once('Views/Resources/modals/modalAssignPolicy.php');
?>

<?php footer($data); ?>