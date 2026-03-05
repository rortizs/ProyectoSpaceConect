<?php head($data); ?>

<div id="divLoading">
    <div><img src="<?= base_style(); ?>/images/loading.svg" alt="Loading"></div>
</div>

<div id="contentAjax">
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-users"></i> <?= $data['page_title']; ?></h1>
                <p>Gestionar usuarios de oficina y sus IPs</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/munidashboard">Red Municipal</a></li>
                <li class="breadcrumb-item active">Usuarios</li>
            </ul>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterDepartment" class="form-control form-control-sm">
                    <option value="">Todos los departamentos</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filterStatus" class="form-control form-control-sm">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Buscar por nombre o IP...">
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-outline-secondary btn-sm" onclick="loadUsers()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <?php if ($_SESSION['permits_module']['r']) { ?>
                    <button class="btn btn-primary btn-sm" onclick="openUserModal()">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tableUsers">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Departamento</th>
                                        <th>IP</th>
                                        <th>MAC</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                        <th>Sync</th>
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

<!-- Modal Usuario -->
<div class="modal fade" id="modalUser" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserTitle">Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formUser">
                <input type="hidden" id="userId" name="id" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="userName" name="name" required placeholder="Ej: Juan Perez - Contabilidad">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Departamento</label>
                                <select class="form-control" id="userDepartment" name="department_id">
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Direccion IP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="userIp" name="ip_address" required placeholder="Ej: 10.0.2.10">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-info" type="button" onclick="showAvailableIPs()" title="Ver IPs disponibles">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Direccion MAC</label>
                                <input type="text" class="form-control" id="userMac" name="mac_address" placeholder="AA:BB:CC:DD:EE:FF">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6><i class="fas fa-tachometer-alt"></i> Ancho de Banda Personalizado (Opcional)</h6>
                    <small class="text-muted mb-2 d-block">Dejar vacio para usar el ancho de banda por defecto del departamento</small>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Upload Personalizado</label>
                                <input type="text" class="form-control" id="userUpload" name="custom_upload" placeholder="Ej: 3M">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Download Personalizado</label>
                                <input type="text" class="form-control" id="userDownload" name="custom_download" placeholder="Ej: 8M">
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

<!-- Modal IPs Disponibles -->
<div class="modal fade" id="modalAvailableIPs" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">IPs Disponibles</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="availableIPsInfo" class="mb-2"></div>
                <div id="availableIPsList" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>

<?php footer($data); ?>
<script src="<?= base_style(); ?>/js/functions/<?= $data['page_functions_js']; ?>"></script>
