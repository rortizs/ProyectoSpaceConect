/**
 * Municipal Dashboard - JS Functions
 * Dashboard simplificado: empleados + consumo en tiempo real
 */

let selectedRouterId = null;
let routerConnected = false;

document.addEventListener('DOMContentLoaded', function () {
    loadRouters();
});

// =============================================
// HELPERS
// =============================================

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const idx = Math.min(i, units.length - 1);
    return (bytes / Math.pow(1024, idx)).toFixed(2) + ' ' + units[idx];
}

function parseMikroTikRate(rateStr) {
    if (!rateStr) return 0;
    rateStr = rateStr.toString().trim().toUpperCase();
    let multiplier = 1;
    if (rateStr.endsWith('K')) { multiplier = 1024; rateStr = rateStr.slice(0, -1); }
    else if (rateStr.endsWith('M')) { multiplier = 1024 * 1024; rateStr = rateStr.slice(0, -1); }
    else if (rateStr.endsWith('G')) { multiplier = 1024 * 1024 * 1024; rateStr = rateStr.slice(0, -1); }
    return parseFloat(rateStr) * multiplier || 0;
}

function escapeHtml(str) {
    if (!str) return '';
    let div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

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

        if (routers.length > 0) {
            select.val(routers[0].id).trigger('change');
        }
    });
}

$('#selectRouter').on('change', function () {
    selectedRouterId = $(this).val();
    if (selectedRouterId) {
        loadBandwidthStats();
        loadAlerts();
        checkRouterStatus();
    }
});

function loadBandwidthStats() {
    $.post(base_url + '/munidashboard/getBandwidthStats', { router_id: selectedRouterId }, function (response) {
        let res = JSON.parse(response);

        if (res.status === 'success') {
            routerConnected = true;
            $('#routerOfflineAlert').hide();

            // Update stat cards
            $('#statActiveUsers').text(res.data.active_count);
            $('#statDisabledUsers').text(res.data.disabled_count);
            $('#statTotalConsumption').text(formatBytes(res.data.total_download));
            $('#bandwidthTimestamp').text('Actualizado: ' + new Date().toLocaleTimeString());

            // Populate top consumers table
            renderTopConsumers(res.data.queues);
        } else {
            // Router offline — fallback to DB stats
            routerConnected = false;
            $('#routerOfflineAlert').show();
            $('#statTotalConsumption').text('--');

            $.post(base_url + '/munidashboard/getStats', { router_id: selectedRouterId }, function (r2) {
                let r2d = JSON.parse(r2);
                if (r2d.status === 'success') {
                    $('#statActiveUsers').text(r2d.data.active_users);
                    $('#statDisabledUsers').text(r2d.data.disabled_users || 0);
                }
            });

            $('#topConsumersBody').html(
                '<tr><td colspan="7" class="text-center text-muted">' +
                '<i class="fas fa-exclamation-triangle text-warning"></i> ' +
                'Router desconectado — datos de consumo no disponibles</td></tr>'
            );
        }
    }).fail(function () {
        routerConnected = false;
        $('#routerOfflineAlert').show();
        $('#topConsumersBody').html(
            '<tr><td colspan="7" class="text-center text-danger">Error al conectar con el servidor</td></tr>'
        );
    });
}

function renderTopConsumers(queues) {
    let tbody = $('#topConsumersBody');
    tbody.empty();

    if (!queues || queues.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted">No hay queues activas</td></tr>');
        return;
    }

    let top = queues.slice(0, 10);
    top.forEach(function (q, idx) {
        let statusBadge = q.disabled
            ? '<span class="badge badge-secondary">Inactivo</span>'
            : '<span class="badge badge-success">Activo</span>';

        tbody.append(`
            <tr>
                <td>${idx + 1}</td>
                <td><strong>${escapeHtml(q.name)}</strong></td>
                <td><code>${q.ip}</code></td>
                <td>${formatBytes(q.download_bytes)}</td>
                <td>${formatBytes(q.upload_bytes)}</td>
                <td><small>${q.max_limit}</small></td>
                <td>${statusBadge}</td>
            </tr>
        `);
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
                        <strong>${escapeHtml(alert.action)}</strong>
                        ${alert.details ? ' - ' + escapeHtml(alert.details.substring(0, 80)) : ''}
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

        if (res.connected) {
            badge.removeClass('badge-secondary badge-danger').addClass('badge-success').text('Conectado');

            let d = res.data;
            let cpuLoad = d.cpu_load || 0;
            let cpuColor = cpuLoad > 80 ? '#dc3545' : (cpuLoad > 50 ? '#ffc107' : '#28a745');

            $('#routerStatusCard').css('border-left-color', cpuColor);
            $('#routerStatusIcon').css('background', cpuColor);
            $('#statRouterStatus').html(cpuLoad + '% CPU');
        } else {
            badge.removeClass('badge-secondary badge-success').addClass('badge-danger').text('Desconectado');
            $('#routerStatusCard').css('border-left-color', '#dc3545');
            $('#routerStatusIcon').css('background', '#dc3545');
            $('#statRouterStatus').text('Offline');
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
        text: 'Esto sincronizara colas de usuarios y filtrado con el router.',
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
                loadBandwidthStats();
                loadAlerts();
            });
        }
    });
}
