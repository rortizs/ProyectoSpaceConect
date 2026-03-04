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
                <p>Panel de control simplificado de la red municipal</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Red Municipal</li>
            </ul>
        </div>

        <!-- Router Status Bar -->
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
                    <i class="fas fa-sync-alt"></i> Sincronizar Todo
                </button>
                <a href="<?= base_url() ?>/munired/users" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row" id="statsCards">
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small primary coloured-icon">
                    <i class="icon fa fa-users fa-3x"></i>
                    <div class="info">
                        <h4 id="statActiveUsers">0</h4>
                        <p><b>Usuarios Activos</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small info coloured-icon">
                    <i class="icon fa fa-sitemap fa-3x"></i>
                    <div class="info">
                        <h4 id="statDepartments">0</h4>
                        <p><b>Departamentos</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small warning coloured-icon">
                    <i class="icon fa fa-clock fa-3x"></i>
                    <div class="info">
                        <h4 id="statPendingSync">0</h4>
                        <p><b>Sync Pendiente</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small danger coloured-icon">
                    <i class="icon fa fa-exclamation-triangle fa-3x"></i>
                    <div class="info">
                        <h4 id="statSyncErrors">0</h4>
                        <p><b>Errores Sync</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small primary coloured-icon">
                    <i class="icon fa fa-ban fa-3x"></i>
                    <div class="info">
                        <h4 id="statBlockedDomains">0</h4>
                        <p><b>Dominios Bloqueados</b></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6">
                <div class="widget-small info coloured-icon">
                    <i class="icon fa fa-filter fa-3x"></i>
                    <div class="info">
                        <h4 id="statCategories">0</h4>
                        <p><b>Categorias Filtro</b></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Summary Cards -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-title-w-btn">
                        <h3 class="title"><i class="fas fa-sitemap"></i> Departamentos</h3>
                        <a href="<?= base_url() ?>/munired/departments" class="btn btn-outline-primary btn-sm">
                            Ver Todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="tile-body">
                        <div class="row" id="departmentCards">
                            <div class="col-12 text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Cargando departamentos...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Block + Recent Alerts -->
        <div class="row">
            <!-- Quick Domain Block -->
            <div class="col-md-4">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-ban"></i> Bloqueo Rapido</h3>
                    <div class="tile-body">
                        <div class="form-group">
                            <label>Dominio a bloquear</label>
                            <div class="input-group">
                                <input type="text" id="quickBlockDomain" class="form-control" placeholder="ejemplo.com">
                                <div class="input-group-append">
                                    <button class="btn btn-danger" onclick="quickBlock()">
                                        <i class="fas fa-ban"></i> Bloquear
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Bloquea el dominio en todos los departamentos via DNS</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Router Info -->
            <div class="col-md-3">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-server"></i> Estado del Router</h3>
                    <div class="tile-body" id="routerInfo">
                        <p class="text-muted">Seleccione un router para ver su estado.</p>
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="col-md-5">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-bell"></i> Actividad Reciente</h3>
                    <div class="tile-body" style="max-height: 300px; overflow-y: auto;">
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
