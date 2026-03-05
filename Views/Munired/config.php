<?php head($data); ?>

<div id="divLoading">
    <div><img src="<?= base_style(); ?>/images/loading.svg" alt="Loading"></div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-cog"></i> <?= $data['page_title']; ?></h1>
                <p>Configuracion general de la red municipal</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/munidashboard">Red Municipal</a></li>
                <li class="breadcrumb-item active">Configuracion</li>
            </ul>
        </div>

        <div class="row">
            <!-- Router Info -->
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-server"></i> Routers Disponibles</h3>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tableRouters">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>IP</th>
                                        <th>Puerto</th>
                                        <th>Rango IP</th>
                                        <th>Estado</th>
                                        <th>Test</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync Overview -->
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-sync-alt"></i> Sincronizacion</h3>
                    <div class="tile-body">
                        <div class="mb-3">
                            <label class="font-weight-bold">Router:</label>
                            <select id="configRouter" class="form-control form-control-sm">
                                <option value="">Seleccionar router...</option>
                            </select>
                        </div>

                        <div class="list-group">
                            <a href="javascript:;" class="list-group-item list-group-item-action" onclick="syncAllFromConfig()">
                                <i class="fas fa-sync-alt text-primary"></i>
                                <strong>Sincronizar TODO</strong>
                                <small class="text-muted d-block">Colas de usuarios (Simple Queues) + Filtrado</small>
                            </a>
                            <a href="javascript:;" class="list-group-item list-group-item-action" onclick="syncFilteringFromConfig()">
                                <i class="fas fa-shield-alt text-danger"></i>
                                <strong>Sincronizar Filtrado</strong>
                                <small class="text-muted d-block">DNS blocks + whitelist por usuario</small>
                            </a>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Los Queue Trees (QoS) son gestionados por Digicom y no se modifican desde este panel.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Router Status (dynamic) -->
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-info-circle"></i> Estado del Router</h3>
                    <div class="tile-body">
                        <div id="routerStatusInfo">
                            <p class="text-muted">Seleccione un router y presione "Test" para ver la informacion del sistema.</p>
                        </div>
                    </div>
                </div>

                <!-- QoS Trees (read-only) -->
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-project-diagram"></i> Queue Trees (solo lectura)</h3>
                    <div class="tile-body">
                        <button class="btn btn-outline-info btn-sm mb-2" onclick="loadQoSFromConfig()">
                            <i class="fas fa-eye"></i> Ver Queue Trees
                        </button>
                        <div id="configQoSPanel"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php footer($data); ?>
<script src="<?= base_style(); ?>/js/functions/<?= $data['page_functions_js']; ?>"></script>
