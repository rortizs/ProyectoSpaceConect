<?php headerAdmin($data) ?>
<link rel="stylesheet" href="<?= media() ?>/css/jquery-confirm.min.css" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h5 class="m-b-10"><?= $data['page_title'] ?></h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>/dashboard">
                            <i class="feather icon-home"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>/network/routers">Gestión de Red</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#!"><?= $data['actual_page'] ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-c-yellow"><?= $data['stats']['total_policies'] ?></h4>
                        <h6 class="text-muted m-b-0">Políticas Activas</h6>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fas fa-shield-alt f-28 text-c-yellow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-c-blue"><?= $data['stats']['filtered_clients'] ?></h4>
                        <h6 class="text-muted m-b-0">Clientes Filtrados</h6>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fas fa-users f-28 text-c-blue"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-c-green"><?= $data['stats']['total_categories'] ?></h4>
                        <h6 class="text-muted m-b-0">Categorías</h6>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fas fa-tags f-28 text-c-green"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h4 class="text-c-red"><?= $data['stats']['blocked_domains'] ?></h4>
                        <h6 class="text-muted m-b-0">Dominios Bloqueados</h6>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fas fa-ban f-28 text-c-red"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Content Filter Management -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Gestión de Filtro de Contenido</h5>
                <div class="card-header-right">
                    <div class="btn-group card-option">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openNewPolicyModal()">
                            <i class="fas fa-plus"></i> Nueva Política
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="openBulkApplyModal()">
                            <i class="fas fa-layer-group"></i> Aplicar Masivo
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="filterTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="policies-tab" data-toggle="tab" href="#policies" role="tab">
                            <i class="fas fa-shield-alt"></i> Políticas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="clients-tab" data-toggle="tab" href="#clients" role="tab">
                            <i class="fas fa-users"></i> Clientes sin Filtro
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="categories-tab" data-toggle="tab" href="#categories" role="tab">
                            <i class="fas fa-tags"></i> Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="logs-tab" data-toggle="tab" href="#logs" role="tab">
                            <i class="fas fa-history"></i> Registro de Actividad
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="filterTabsContent">
                    <!-- Policies Tab -->
                    <div class="tab-pane fade show active" id="policies" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table id="policiesTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Política</th>
                                        <th>Descripción</th>
                                        <th>Categorías Bloqueadas</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['policies'] as $policy): ?>
                                    <tr>
                                        <td>
                                            <?= $policy['name'] ?>
                                            <?php if($policy['is_default']): ?>
                                                <span class="badge badge-primary">Por Defecto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $policy['description'] ?></td>
                                        <td>
                                            <?php foreach($policy['categories'] as $cat): ?>
                                                <span class="badge badge-secondary" style="background-color: <?= $cat['color'] ?>">
                                                    <?= $cat['name'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <?php if($policy['is_active']): ?>
                                                <span class="badge badge-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPolicy(<?= $policy['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePolicy(<?= $policy['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Clients Tab -->
                    <div class="tab-pane fade" id="clients" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table id="clientsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllClients" onchange="toggleAllClients(this)">
                                        </th>
                                        <th>Cliente</th>
                                        <th>IP</th>
                                        <th>Router</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['unfiltered_clients'] as $client): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="client-checkbox" value="<?= $client['id'] ?>">
                                        </td>
                                        <td><?= $client['name'] ?></td>
                                        <td><?= $client['net_ip'] ?></td>
                                        <td><?= $client['router_name'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="applyFilterToClient(<?= $client['id'] ?>, <?= $client['net_router'] ?>)">
                                                <i class="fas fa-shield-alt"></i> Aplicar Filtro
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="categories" role="tabpanel">
                        <div class="row mt-3">
                            <?php foreach($data['categories'] as $category): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    <?= $category['name'] ?>
                                                </div>
                                                <div class="text-xs mb-0 text-gray-800">
                                                    <?= $category['description'] ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="<?= $category['icon'] ?> fa-2x" style="color: <?= $category['color'] ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Logs Tab -->
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table id="logsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Router</th>
                                        <th>Acción</th>
                                        <th>Política</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['recent_logs'] as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td><?= $log['client_name'] ?></td>
                                        <td><?= $log['router_name'] ?></td>
                                        <td>
                                            <?php
                                            $action_icons = [
                                                'apply' => 'fas fa-shield-alt text-success',
                                                'remove' => 'fas fa-shield-alt text-danger',
                                                'update' => 'fas fa-edit text-warning'
                                            ];
                                            ?>
                                            <i class="<?= $action_icons[$log['action']] ?>"></i> <?= ucfirst($log['action']) ?>
                                        </td>
                                        <td><?= $log['policy_name'] ?></td>
                                        <td>
                                            <?php if($log['status'] === 'success'): ?>
                                                <span class="badge badge-success">Exitoso</span>
                                            <?php elseif($log['status'] === 'error'): ?>
                                                <span class="badge badge-danger">Error</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Policy Modal -->
<div class="modal fade" id="newPolicyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Política de Filtrado</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="newPolicyForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre de la Política</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Categorías a Bloquear</label>
                        <div class="row">
                            <?php foreach($data['categories'] as $category): ?>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="cat_<?= $category['id'] ?>" name="category_ids[]" value="<?= $category['id'] ?>">
                                    <label class="custom-control-label" for="cat_<?= $category['id'] ?>">
                                        <i class="<?= $category['icon'] ?>" style="color: <?= $category['color'] ?>"></i>
                                        <?= $category['name'] ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1">
                            <label class="custom-control-label" for="is_default">
                                Establecer como política por defecto
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Política</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Apply Filter Modal -->
<div class="modal fade" id="applyFilterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aplicar Filtro de Contenido</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="applyFilterForm">
                <div class="modal-body">
                    <input type="hidden" name="client_id" id="filter_client_id">
                    <input type="hidden" name="router_id" id="filter_router_id">
                    
                    <div class="form-group">
                        <label>Seleccionar Política</label>
                        <select class="form-control" name="policy_id" required>
                            <option value="">Seleccione una política...</option>
                            <?php foreach($data['policies'] as $policy): ?>
                            <option value="<?= $policy['id'] ?>" <?= $policy['is_default'] ? 'selected' : '' ?>>
                                <?= $policy['name'] ?> <?= $policy['is_default'] ? '(Por defecto)' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Se aplicará el filtro de contenido al cliente seleccionado. Los dominios bloqueados serán redirigidos según la política elegida.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Apply Modal -->
<div class="modal fade" id="bulkApplyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aplicar Filtro Masivo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkApplyForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Seleccionar Política</label>
                        <select class="form-control" name="policy_id" required>
                            <option value="">Seleccione una política...</option>
                            <?php foreach($data['policies'] as $policy): ?>
                            <option value="<?= $policy['id'] ?>" <?= $policy['is_default'] ? 'selected' : '' ?>>
                                <?= $policy['name'] ?> <?= $policy['is_default'] ? '(Por defecto)' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Se aplicará el filtro a todos los clientes seleccionados. Esta operación puede tomar varios minutos.
                    </div>
                    
                    <div id="selectedClientsInfo"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Aplicar a Seleccionados</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php footerAdmin($data) ?>

<script src="<?= media() ?>/js/jquery-confirm.min.js"></script>
<script src="<?= media() ?>/js/functions/<?= $data['page_functions_js'] ?>"></script>