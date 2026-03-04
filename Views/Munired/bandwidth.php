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
                <button class="btn btn-warning btn-sm" onclick="syncQoS()">
                    <i class="fas fa-project-diagram"></i> Sincronizar QoS
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
                    <h3 class="tile-title"><i class="fas fa-project-diagram"></i> Jerarquia QoS (Queue Tree)</h3>
                    <div class="tile-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Estructura:</strong> Queue Tree padre <code>muni-global</code> (37M/251M) &rarr; un hijo por departamento con su limite maximo.
                            Cada usuario tiene su Simple Queue individual.
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
