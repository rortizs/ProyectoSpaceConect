<?php head($data); ?>

<div id="divLoading">
    <div><img src="<?= base_style(); ?>/images/loading.svg" alt="Loading"></div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> <?= $data['page_title']; ?></h1>
                <p>Control de ancho de banda y QoS por departamento</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/munidashboard">Red Municipal</a></li>
                <li class="breadcrumb-item active">Ancho de Banda</li>
            </ul>
        </div>

        <!-- Router selector + Sync -->
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="bwRouter" class="form-control form-control-sm">
                    <option value="">Seleccionar router...</option>
                </select>
            </div>
            <div class="col-md-8 text-right">
                <button class="btn btn-outline-secondary btn-sm" onclick="loadQoSStatus()">
                    <i class="fas fa-project-diagram"></i> Ver Queue Trees
                </button>
                <button class="btn btn-info btn-sm" onclick="syncAllQueues()">
                    <i class="fas fa-sync-alt"></i> Sincronizar Colas
                </button>
            </div>
        </div>

        <!-- QoS Hierarchy Info -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-project-diagram"></i> Ancho de Banda por Departamento</h3>
                    <div class="tile-body">
                        <div class="alert alert-secondary">
                            <i class="fas fa-info-circle"></i>
                            <strong>Queue Trees:</strong> Administrados por Digicom (DESCARGAS/SUBIDAS). Use el boton "Ver Queue Trees" para consultar el estado actual.
                            Los Simple Queues por usuario se gestionan desde este panel.
                        </div>

                        <!-- QoS Status Panel (read-only, loaded on demand) -->
                        <div id="qosStatusPanel" style="display: none;" class="mb-3">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <i class="fas fa-project-diagram"></i> Queue Trees del Router (solo lectura)
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0" id="tableQoSTrees">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Parent</th>
                                                <th>Max Limit</th>
                                                <th>Prioridad</th>
                                                <th>Comentario</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tableBandwidth">
                                <thead>
                                    <tr>
                                        <th>Departamento</th>
                                        <th>Prioridad</th>
                                        <th>QoS Max Limit</th>
                                        <th>BW Default (Up/Down)</th>
                                        <th>Usuarios Activos</th>
                                        <th>QoS Status</th>
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

        <!-- Per-User Bandwidth in selected department -->
        <div class="row" id="userBandwidthSection" style="display: none;">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-users"></i> Usuarios - <span id="selectedDeptName"></span></h3>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tableUserBandwidth">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>IP</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                        <th>Cola MikroTik</th>
                                        <th>Sync Status</th>
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
    </main>
</div>

<?php footer($data); ?>
<script src="<?= base_style(); ?>/js/functions/<?= $data['page_functions_js']; ?>"></script>
