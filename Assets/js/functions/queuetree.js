// Queue Tree Management JavaScript
let tablePolicies, tableAssignments;
let allPolicies = [];
let allClients = [];
let allTemplates = [];
let allRouters = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    initTables();
    
    // Load initial data
    loadPolicies();
    loadRouters();
    loadTemplates();
    loadClients();
    
    // Add router queue trees functionality
    addRouterQueueTreesTab();
    
    // Form event listeners
    setupFormEvents();
    
    // Tab change events
    setupTabEvents();
});

// Initialize DataTables
function initTables() {
    tablePolicies = $('#tablePolicies').DataTable({
        responsive: true,
        processing: true,
        language: {
            url: base_url + '/Assets/plugins/datatables/spanish.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm'
            }
        ],
        columns: [
            { data: 'id', width: '5%' },
            { data: 'name', width: '15%' },
            { 
                data: null, 
                width: '12%',
                render: function(data, type, row) {
                    return `<small>${row.router_name}<br>${row.router_ip}:${row.router_port}</small>`;
                }
            },
            { data: 'target', width: '12%' },
            { data: 'max_limit', width: '10%' },
            { 
                data: 'burst_limit', 
                width: '10%',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">No</span>';
                }
            },
            { 
                data: 'priority', 
                width: '8%',
                render: function(data, type, row) {
                    const priorityLabels = {
                        1: 'Muy Alta', 2: 'Alta', 3: 'Normal Alta', 4: 'Normal',
                        5: 'Normal Baja', 6: 'Baja', 7: 'Muy Baja', 8: 'Mínima'
                    };
                    return `${data} - ${priorityLabels[data] || 'Normal'}`;
                }
            },
            { 
                data: 'clients_count', 
                width: '8%',
                render: function(data, type, row) {
                    return `<span class="badge bg-info">${data}</span>`;
                }
            },
            { 
                data: 'status', 
                width: '10%',
                render: function(data, type, row) {
                    const statusClass = {
                        'active': 'success',
                        'inactive': 'secondary',
                        'error': 'danger'
                    };
                    return `<span class="badge bg-${statusClass[data] || 'secondary'}">${data}</span>`;
                }
            },
            { 
                data: null, 
                width: '10%',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-warning btn-sm" onclick="editPolicy(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-success btn-sm" onclick="syncPolicy(${row.id})" title="Sincronizar">
                                <i class="fas fa-sync"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deletePolicy(${row.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    tableAssignments = $('#tableAssignments').DataTable({
        responsive: true,
        processing: true,
        language: {
            url: base_url + '/Assets/plugins/datatables/spanish.json'
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.names} ${row.surnames}`;
                }
            },
            { data: 'ip_address' },
            { 
                data: 'policy_name',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">Sin asignar</span>';
                }
            },
            { 
                data: 'upload_limit',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'download_limit',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    if (!row.queue_policy_id) return '<span class="text-muted">-</span>';
                    
                    const priorityLabels = {
                        1: 'Muy Alta', 2: 'Alta', 3: 'Normal Alta', 4: 'Normal',
                        5: 'Normal Baja', 6: 'Baja', 7: 'Muy Baja', 8: 'Mínima'
                    };
                    return priorityLabels[4] || 'Normal'; // Default priority
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    const statusClass = {
                        'active': 'success',
                        'suspended': 'warning',
                        'inactive': 'secondary'
                    };
                    return `<span class="badge bg-${statusClass[data] || 'secondary'}">${data}</span>`;
                }
            },
            { 
                data: 'sync_status',
                render: function(data, type, row) {
                    if (!row.queue_policy_id) return '<span class="text-muted">-</span>';
                    
                    const syncClass = {
                        'synced': 'success',
                        'pending': 'warning',
                        'error': 'danger'
                    };
                    return `<span class="badge bg-${syncClass[data] || 'secondary'}">${data || 'pending'}</span>`;
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let buttons = `
                        <button class="btn btn-primary btn-sm" onclick="assignPolicy(${row.id})" title="Asignar/Editar">
                            <i class="fas fa-cog"></i>
                        </button>
                    `;
                    
                    if (row.queue_policy_id) {
                        buttons += `
                            <button class="btn btn-warning btn-sm" onclick="syncSingleAssignment(${row.id})" title="Sincronizar">
                                <i class="fas fa-sync"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="removeAssignment(${row.id})" title="Quitar">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }
                    
                    return `<div class="btn-group" role="group">${buttons}</div>`;
                }
            }
        ]
    });
}

// Load data functions
function loadPolicies() {
    $.ajax({
        url: base_url + '/queuetree/getPolicies',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                allPolicies = response.data;
                tablePolicies.clear().rows.add(response.data).draw();
                updateMonitoringStats();
            } else {
                showAlert('error', response.message || 'Error al cargar políticas');
            }
        },
        error: function() {
            showAlert('error', 'Error de conexión al cargar políticas');
        }
    });
}

function loadRouters() {
    $.ajax({
        url: base_url + '/queuetree/getRouters',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                allRouters = response.data;
                
                const routerSelect = $('#listRouter');
                routerSelect.empty().append('<option value="">Seleccionar router</option>');
                
                response.data.forEach(router => {
                    routerSelect.append(`<option value="${router.id}">${router.name} (${router.ip}:${router.port})</option>`);
                });
            }
        }
    });
}

function loadTemplates() {
    $.ajax({
        url: base_url + '/queuetree/getTemplates',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                allTemplates = response.data;
                
                // Populate template select
                const templateSelect = $('#listTemplate');
                templateSelect.empty().append('<option value="">Configuración manual</option>');
                
                response.data.forEach(template => {
                    templateSelect.append(`<option value="${template.id}" data-upload="${template.upload_speed}" data-download="${template.download_speed}" data-burst="${template.burst_ratio}" data-priority="${template.priority}">${template.name} (${template.upload_speed}/${template.download_speed})</option>`);
                });
                
                // Display templates in templates tab
                displayTemplatesCards(response.data);
            }
        }
    });
}

function loadClients() {
    $.ajax({
        url: base_url + '/queuetree/getClients',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                allClients = response.data;
                tableAssignments.clear().rows.add(response.data).draw();
                
                // Populate client select
                const clientSelect = $('#listClient');
                clientSelect.empty().append('<option value="">Seleccionar cliente</option>');
                
                response.data.forEach(client => {
                    if (client.ip_address) {
                        clientSelect.append(`<option value="${client.id}" data-ip="${client.ip_address}" data-name="${client.names} ${client.surnames}" data-product="${client.product_name || 'Sin producto'}" data-status="${client.status}">${client.names} ${client.surnames} (${client.ip_address})</option>`);
                    }
                });
                
                updateMonitoringStats();
            }
        }
    });
}

// Display templates as cards
function displayTemplatesCards(templates) {
    const container = $('#templatesContainer');
    container.empty();
    
    const categories = ['residential', 'business', 'premium', 'custom'];
    const categoryNames = {
        'residential': 'Residencial',
        'business': 'Empresarial', 
        'premium': 'Premium',
        'custom': 'Personalizado'
    };
    
    categories.forEach(category => {
        const categoryTemplates = templates.filter(t => t.category === category);
        
        if (categoryTemplates.length > 0) {
            container.append(`<div class="col-12"><h5 class="mt-3">${categoryNames[category]}</h5><hr></div>`);
            
            categoryTemplates.forEach(template => {
                const burstInfo = template.burst_ratio > 1 ? `Burst: ${(template.burst_ratio * 100)}%` : 'Sin burst';
                const priorityLabels = {
                    1: 'Muy Alta', 2: 'Alta', 3: 'Normal Alta', 4: 'Normal',
                    5: 'Normal Baja', 6: 'Baja', 7: 'Muy Baja', 8: 'Mínima'
                };
                
                container.append(`
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title">${template.name}</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text">${template.description}</p>
                                <p><strong>Velocidad:</strong> ${template.upload_speed}/${template.download_speed}</p>
                                <p><strong>Prioridad:</strong> ${priorityLabels[template.priority]} (${template.priority})</p>
                                <p><strong>${burstInfo}</strong></p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary btn-sm" onclick="useTemplate(${template.id})">
                                    <i class="fas fa-plus"></i> Usar Template
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            });
        }
    });
}

// Form event handlers
function setupFormEvents() {
    // Policy form submission
    $('#formQueueTreePolicy').on('submit', function(e) {
        e.preventDefault();
        savePolicy();
    });
    
    // Assignment form submission
    $('#formAssignPolicy').on('submit', function(e) {
        e.preventDefault();
        saveAssignment();
    });
    
    // Client selection change
    $('#listClient').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#clientInfo').show();
            $('#clientName').text(selectedOption.data('name'));
            $('#clientIP').text(selectedOption.data('ip'));
            $('#clientProduct').text(selectedOption.data('product'));
            $('#clientStatus').text(selectedOption.data('status'));
        } else {
            $('#clientInfo').hide();
        }
    });
}

function setupTabEvents() {
    $('#queueTreeTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('data-bs-target');
        
        if (target === '#assignments') {
            loadClients();
        } else if (target === '#monitoring') {
            updateMonitoringStats();
            updateCharts();
        }
    });
}

// Policy management functions
function openPolicyModal() {
    clearPolicyForm();
    $('#titleModal').text('Nueva Política Queue Tree');
    $('#btnActionPolicy').text('Guardar');
    $('#modalQueueTreePolicy').modal('show');
}

function clearPolicyForm() {
    $('#formQueueTreePolicy')[0].reset();
    $('#idPolicy').val('');
    $('#txtParentQueue').val('global');
    $('#listPriority').val('4');
    $('#listQueueType').val('default');
}

function savePolicy() {
    const formData = new FormData($('#formQueueTreePolicy')[0]);
    
    $.ajax({
        url: base_url + '/queuetree/createPolicy',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                showAlert('success', response.message);
                $('#modalQueueTreePolicy').modal('hide');
                loadPolicies();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Error de conexión al guardar política');
        }
    });
}

function editPolicy(id) {
    const policy = allPolicies.find(p => p.id == id);
    if (!policy) return;
    
    // Fill form with policy data
    $('#idPolicy').val(policy.id);
    $('#txtName').val(policy.name);
    $('#listRouter').val(policy.router_id);
    $('#txtParentQueue').val(policy.parent_queue || 'global');
    $('#txtTarget').val(policy.target);
    $('#txtMaxLimit').val(policy.max_limit);
    $('#txtBurstLimit').val(policy.burst_limit || '');
    $('#txtBurstThreshold').val(policy.burst_threshold || '');
    $('#txtBurstTime').val(policy.burst_time || '');
    $('#listPriority').val(policy.priority);
    $('#listQueueType').val(policy.queue_type);
    $('#txtPacketMark').val(policy.packet_mark || '');
    $('#txtConnectionMark').val(policy.connection_mark || '');
    $('#txtDscp').val(policy.dscp || '');
    $('#txtDescription').val(policy.description || '');
    
    $('#titleModal').text('Editar Política Queue Tree');
    $('#btnActionPolicy').text('Actualizar');
    $('#modalQueueTreePolicy').modal('show');
}

function syncPolicy(id) {
    if (!confirm('¿Sincronizar esta política con MikroTik?')) return;
    
    $.ajax({
        url: base_url + '/queuetree/syncPolicy',
        type: 'POST',
        data: { policy_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                showAlert('success', response.message);
                loadPolicies();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Error de conexión al sincronizar');
        }
    });
}

function deletePolicy(id) {
    if (!confirm('¿Eliminar esta política? Esta acción no se puede deshacer.')) return;
    
    $.ajax({
        url: base_url + '/queuetree/deletePolicy',
        type: 'POST',
        data: { policy_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                showAlert('success', response.message);
                loadPolicies();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Error de conexión al eliminar');
        }
    });
}

function syncAllPolicies() {
    if (!confirm('¿Sincronizar todas las políticas con MikroTik?')) return;
    
    showAlert('info', 'Sincronizando políticas...');
    
    // This would need to be implemented in the controller
    showAlert('warning', 'Función en desarrollo');
}

// Assignment management functions
function openAssignModal() {
    clearAssignmentForm();
    loadPoliciesForAssignment();
    $('#titleModalAssign').text('Asignar Política QoS');
    $('#btnActionAssign').text('Asignar Política');
    $('#modalAssignPolicy').modal('show');
}

function clearAssignmentForm() {
    $('#formAssignPolicy')[0].reset();
    $('#idAssignment').val('');
    $('#clientInfo').hide();
    $('#listAssignPriority').val('4');
    $('#chkApplyNow').prop('checked', true);
}

function loadPoliciesForAssignment() {
    const policySelect = $('#listPolicy');
    policySelect.empty().append('<option value="">Seleccionar política</option>');
    
    allPolicies.forEach(policy => {
        policySelect.append(`<option value="${policy.id}">${policy.name} (${policy.max_limit})</option>`);
    });
}

function assignPolicy(clientId) {
    const client = allClients.find(c => c.id == clientId);
    if (!client) return;
    
    clearAssignmentForm();
    loadPoliciesForAssignment();
    
    // Pre-select client
    $('#listClient').val(clientId).trigger('change');
    
    // If client already has a policy, load it
    if (client.queue_policy_id) {
        $('#listPolicy').val(client.queue_policy_id);
        $('#txtUploadLimit').val(client.upload_limit || '');
        $('#txtDownloadLimit').val(client.download_limit || '');
        $('#titleModalAssign').text('Editar Asignación QoS');
        $('#btnActionAssign').text('Actualizar Asignación');
    }
    
    $('#modalAssignPolicy').modal('show');
}

function saveAssignment() {
    const formData = new FormData($('#formAssignPolicy')[0]);
    
    $.ajax({
        url: base_url + '/queuetree/assignPolicy',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                showAlert('success', response.message);
                $('#modalAssignPolicy').modal('hide');
                loadClients();
                
                // If apply now is checked, sync immediately
                if ($('#chkApplyNow').is(':checked')) {
                    setTimeout(() => syncAssignments(), 1000);
                }
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Error de conexión al asignar política');
        }
    });
}

function syncAssignments() {
    showAlert('info', 'Sincronizando asignaciones con MikroTik...');
    
    $.ajax({
        url: base_url + '/queuetree/syncAssignments',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                showAlert('success', response.message);
            } else if (response.result === 'warning') {
                showAlert('warning', response.message);
                if (response.errors && response.errors.length > 0) {
                    console.log('Errores de sincronización:', response.errors);
                }
            } else {
                showAlert('error', response.message);
            }
            loadClients();
            updateMonitoringStats();
        },
        error: function() {
            showAlert('error', 'Error de conexión al sincronizar');
        }
    });
}

// Template functions
function applyTemplate() {
    const selectedOption = $('#listTemplate').find('option:selected');
    if (!selectedOption.val()) return;
    
    const upload = selectedOption.data('upload');
    const download = selectedOption.data('download');
    const burstRatio = selectedOption.data('burst');
    const priority = selectedOption.data('priority');
    
    $('#txtUploadLimit').val(upload);
    $('#txtDownloadLimit').val(download);
    $('#listAssignPriority').val(priority);
    
    if (burstRatio > 1) {
        const uploadValue = parseFloat(upload.replace(/[^0-9.]/g, ''));
        const downloadValue = parseFloat(download.replace(/[^0-9.]/g, ''));
        const uploadUnit = upload.replace(/[0-9.]/g, '');
        const downloadUnit = download.replace(/[0-9.]/g, '');
        
        $('#txtBurstUpload').val((uploadValue * burstRatio).toFixed(0) + uploadUnit);
        $('#txtBurstDownload').val((downloadValue * burstRatio).toFixed(0) + downloadUnit);
    }
}

function useTemplate(templateId) {
    const template = allTemplates.find(t => t.id == templateId);
    if (!template) return;
    
    openAssignModal();
    $('#listTemplate').val(templateId);
    applyTemplate();
}

// Monitoring functions
function updateMonitoringStats() {
    const totalPolicies = allPolicies.length;
    const totalAssignments = allClients.filter(c => c.queue_policy_id).length;
    const pendingSync = allClients.filter(c => c.sync_status === 'pending').length;
    const syncErrors = allClients.filter(c => c.sync_status === 'error').length;
    
    $('#totalPolicies').text(totalPolicies);
    $('#totalAssignments').text(totalAssignments);
    $('#pendingSync').text(pendingSync);
    $('#syncErrors').text(syncErrors);
}

function updateCharts() {
    // This would implement Chart.js charts for monitoring
    // For now, just a placeholder
    console.log('Charts would be updated here');
}

function refreshMonitoring() {
    loadPolicies();
    loadClients();
    updateMonitoringStats();
    updateCharts();
    showAlert('success', 'Datos de monitoreo actualizados');
}

// Utility functions
function showAlert(type, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the main content
    $('.app-content').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Add Router Queue Trees Tab
function addRouterQueueTreesTab() {
    // Add tab if it doesn't exist
    if (!$('#router-queue-trees-tab').length) {
        const tabHtml = `
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="router-queue-trees-tab" data-toggle="tab" href="#router-queue-trees" role="tab" aria-controls="router-queue-trees" aria-selected="false">
                    <i class="fas fa-server"></i> Queue Trees del Router
                </a>
            </li>
        `;
        $('#queueTreeTabs').append(tabHtml);
        
        const tabContentHtml = `
            <div class="tab-pane fade" id="router-queue-trees" role="tabpanel" aria-labelledby="router-queue-trees-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <div class="tile-header">
                                <h3 class="tile-title">Queue Trees Existentes en los Routers</h3>
                                <div class="tile-elements">
                                    <button class="btn btn-info" type="button" onclick="loadRouterQueueTrees()">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                            <div class="tile-body">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-info-circle"></i> Información:</strong>
                                    Esta tabla muestra todas las Queue Trees configuradas directamente en los routers MikroTik.
                                    Se incluyen tanto las creadas desde el sistema como las configuradas manualmente en Winbox.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="tableRouterQueueTrees">
                                        <thead>
                                            <tr>
                                                <th>Router</th>
                                                <th>Nombre</th>
                                                <th>Parent</th>
                                                <th>Packet Marks</th>
                                                <th>Max Limit</th>
                                                <th>Burst Limit</th>
                                                <th>Prioridad</th>
                                                <th>Bytes</th>
                                                <th>Packets</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#queueTreeTabContent').append(tabContentHtml);
        
        // Initialize the new table
        $('#tableRouterQueueTrees').DataTable({
            responsive: true,
            processing: true,
            language: {
                url: base_url + '/Assets/plugins/datatables/spanish.json'
            },
            pageLength: 25,
            order: [[0, "asc"]], // Order by router name
            columnDefs: [
                {
                    targets: [7, 8], // bytes, packets columns
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return formatBytes(data);
                        }
                        return data;
                    }
                },
                {
                    targets: 9, // Status column
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (row[1] === 'ROUTER NO CONECTADO') {
                                return '<span class="badge badge-secondary">Sin conexión</span>';
                            } else if (row[1] === 'ERROR DE CONEXIÓN') {
                                return '<span class="badge badge-danger">Error</span>';
                            } else {
                                return '<span class="badge badge-success">Activo</span>';
                            }
                        }
                        return data;
                    }
                }
            ]
        });
        
        // Load data automatically
        loadRouterQueueTrees();
    }
}

// Load Queue Trees from routers
function loadRouterQueueTrees() {
    showAlert('info', 'Cargando Queue Trees de los routers...');
    
    $.ajax({
        url: base_url + '/queuetree/get_router_queue_trees',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                populateRouterQueueTreesTable(response.data);
                showAlert('success', `Se cargaron ${response.total} Queue Trees de ${getUniqueRouterCount(response.data)} router(s)`);
            } else {
                showAlert('danger', response.message || 'Error al cargar Queue Trees');
            }
        },
        error: function(xhr, status, error) {
            try {
                const response = JSON.parse(xhr.responseText);
                showAlert('danger', response.message || 'Tu sesión ha expirado. Por favor recarga la página.');
            } catch (e) {
                showAlert('danger', 'Error de red. Verifica tu conexión.');
            }
        }
    });
}

// Populate router queue trees table
function populateRouterQueueTreesTable(data) {
    const table = $('#tableRouterQueueTrees').DataTable();
    
    // Clear existing data
    table.clear();
    
    // Add new data
    data.forEach(function(item) {
        const row = [
            `<strong>${item.router_name}</strong><br><small class="text-muted">${item.router_ip}</small>`,
            item.name,
            item.parent,
            item.packet_marks,
            item.max_limit,
            item.burst_limit,
            item.priority,
            item.bytes,
            item.packets,
            '' // Status column will be handled by columnDefs
        ];
        
        table.row.add(row);
    });
    
    // Redraw table
    table.draw();
}

// Get unique router count
function getUniqueRouterCount(data) {
    const uniqueRouters = new Set(data.map(item => item.router_id));
    return uniqueRouters.size;
}

// Format bytes function
function formatBytes(bytes) {
    if (bytes === 0 || bytes === '0') return '0 B';
    if (!bytes || bytes === 'N/A') return 'N/A';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}