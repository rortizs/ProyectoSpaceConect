// Content Filter Management JavaScript

// Prevent multiple declarations
if (typeof window.contentFilterLoaded === 'undefined') {
    window.contentFilterLoaded = true;
    
var contentFilterTable;
var clientsTable;  
var logsTable;

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
    
    // Edit Policy Form
    $('#editPolicyForm').submit(function(e) {
        e.preventDefault();
        updatePolicy();
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
    console.log('editPolicy called with ID:', policyId);
    console.log('base_url:', typeof base_url !== 'undefined' ? base_url : 'UNDEFINED');
    
    // Check if modal exists
    if ($('#editPolicyModal').length === 0) {
        console.error('editPolicyModal not found in DOM');
        alert('Error: Modal de edición no encontrado');
        return;
    }
    
    // Test modal directly first
    console.log('Attempting to show modal directly...');
    $('#editPolicyModal').modal('show');
    
    // If modal shows, then load data
    setTimeout(function() {
        if ($('#editPolicyModal').hasClass('show')) {
            console.log('Modal is visible, loading data...');
            loadPolicyData(policyId);
        } else {
            console.error('Modal failed to show');
            alert('Error: El modal no se puede mostrar. Verifique que Bootstrap esté cargado correctamente.');
        }
    }, 500);
}

function loadPolicyData(policyId) {
    // Check if base_url is defined
    if (typeof base_url === 'undefined') {
        console.error('base_url is undefined');
        alert('Error: URL base no definida');
        return;
    }
    
    console.log('Loading policy data for ID:', policyId);
    console.log('AJAX URL:', base_url + '/network/get_policy_details');
    
    $.ajax({
        url: base_url + '/network/get_policy_details',
        type: 'POST',
        data: { policy_id: policyId },
        dataType: 'json', // Expect JSON response
        success: function(response) {
            console.log('AJAX response received:', response);
            console.log('Response type:', typeof response);
            
            if (response && response.result === 'success' && response.policy) {
                const policy = response.policy;
                console.log('Policy data:', policy);
                
                // Fill form fields with validation
                console.log('Setting policy ID:', policy.id);
                $('#edit_policy_id').val(policy.id);
                
                console.log('Setting policy name:', policy.name);
                $('#edit_policy_name').val(policy.name);
                
                console.log('Setting policy description:', policy.description);  
                $('#edit_policy_description').val(policy.description);
                
                // Verify fields were set
                console.log('Verification - ID field value:', $('#edit_policy_id').val());
                console.log('Verification - Name field value:', $('#edit_policy_name').val());
                console.log('Verification - Description field value:', $('#edit_policy_description').val());
                
                // Reset all checkboxes first
                $('.edit-category-checkbox').prop('checked', false);
                console.log('Reset all checkboxes');
                
                // Check selected categories
                if (policy.selected_categories && Array.isArray(policy.selected_categories)) {
                    console.log('Selected categories:', policy.selected_categories);
                    policy.selected_categories.forEach(function(categoryId) {
                        const checkboxId = '#edit_cat_' + categoryId;
                        console.log('Checking checkbox:', checkboxId);
                        $(checkboxId).prop('checked', true);
                    });
                } else {
                    console.log('No selected categories or invalid format');
                }
                
                console.log('Policy data loaded and applied successfully');
            } else {
                console.error('Invalid response format:', response);
                alert('Error: Respuesta inválida del servidor - ' + (response?.message || 'formato incorrecto'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error details:');
            console.error('Status:', status);
            console.error('Error:', error);  
            console.error('Response Text:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            alert('Error al cargar los datos de la política: ' + error + '\nVerifica la consola para más detalles.');
        }
    });
}

function updatePolicy() {
    console.log('updatePolicy called');
    const formData = new FormData($('#editPolicyForm')[0]);
    
    // Debug form data
    console.log('Form data:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    showLoader('Actualizando política...');
    
    console.log('Making AJAX request to:', base_url + '/network/update_policy');
    
    // First, let's test if we can reach a simple endpoint to check session
    console.log('Testing session by calling get_policy_details first...');
    
    $.ajax({
        url: base_url + '/network/get_policy_details',
        type: 'POST', 
        data: { policy_id: 1 },
        success: function(response) {
            console.log('Session test successful:', response);
            proceedWithUpdate();
        },
        error: function(xhr, status, error) {
            console.error('Session test failed:', status, error);
            console.error('Response:', xhr.responseText);
            hideLoader();
            showError('Sesión expirada o error de permisos. Por favor recarga la página.');
        }
    });
}

function proceedWithUpdate() {
    const formData = new FormData($('#editPolicyForm')[0]);
    
    // Debug form data
    console.log('Form data:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    console.log('Proceeding with actual update...');
    
    $.ajax({
        url: base_url + '/network/update_policy',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        // dataType: 'json', // Temporarily commented to debug
        beforeSend: function() {
            console.log('AJAX request starting...');
        },
        success: function(response) {
            console.log('Update response received:', response);
            console.log('Response type:', typeof response);
            console.log('Raw response length:', response ? response.length : 'null');
            
            hideLoader();
            
            // Try to parse JSON if it's a string
            let parsedResponse = response;
            if (typeof response === 'string') {
                try {
                    parsedResponse = JSON.parse(response);
                    console.log('Parsed JSON response:', parsedResponse);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.log('Raw string response:', response);
                }
            }
            
            if (parsedResponse && parsedResponse.result === 'success') {
                console.log('Update successful, closing modal');
                $('#editPolicyModal').modal('hide');
                showSuccess(parsedResponse.message || 'Política actualizada correctamente');
                
                // Refresh page to show updated policy
                setTimeout(() => {
                    console.log('Reloading page...');
                    location.reload();
                }, 1500);
            } else {
                console.log('Update failed:', parsedResponse);
                showError(parsedResponse?.message || 'Error desconocido al actualizar la política');
            }
        },
        error: function(xhr, status, error) {
            console.error('Update AJAX Error:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            hideLoader();
            showError('Error al actualizar la política: ' + error);
        },
        complete: function() {
            console.log('AJAX request completed');
        }
    });
}

function deletePolicy(policyId) {
    $.confirm({
        title: '¿Eliminar Política?',
        content: '¿Está seguro que desea eliminar esta política de filtrado? Esta acción no se puede deshacer y se verificará que no esté en uso por algún cliente.',
        type: 'red',
        buttons: {
            confirm: {
                text: 'Sí, Eliminar',
                btnClass: 'btn-red',
                action: function() {
                    executeDeletePolicy(policyId);
                }
            },
            cancel: {
                text: 'Cancelar',
                btnClass: 'btn-default'
            }
        }
    });
}

function executeDeletePolicy(policyId) {
    showLoader('Eliminando política...');
    
    $.ajax({
        url: base_url + '/network/delete_policy',
        type: 'POST',
        data: { policy_id: policyId },
        success: function(response) {
            hideLoader();
            
            if (response.result === 'success') {
                showSuccess(response.message);
                
                // Refresh page to remove deleted policy
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader();
            showError('Error al eliminar la política');
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
    console.log('showLoader called:', message);
    // Remove existing loader if any
    $('#loadingModal').remove();
    
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

} // End protection block