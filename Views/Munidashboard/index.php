<?php head($data); ?>

<div id="divLoading">
    <div>
        <img src="<?= base_style(); ?>/images/loading.svg" alt="Loading">
    </div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-building"></i> <?= $data['page_title']; ?></h1>
                <p>Panel de control de la red municipal</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item active">Red Municipal</li>
            </ul>
        </div>

        <!-- Router Selector -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <label class="mr-2 mb-0 font-weight-bold">Router:</label>
                    <select id="selectRouter" class="form-control form-control-sm" style="width: 250px;">
                        <option value="">Seleccionar router...</option>
                    </select>
                    <span id="routerStatusBadge" class="badge badge-secondary ml-2">Sin conexion</span>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-primary btn-sm" onclick="syncAll()">
                    <i class="fas fa-sync-alt"></i> Sincronizar
                </button>
                <a href="<?= base_url() ?>/munired/users" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus"></i> Nuevo Empleado
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row" id="statsCards">
            <div class="col-md-3 col-sm-6 col-6">
                <div class="widget-small primary coloured-icon">
                    <i class="icon fa fa-users fa-3x"></i>
                    <div class="info">
                        <h4 id="statActiveUsers">0</h4>
                        <p><b>Empleados Activos</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-6">
                <div class="widget-small coloured-icon" style="border-left: 3px solid #999;">
                    <i class="icon fa fa-user-slash fa-3x" style="background:#999;"></i>
                    <div class="info">
                        <h4 id="statDisabledUsers">0</h4>
                        <p><b>Inactivos</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-6">
                <div class="widget-small info coloured-icon">
                    <i class="icon fa fa-download fa-3x"></i>
                    <div class="info">
                        <h4 id="statTotalConsumption">--</h4>
                        <p><b>Consumo Total</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-6">
                <div id="routerStatusCard" class="widget-small coloured-icon" style="border-left: 3px solid #999;">
                    <i id="routerStatusIcon" class="icon fa fa-server fa-3x" style="background:#999;"></i>
                    <div class="info">
                        <h4 id="statRouterStatus">--</h4>
                        <p><b>Estado Router</b></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Router offline alert -->
        <div class="row" id="routerOfflineAlert" style="display:none;">
            <div class="col-12">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Router desconectado</strong> — Las metricas de consumo en tiempo real no estan disponibles.
                </div>
            </div>
        </div>

        <!-- Top Consumers Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-title-w-btn">
                        <h3 class="title"><i class="fas fa-chart-bar"></i> Consumo por Empleado</h3>
                        <small class="text-muted" id="bandwidthTimestamp"></small>
                    </div>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="topConsumersTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Empleado</th>
                                        <th>IP</th>
                                        <th>Bajada</th>
                                        <th>Subida</th>
                                        <th>Limite</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="topConsumersBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Cargando datos del router...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Block + Recent Activity -->
        <div class="row">
            <div class="col-md-5">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-ban"></i> Bloqueo Rapido</h3>
                    <div class="tile-body">
                        <div class="form-group mb-0">
                            <div class="input-group">
                                <input type="text" id="quickBlockDomain" class="form-control" placeholder="ejemplo.com">
                                <div class="input-group-append">
                                    <button class="btn btn-danger" onclick="quickBlock()">
                                        <i class="fas fa-ban"></i> Bloquear
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Bloquea el dominio via DNS en el router</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-bell"></i> Actividad Reciente</h3>
                    <div class="tile-body" style="max-height: 250px; overflow-y: auto;">
                        <ul class="list-group" id="recentAlerts">
                            <li class="list-group-item text-muted text-center">Cargando...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<?php footer($data); ?>
<script src="<?= base_style(); ?>/js/functions/<?= $data['page_functions_js']; ?>"></script>
