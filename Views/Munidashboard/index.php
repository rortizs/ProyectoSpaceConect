<?php head($data); ?>

<!-- Municipal Dashboard Custom Styles -->
<link rel="stylesheet" href="<?= base_url(); ?>/Assets/css/munidashboard.css">

<div id="divLoading">
    <div>
        <img src="<?= base_style(); ?>/images/loading.svg" alt="Loading">
    </div>
</div>

<div id="contentAjax">
    <main class="app-content muni-dashboard">
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

        <!-- Stats Cards - Redesigned -->
        <div class="muni-stats-grid" id="statsCards">
            <div class="muni-stat-card muni-stat-card--ok">
                <div class="muni-stat-card__label">Empleados Activos</div>
                <div class="muni-stat-card__value" id="statActiveUsers">0</div>
                <div class="muni-stat-card__meta">
                    <i class="fas fa-users"></i>
                    <span>Con ancho de banda asignado</span>
                </div>
            </div>
            <div class="muni-stat-card muni-stat-card--warning" id="statRiskCard">
                <div class="muni-stat-card__label">En Zona de Riesgo</div>
                <div class="muni-stat-card__value" id="statRiskUsers">0</div>
                <div class="muni-stat-card__meta">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="statRiskMeta">&gt; 70% del límite</span>
                </div>
            </div>
            <div class="muni-stat-card muni-stat-card--info">
                <div class="muni-stat-card__label">Consumo Total (Bajada)</div>
                <div class="muni-stat-card__value" id="statTotalConsumption">--</div>
                <div class="muni-stat-card__meta">
                    <i class="fas fa-download"></i>
                    <span>Último minuto</span>
                </div>
            </div>
            <div class="muni-stat-card" id="routerStatusCard">
                <div class="muni-stat-card__label">Estado Router</div>
                <div class="muni-stat-card__value" id="statRouterStatus">--</div>
                <div class="muni-stat-card__meta">
                    <i class="fas fa-server"></i>
                    <span id="statRouterMeta">Verificando...</span>
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

        <!-- Employee Bandwidth Table - Redesigned -->
        <div class="row">
            <div class="col-md-12">
                <div class="muni-table-container">
                    <div class="muni-table-header">
                        <h3 class="muni-table-header__title">
                            <i class="fas fa-chart-bar"></i> Consumo por Empleado
                        </h3>
                        <div class="muni-table-header__meta" id="bandwidthTimestamp">
                            Cargando...
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="muni-table" id="topConsumersTable">
                            <thead>
                                <tr>
                                    <th class="muni-table__rank">#</th>
                                    <th>Empleado</th>
                                    <th>IP</th>
                                    <th>Uso de Ancho de Banda</th>
                                    <th>Consumo (Bajada / Subida)</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="topConsumersBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted" style="padding: 48px;">
                                        <i class="fas fa-spinner fa-spin muni-loading" style="font-size: 2rem; opacity: 0.3;"></i>
                                        <div style="margin-top: 16px; color: #718096;">Cargando datos del router...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Block + Actionable Alerts -->
        <div class="row">
            <div class="col-md-5">
                <div class="muni-table-container">
                    <div class="muni-table-header">
                        <h3 class="muni-table-header__title">
                            <i class="fas fa-ban"></i> Bloqueo Rápido
                        </h3>
                    </div>
                    <div style="padding: 24px;">
                        <div class="form-group mb-0">
                            <div class="input-group">
                                <input type="text" id="quickBlockDomain" class="form-control" placeholder="ejemplo.com" 
                                       style="font-family: var(--font-mono); font-size: 0.875rem;">
                                <div class="input-group-append">
                                    <button class="btn btn-danger" onclick="quickBlock()">
                                        <i class="fas fa-ban"></i> Bloquear
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted" style="margin-top: 8px;">
                                Bloquea el dominio vía DNS en el router seleccionado
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="muni-alerts">
                    <div class="muni-alerts__header">
                        <h3 class="muni-alerts__title">
                            <i class="fas fa-bell"></i> Alertas y Eventos
                        </h3>
                    </div>
                    <div class="muni-alerts__body" id="recentAlertsContainer">
                        <div class="muni-empty-state">
                            <div class="muni-empty-state__icon">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <div class="muni-empty-state__text">Cargando actividad reciente...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<?php footer($data); ?>
