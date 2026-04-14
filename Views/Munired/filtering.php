<?php head($data); ?>

<div id="divLoading">
    <div><img src="<?= base_style(); ?>/images/loading.svg" alt="Loading"></div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-shield-alt"></i> <?= $data['page_title']; ?></h1>
                <p>Bloqueo de contenido no deseado por departamento</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/munidashboard">Red Municipal</a></li>
                <li class="breadcrumb-item active">Filtrado de Contenido</li>
            </ul>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <select id="filteringDept" class="form-control form-control-sm">
                    <option value="">Seleccionar departamento...</option>
                </select>
            </div>
            <div class="col-md-4">
                <select id="filteringRouter" class="form-control form-control-sm">
                    <option value="">Seleccionar router...</option>
                </select>
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-warning btn-sm" onclick="syncFilteringRules()">
                    <i class="fas fa-sync-alt"></i> Sincronizar con Router
                </button>
            </div>
        </div>

        <!-- Categories (Blacklist) -->
        <div class="row">
            <div class="col-md-7">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-ban"></i> Categorias Bloqueadas</h3>
                    <div class="tile-body">
                        <p class="text-muted mb-3">Seleccione las categorias que desea bloquear para este departamento:</p>
                        <div id="categoryList">
                            <p class="text-muted">Seleccione un departamento primero.</p>
                        </div>
                        <hr>
                        <button class="btn btn-danger btn-sm" onclick="saveFilterPolicy()">
                            <i class="fas fa-save"></i> Guardar Politica de Filtrado
                        </button>
                    </div>
                </div>
            </div>

            <!-- Whitelist -->
            <div class="col-md-5">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-check-circle"></i> Lista Blanca (Excepciones)</h3>
                    <div class="tile-body">
                        <div class="input-group mb-3">
                            <input type="text" id="whitelistDomain" class="form-control form-control-sm" placeholder="dominio.com">
                            <div class="input-group-append">
                                <button class="btn btn-success btn-sm" onclick="addWhitelist()">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="tableWhitelist">
                                <thead>
                                    <tr>
                                        <th>Dominio</th>
                                        <th>Tipo</th>
                                        <th>Agregado por</th>
                                        <th></th>
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
