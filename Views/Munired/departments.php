<?php head($data); ?>

<div id="divLoading">
    <div><img src="<?= base_style(); ?>/images/loading.svg" alt="Loading"></div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-sitemap"></i> <?= $data['page_title']; ?></h1>
                <p>Gestionar departamentos y sus rangos IP</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/munidashboard">Red Municipal</a></li>
                <li class="breadcrumb-item active">Departamentos</li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-title-w-btn">
                        <h3 class="title">Listado de Departamentos</h3>
                        <div>
                            <select id="filterRouter" class="form-control form-control-sm d-inline-block" style="width: 200px;">
                                <option value="">Todos los routers</option>
                            </select>
                            <?php if ($_SESSION['permits_module']['r']) { ?>
                                <button class="btn btn-primary btn-sm ml-2" onclick="openDepartmentModal()">
                                    <i class="fas fa-plus"></i> Nuevo Departamento
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tableDepartments">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Router</th>
                                        <th>Rango IP</th>
                                        <th>Prioridad</th>
                                        <th>BW Default</th>
                                        <th>QoS Max</th>
                                        <th>Usuarios</th>
                                        <th>QoS Status</th>
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
    </main>
</div>

<!-- Modal Departamento -->
<div class="modal fade" id="modalDepartment" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDepartmentTitle">Nuevo Departamento</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formDepartment">
                <input type="hidden" id="deptId" name="id" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="deptName" name="name" required placeholder="Ej: Finanzas">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Router <span class="text-danger">*</span></label>
                                <select class="form-control" id="deptRouter" name="router_id" required>
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango IP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="deptIpRange" name="ip_range" required placeholder="Ej: 192.168.88.10-192.168.88.50">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Prioridad (1-8)</label>
                                <input type="number" class="form-control" id="deptPriority" name="priority" min="1" max="8" value="4">
                                <small class="text-muted">1 = maxima</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Descripcion</label>
                                <input type="text" class="form-control" id="deptDescription" name="description" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6><i class="fas fa-tachometer-alt"></i> Ancho de Banda por Defecto</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Upload</label>
                                <input type="text" class="form-control" id="deptUpload" name="default_upload" value="5M" placeholder="Ej: 5M">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Download</label>
                                <input type="text" class="form-control" id="deptDownload" name="default_download" value="10M" placeholder="Ej: 10M">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>QoS Max Limit (Queue Tree)</label>
                                <input type="text" class="form-control" id="deptQosMaxLimit" name="qos_max_limit" placeholder="Ej: 8M/50M (upload/download)">
                                <small class="text-muted">Limite maximo para todo el departamento</small>
                            </div>
                        </div>
                    </div>

                    <h6><i class="fas fa-bolt"></i> Burst (Opcional)</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Burst Upload</label>
                                <input type="text" class="form-control" name="burst_upload" placeholder="Ej: 8M">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Burst Download</label>
                                <input type="text" class="form-control" name="burst_download" placeholder="Ej: 15M">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Burst Threshold Up</label>
                                <input type="text" class="form-control" name="burst_threshold_up" placeholder="Ej: 4M">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Burst Time</label>
                                <input type="text" class="form-control" name="burst_time" placeholder="Ej: 8s/4s">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php footer($data); ?>
<script src="<?= base_style(); ?>/js/functions/<?= $data['page_functions_js']; ?>"></script>
