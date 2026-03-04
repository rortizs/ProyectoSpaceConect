/**
 * Municipal Dashboard - JS Functions
 */

let selectedRouterId = null;

document.addEventListener('DOMContentLoaded', function () {
    loadRouters();
});

// =============================================
// DATA LOADING
// =============================================

function loadRouters() {
    $.post(base_url + '/munired/getRouters', function (response) {
        let routers = JSON.parse(response);
        let select = $('#selectRouter');
        select.find('option:not(:first)').remove();

        routers.forEach(function (r) {
            select.append(`<option value="${r.id}">${r.name} (${r.ip})</option>`);
        });

        // Auto-select first router
        if (routers.length > 0) {
            select.val(routers[0].id).trigger('change');
        }
    });
}

$('#selectRouter').on('change', function () {
    selectedRouterId = $(this).val();
    if (selectedRouterId) {
        loadStats();
        loadDepartmentSummary();
        loadAlerts();
        checkRouterStatus();
    }
});

function loadStats() {
    $.post(base_url + '/munidashboard/getStats', { router_id: selectedRouterId }, function (response) {
        let res = JSON.parse(response);
        if (res.status === 'success') {
            let d = res.data;
            $('#statActiveUsers').text(d.active_users);
            $('#statDepartments').text(d.total_departments);
            $('#statPendingSync').text(d.pending_sync);
            $('#statSyncErrors').text(d.sync_errors);
            $('#statBlockedDomains').text(d.blocked_domains);
            $('#statCategories').text(d.total_categories);
        }
    });
}

function loadDepartmentSummary() {
    $.post(base_url + '/munidashboard/getDepartmentSummary', { router_id: selectedRouterId }, function (response) {
        let res = JSON.parse(response);
        let container = $('#departmentCards');
        container.empty();

        if (res.status === 'success' && res.data.length > 0) {
            res.data.forEach(function (dept) {
                let statusClass = dept.status == 1 ? 'border-success' : 'border-danger';
                let qosClass = dept.qos_sync_status === 'synced' ? 'text-success' : (dept.qos_sync_status === 'error' ? 'text-danger' : 'text-warning');
                let qosIcon = dept.qos_sync_status === 'synced' ? 'fa-check-circle' : (dept.qos_sync_status === 'error' ? 'fa-exclamation-circle' : 'fa-clock');

                container.append(`
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card ${statusClass}" style="border-left-width: 4px;">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-building"></i> ${dept.name}
                                </h6>
                                <div class="d-flex justify-content-between">
                                    <small>P: <strong>${dept.priority}</strong></small>
                                    <small>Usuarios: <strong>${dept.active_users}</strong></small>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small>${dept.default_upload}/${dept.default_download}</small>
                                    <small class="${qosClass}"><i class="fas ${qosIcon}"></i> QoS</small>
                                </div>
                                ${dept.error_users > 0 ? `<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${dept.error_users} errores</small>` : ''}
                            </div>
                        </div>
                    </div>
                `);
            });
        } else {
            container.html('<div class="col-12 text-center text-muted"><i class="fas fa-inbox"></i> No hay departamentos configurados.</div>');
        }
    });
}

function loadAlerts() {
    $.post(base_url + '/munidashboard/getAlerts', { limit: 10 }, function (response) {
        let res = JSON.parse(response);
        let list = $('#recentAlerts');
        list.empty();

        if (res.status === 'success' && res.data.length > 0) {
            res.data.forEach(function (alert) {
                let icon = alert.status === 'success' ? 'fa-check text-success' : 'fa-exclamation-triangle text-danger';
                let time = alert.created_at || '';
                list.append(`
                    <li class="list-group-item py-2">
                        <i class="fas ${icon}"></i>
                        <strong>${alert.action}</strong>
                        ${alert.details ? ' - ' + alert.details.substring(0, 80) : ''}
                        <small class="text-muted float-right">${time}</small>
                    </li>
                `);
            });
        } else {
            list.html('<li class="list-group-item text-muted text-center">Sin actividad reciente</li>');
        }
    });
}

function checkRouterStatus() {
    $.post(base_url + '/munidashboard/getRouterStatus', { router_id: selectedRouterId }, function (response) {
        let res = JSON.parse(response);
        let badge = $('#routerStatusBadge');
        let info = $('#routerInfo');

        if (res.connected) {
            badge.removeClass('badge-secondary badge-danger').addClass('badge-success').text('Conectado');
            let d = res.data;
            info.html(`
                <table class="table table-sm mb-0">
                    <tr><td>Version</td><td><strong>${d.version}</strong></td></tr>
                    <tr><td>Board</td><td>${d.board_name}</td></tr>
                    <tr><td>CPU</td><td>${d.cpu_load}%</td></tr>
                    <tr><td>Uptime</td><td>${d.uptime}</td></tr>
                </table>
            `);
        } else {
            badge.removeClass('badge-secondary badge-success').addClass('badge-danger').text('Desconectado');
            info.html('<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + (res.msg || 'No se pudo conectar') + '</p>');
        }
    });
}

// =============================================
// ACTIONS
// =============================================

function quickBlock() {
    let domain = $('#quickBlockDomain').val().trim();
    if (!domain) {
        Swal.fire('Error', 'Ingrese un dominio', 'warning');
        return;
    }
    if (!selectedRouterId) {
        Swal.fire('Error', 'Seleccione un router primero', 'warning');
        return;
    }

    Swal.fire({
        title: 'Bloquear dominio?',
        text: `Se bloqueara ${domain} via DNS en el router.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, bloquear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(base_url + '/munidashboard/quickBlock', {
                domain: domain,
                router_id: selectedRouterId
            }, function (response) {
                let res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire('Bloqueado', res.msg, 'success');
                    $('#quickBlockDomain').val('');
                    loadStats();
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            });
        }
    });
}

function syncAll() {
    if (!selectedRouterId) {
        Swal.fire('Error', 'Seleccione un router primero', 'warning');
        return;
    }

    Swal.fire({
        title: 'Sincronizar todo?',
        text: 'Esto sincronizara colas de usuarios, QoS y filtrado con el router.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, sincronizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Sincronizando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.post(base_url + '/munired/syncAll', { router_id: selectedRouterId }, function (response) {
                let res = JSON.parse(response);
                Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
                loadStats();
                loadDepartmentSummary();
                loadAlerts();
            });
        }
    });
}
