// Content Filter Management JavaScript

let contentFilterTable;
let clientsTable;
let logsTable;

document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTables
    initializeDataTables();
    
    // Initialize forms
    initializeForms();
    
    // Load initial data
    loadFilterData();
});

function initializeDataTables() {
    // Policies table
    contentFilterTable = $('#policiesTable').DataTable({
        "language": {
            "url": base_url + "/Assets/css/datatables.min.css"
        },
        "responsive": true,
        "pageLength": 25,
        "order": [[0, "asc"]]
    });
    
    // Clients table
    clientsTable = $('#clientsTable').DataTable({
        "language": {
            "url": base_url + "/Assets/css/datatables.min.css"
        },
        "responsive": true,
        "pageLength": 25,
        "order": [[1, "asc"]]
    });
    
    // Logs table
    logsTable = $('#logsTable').DataTable({
        "language": {
            "url": base_url + "/Assets/css/datatables.min.css"
        },
        "responsive": true,
        "pageLength": 25,
        "order": [[0, "desc"]]
    });
}

function initializeForms() {
    // New Policy Form
    $('#newPolicyForm').submit(function(e) {
        e.preventDefault();
        createNewPolicy();
    });
    
    // Apply Filter Form
    $('#applyFilterForm').submit(function(e) {
        e.preventDefault();
        submitApplyFilter();
    });
    
    // Bulk Apply Form
    $('#bulkApplyForm').submit(function(e) {
        e.preventDefault();
        submitBulkApply();
    });
}

function loadFilterData() {
    // This function can be used to refresh data if needed
    console.log('Content Filter module loaded');
}

// Policy Management Functions
function openNewPolicyModal() {
    $('#newPolicyModal').modal('show');
}

function createNewPolicy() {
    const formData = new FormData($('#newPolicyForm')[0]);
    
    showLoader('Creando política...');
    
    $.ajax({
        url: base_url + '/network/create_filter_policy',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoader();
            
            if (response.result === 'success') {
                $('#newPolicyModal').modal('hide');
                showSuccess(response.message);
                
                // Refresh page to show new policy
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader();
            showError('Error al crear la política');
        }
    });
}

function editPolicy(policyId) {
    // Implementation for editing policies
    showInfo('Funcionalidad de edición en desarrollo');
}

function deletePolicy(policyId) {
    $.confirm({
        title: '¿Eliminar Política?',
        content: '¿Está seguro que desea eliminar esta política de filtrado? Esta acción no se puede deshacer.',
        type: 'red',
        buttons: {
            confirm: {
                text: 'Sí, Eliminar',
                btnClass: 'btn-red',
                action: function() {
                    // Implementation for deleting policies
                    showInfo('Funcionalidad de eliminación en desarrollo');
                }
            },
            cancel: {
                text: 'Cancelar',
                btnClass: 'btn-default'
            }
        }
    });
}

// Client Filter Management Functions
function applyFilterToClient(clientId, routerId) {
    $('#filter_client_id').val(clientId);
    $('#filter_router_id').val(routerId);
    $('#applyFilterModal').modal('show');
}

function submitApplyFilter() {
    const formData = new FormData($('#applyFilterForm')[0]);
    
    showLoader('Aplicando filtro de contenido...');
    
    $.ajax({
        url: base_url + '/network/apply_content_filter',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoader();
            
            if (response.result === 'success') {
                $('#applyFilterModal').modal('hide');
                showSuccess(response.message + (response.domains_blocked ? ` (${response.domains_blocked} dominios bloqueados)` : ''));
                
                // Refresh clients table
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader();
            showError('Error al aplicar el filtro');
        }
    });
}

function removeFilterFromClient(clientId, routerId) {
    $.confirm({
        title: '¿Remover Filtro?',
        content: '¿Está seguro que desea remover el filtro de contenido de este cliente?',
        type: 'orange',
        buttons: {
            confirm: {
                text: 'Sí, Remover',
                btnClass: 'btn-orange',
                action: function() {
                    executeRemoveFilter(clientId, routerId);
                }
            },
            cancel: {
                text: 'Cancelar',
                btnClass: 'btn-default'
            }
        }
    });
}

function executeRemoveFilter(clientId, routerId) {
    showLoader('Removiendo filtro de contenido...');
    
    $.ajax({
        url: base_url + '/network/remove_content_filter',
        type: 'POST',
        data: {
            client_id: clientId,
            router_id: routerId
        },
        success: function(response) {
            hideLoader();
            
            if (response.result === 'success') {
                showSuccess(response.message + (response.domains_unblocked ? ` (${response.domains_unblocked} dominios desbloqueados)` : ''));
                
                // Refresh page
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader();
            showError('Error al remover el filtro');
        }
    });
}

// Bulk Operations Functions
function toggleAllClients(checkbox) {
    $('.client-checkbox').prop('checked', checkbox.checked);
    updateSelectedClientsInfo();
}

function updateSelectedClientsInfo() {
    const selectedCount = $('.client-checkbox:checked').length;
    $('#selectedClientsInfo').html(
        selectedCount > 0 ? 
        `<div class="alert alert-info">Se aplicará el filtro a <strong>${selectedCount}</strong> cliente(s) seleccionado(s).</div>` :
        '<div class="alert alert-warning">No hay clientes seleccionados.</div>'
    );
}

function openBulkApplyModal() {
    const selectedClients = $('.client-checkbox:checked');
    
    if (selectedClients.length === 0) {
        showWarning('Por favor seleccione al menos un cliente');
        return;
    }
    
    updateSelectedClientsInfo();
    $('#bulkApplyModal').modal('show');
}

function submitBulkApply() {
    const selectedClientIds = [];
    $('.client-checkbox:checked').each(function() {
        selectedClientIds.push($(this).val());
    });
    
    if (selectedClientIds.length === 0) {
        showWarning('No hay clientes seleccionados');
        return;
    }
    
    const formData = new FormData($('#bulkApplyForm')[0]);
    formData.append('client_ids', selectedClientIds.join(','));
    
    showLoader('Aplicando filtros... Esto puede tomar varios minutos.');
    
    $.ajax({
        url: base_url + '/network/bulk_apply_filter',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoader();
            
            if (response.result === 'success') {
                $('#bulkApplyModal').modal('hide');
                
                let message = `Operación completada: ${response.success_count} exitosos`;
                if (response.error_count > 0) {
                    message += `, ${response.error_count} errores`;
                }
                
                if (response.error_count > 0) {
                    showWarning(message);
                } else {
                    showSuccess(message);
                }
                
                // Refresh page
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader();
            showError('Error en la aplicación masiva de filtros');
        }
    });
}

// Utility Functions
function showSuccess(message) {
    $.gritter.add({
        title: 'Éxito',
        text: message,
        class_name: 'color success'
    });
}

function showError(message) {
    $.gritter.add({
        title: 'Error',
        text: message,
        class_name: 'color danger'
    });
}

function showWarning(message) {
    $.gritter.add({
        title: 'Advertencia',
        text: message,
        class_name: 'color warning'
    });
}

function showInfo(message) {
    $.gritter.add({
        title: 'Información',
        text: message,
        class_name: 'color info'
    });
}

function showLoader(message = 'Procesando...') {
    $('body').append(`
        <div id="loadingModal" class="modal fade" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2">${message}</div>
                    </div>
                </div>
            </div>
        </div>
    `);
    $('#loadingModal').modal('show');
}

function hideLoader() {
    $('#loadingModal').modal('hide');
    setTimeout(() => {
        $('#loadingModal').remove();
    }, 500);
}

// Event Listeners
$(document).ready(function() {
    // Update bulk info when checkboxes change
    $(document).on('change', '.client-checkbox', updateSelectedClientsInfo);
    
    // Tab change events
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Refresh DataTables when tab is shown
        setTimeout(() => {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }, 100);
    });
});