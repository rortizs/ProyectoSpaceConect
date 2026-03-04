/**
 * Municipal Network Administration - JS Functions
 * Handles: Departments, Users, Bandwidth, Filtering, Config
 */

let currentSection = document.querySelector('main')?.dataset?.section || '';
let tableDepartments, tableUsers;

document.addEventListener('DOMContentLoaded', function () {
    // Detect current section from page_section data or URL
    let path = window.location.pathname;
    if (path.includes('/departments')) currentSection = 'departments';
    else if (path.includes('/users')) currentSection = 'users';
    else if (path.includes('/bandwidth')) currentSection = 'bandwidth';
    else if (path.includes('/filtering')) currentSection = 'filtering';
    else if (path.includes('/config')) currentSection = 'config';

    loadRouterSelects();

    switch (currentSection) {
        case 'departments':
            loadDepartments();
            break;
        case 'users':
            loadDepartmentSelects();
            loadUsers();
            break;
        case 'bandwidth':
            loadBandwidthTable();
            break;
        case 'filtering':
            loadDepartmentSelects();
            loadCategories();
            break;
        case 'config':
            loadConfigRouters();
            break;
    }
});

// =============================================
// SHARED: Router & Department selects
// =============================================

function loadRouterSelects() {
    $.post(base_url + '/munired/getRouters', function (response) {
        let routers = JSON.parse(response);
        let selects = $('select[id*="Router"], select[id*="router"], #deptRouter, #bwRouter, #filteringRouter, #configRouter, #filterRouter');
        selects.each(function () {
            let $sel = $(this);
            $sel.find('option:not(:first)').remove();
            routers.forEach(function (r) {
                $sel.append(`<option value="${r.id}">${r.name} (${r.ip})</option>`);
            });
        });
    });
}

function loadDepartmentSelects() {
    $.post(base_url + '/munired/getDepartments', function (response) {
        let depts = JSON.parse(response);
        let selects = $('#filterDepartment, #userDepartment, #filteringDept');
        selects.each(function () {
            let $sel = $(this);
            $sel.find('option:not(:first)').remove();
            depts.forEach(function (d) {
                $sel.append(`<option value="${d.encrypt_id}">${d.name}</option>`);
            });
        });
    });
}

// =============================================
// DEPARTMENTS
// =============================================

function loadDepartments() {
    let routerId = $('#filterRouter').val() || '';
    $.post(base_url + '/munired/getDepartments', { router_id: routerId }, function (response) {
        let data = JSON.parse(response);
        let tbody = $('#tableDepartments tbody');
        tbody.empty();

        data.forEach(function (d) {
            tbody.append(`
                <tr>
                    <td><strong>${d.name}</strong><br><small class="text-muted">${d.description || ''}</small></td>
                    <td>${d.router_name || 'N/A'}</td>
                    <td><code>${d.ip_range}</code></td>
                    <td class="text-center"><span class="badge badge-info">${d.priority}</span></td>
                    <td>${d.default_upload}/${d.default_download}</td>
                    <td>${d.qos_max_limit || '<span class="text-muted">N/A</span>'}</td>
                    <td class="text-center">${d.user_count || 0} / ${d.total_users || 0}</td>
                    <td class="text-center">${d.qos_label}</td>
                    <td class="text-center">${d.status_label}</td>
                    <td class="text-center">${d.options}</td>
                </tr>
            `);
        });

        if ($.fn.DataTable.isDataTable('#tableDepartments')) {
            $('#tableDepartments').DataTable().destroy();
        }
        $('#tableDepartments').DataTable({
            language: { url: base_url + '/Assets/js/plugins/datatables/Spanish.json' },
            order: [[3, 'asc']],
            pageLength: 25
        });
    });
}

$('#filterRouter').on('change', function () { loadDepartments(); });

function openDepartmentModal(encId) {
    $('#formDepartment')[0].reset();
    $('#deptId').val('');

    if (encId) {
        $('#modalDepartmentTitle').text('Editar Departamento');
        $.post(base_url + '/munired/getDepartment/' + encId, function (response) {
            let res = JSON.parse(response);
            if (res.status === 'success') {
                let d = res.data;
                $('#deptId').val(d.encrypt_id);
                $('#deptName').val(d.name);
                $('#deptRouter').val(d.router_id);
                $('#deptIpRange').val(d.ip_range);
                $('#deptPriority').val(d.priority);
                $('#deptDescription').val(d.description);
                $('#deptUpload').val(d.default_upload);
                $('#deptDownload').val(d.default_download);
                $('#deptQosMaxLimit').val(d.qos_max_limit);
                $('input[name="burst_upload"]').val(d.burst_upload);
                $('input[name="burst_download"]').val(d.burst_download);
                $('input[name="burst_threshold_up"]').val(d.burst_threshold_up);
                $('input[name="burst_time"]').val(d.burst_time);
            }
        });
    } else {
        $('#modalDepartmentTitle').text('Nuevo Departamento');
    }

    $('#modalDepartment').modal('show');
}

function editDepartment(encId) { openDepartmentModal(encId); }

$('#formDepartment').on('submit', function (e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.post(base_url + '/munired/saveDepartment', formData, function (response) {
        let res = JSON.parse(response);
        if (res.status === 'success') {
            Swal.fire('Exito', res.msg, 'success');
            $('#modalDepartment').modal('hide');
            loadDepartments();
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    });
});

function deleteDepartment(encId) {
    Swal.fire({
        title: 'Eliminar departamento?',
        text: 'Esta accion no se puede deshacer. Se eliminara el departamento si no tiene usuarios.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(base_url + '/munired/deleteDepartment', { id: encId }, function (response) {
                let res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire('Eliminado', res.msg, 'success');
                    loadDepartments();
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            });
        }
    });
}

// =============================================
// USERS
// =============================================

function loadUsers() {
    let filters = {
        department_id: $('#filterDepartment').val() || '',
        status: $('#filterStatus').val() || '',
        search: $('#filterSearch').val() || ''
    };

    $.post(base_url + '/munired/getUsers', filters, function (response) {
        let data = JSON.parse(response);
        let tbody = $('#tableUsers tbody');
        tbody.empty();

        data.forEach(function (u) {
            tbody.append(`
                <tr>
                    <td><strong>${u.name}</strong></td>
                    <td>${u.department_name}</td>
                    <td><code>${u.ip_address}</code></td>
                    <td><small>${u.mac_address || '-'}</small></td>
                    <td>${u.effective_upload}</td>
                    <td>${u.effective_download}</td>
                    <td class="text-center">${u.sync_label}</td>
                    <td class="text-center">${u.status_label}</td>
                    <td class="text-center">${u.options}</td>
                </tr>
            `);
        });

        if ($.fn.DataTable.isDataTable('#tableUsers')) {
            $('#tableUsers').DataTable().destroy();
        }
        $('#tableUsers').DataTable({
            language: { url: base_url + '/Assets/js/plugins/datatables/Spanish.json' },
            order: [[1, 'asc'], [0, 'asc']],
            pageLength: 25
        });
    });
}

$('#filterDepartment, #filterStatus').on('change', function () { loadUsers(); });
$('#filterSearch').on('keyup', debounce(function () { loadUsers(); }, 500));

function openUserModal(encId) {
    $('#formUser')[0].reset();
    $('#userId').val('');

    if (encId) {
        $('#modalUserTitle').text('Editar Usuario');
        $.post(base_url + '/munired/getUser/' + encId, function (response) {
            let res = JSON.parse(response);
            if (res.status === 'success') {
                let u = res.data;
                $('#userId').val(u.encrypt_id);
                $('#userName').val(u.name);
                $('#userDepartment').val(u.department_id);
                $('#userIp').val(u.ip_address);
                $('#userMac').val(u.mac_address);
                $('#userUpload').val(u.custom_upload);
                $('#userDownload').val(u.custom_download);
            }
        });
    } else {
        $('#modalUserTitle').text('Nuevo Usuario');
    }

    $('#modalUser').modal('show');
}

function editUser(encId) { openUserModal(encId); }

$('#formUser').on('submit', function (e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.post(base_url + '/munired/saveUser', formData, function (response) {
        let res = JSON.parse(response);
        if (res.status === 'success') {
            let msg = res.msg;
            if (res.sync && res.sync !== 'synced') {
                msg += '\n(Sync: ' + res.sync + ')';
            }
            Swal.fire('Exito', msg, 'success');
            $('#modalUser').modal('hide');
            loadUsers();
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    });
});

function deleteUser(encId) {
    Swal.fire({
        title: 'Eliminar usuario?',
        text: 'Se eliminara el usuario y su cola del router.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(base_url + '/munired/deleteUser', { id: encId }, function (response) {
                let res = JSON.parse(response);
                Swal.fire(res.status === 'success' ? 'Eliminado' : 'Error', res.msg, res.status === 'success' ? 'success' : 'error');
                loadUsers();
            });
        }
    });
}

function toggleUser(encId, status) {
    let action = status === 1 ? 'habilitar' : 'deshabilitar';

    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} usuario?`,
        text: status === 0 ? 'El ancho de banda se reducira a 1k/1k' : 'Se restaurara el ancho de banda normal',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, ' + action,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(base_url + '/munired/toggleUser', { id: encId, status: status }, function (response) {
                let res = JSON.parse(response);
                Swal.fire(res.status === 'success' ? 'Listo' : 'Error', res.msg, res.status === 'success' ? 'success' : 'error');
                loadUsers();
            });
        }
    });
}

function showAvailableIPs() {
    let deptId = $('#userDepartment').val();
    if (!deptId) {
        Swal.fire('Atencion', 'Seleccione un departamento primero.', 'warning');
        return;
    }

    $.post(base_url + '/munired/getAvailableIPs', { dept_id: deptId }, function (response) {
        let res = JSON.parse(response);
        if (res.status === 'success') {
            let d = res.data;
            $('#availableIPsInfo').html(
                `<strong>Total:</strong> ${d.total} | <strong>Usadas:</strong> ${d.used} | <strong>Disponibles:</strong> ${d.available.length}`
            );
            let html = '';
            d.available.forEach(function (ip) {
                html += `<a href="javascript:;" class="btn btn-outline-primary btn-sm m-1" onclick="selectIP('${ip}')">${ip}</a>`;
            });
            $('#availableIPsList').html(html || '<p class="text-muted">No hay IPs disponibles</p>');
            $('#modalAvailableIPs').modal('show');
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    });
}

function selectIP(ip) {
    $('#userIp').val(ip);
    $('#modalAvailableIPs').modal('hide');
}

// =============================================
// BANDWIDTH
// =============================================

function loadBandwidthTable() {
    let routerId = $('#bwRouter').val() || '';

    $.post(base_url + '/munired/getDepartments', { router_id: routerId }, function (response) {
        let data = JSON.parse(response);
        let tbody = $('#tableBandwidth tbody');
        tbody.empty();

        data.forEach(function (d) {
            let syncBtn = `<button class="btn btn-outline-warning btn-sm" onclick="syncDeptQueues('${d.encrypt_id}')"><i class="fas fa-sync-alt"></i></button>`;
            let viewBtn = `<button class="btn btn-outline-info btn-sm" onclick="viewDeptUsers(${d.id}, '${d.name}')"><i class="fas fa-users"></i></button>`;

            tbody.append(`
                <tr>
                    <td><strong>${d.name}</strong></td>
                    <td class="text-center"><span class="badge badge-info">${d.priority}</span></td>
                    <td>${d.qos_max_limit || '<span class="text-muted">Sin configurar</span>'}</td>
                    <td>${d.default_upload} / ${d.default_download}</td>
                    <td class="text-center">${d.user_count || 0}</td>
                    <td class="text-center">${d.qos_label || '<span class="badge badge-secondary">N/A</span>'}</td>
                    <td class="text-center">${syncBtn} ${viewBtn}</td>
                </tr>
            `);
        });
    });
}

$('#bwRouter').on('change', function () { loadBandwidthTable(); });

function syncDeptQueues(encDeptId) {
    Swal.fire({ title: 'Sincronizando colas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncDepartmentQueues', { dept_id: encDeptId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
        loadBandwidthTable();
    });
}

function syncQoS() {
    let routerId = $('#bwRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando QoS...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncQoS', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
        loadBandwidthTable();
    });
}

function syncAllQueues() {
    let routerId = $('#bwRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando todas las colas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncAll', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
        loadBandwidthTable();
    });
}

function viewDeptUsers(deptId, deptName) {
    $('#selectedDeptName').text(deptName);
    $('#userBandwidthSection').show();

    $.post(base_url + '/munired/getUsers', { department_id: deptId, status: '1' }, function (response) {
        let data = JSON.parse(response);
        let tbody = $('#tableUserBandwidth tbody');
        tbody.empty();

        data.forEach(function (u) {
            tbody.append(`
                <tr>
                    <td>${u.name}</td>
                    <td><code>${u.ip_address}</code></td>
                    <td>${u.effective_upload}</td>
                    <td>${u.effective_download}</td>
                    <td><small>${u.queue_name || 'N/A'}</small></td>
                    <td class="text-center">${u.sync_label}</td>
                    <td class="text-center">${u.options}</td>
                </tr>
            `);
        });
    });
}

// =============================================
// FILTERING
// =============================================

let selectedCategories = [];

function loadCategories() {
    $.post(base_url + '/munired/getCategories', function (response) {
        let data = JSON.parse(response);
        window._allCategories = data;
    });
}

$('#filteringDept').on('change', function () {
    let deptId = $(this).val();
    if (!deptId) return;

    // Load current policies for this dept
    $.post(base_url + '/munired/getFilterPolicies', { dept_id: deptId }, function (response) {
        let policies = JSON.parse(response);
        selectedCategories = policies.map(p => parseInt(p.category_id));
        renderCategoryList();
    });

    // Load whitelist
    loadWhitelist(deptId);
});

function renderCategoryList() {
    let container = $('#categoryList');
    let cats = window._allCategories || [];

    if (cats.length === 0) {
        container.html('<p class="text-muted">No hay categorias disponibles.</p>');
        return;
    }

    let html = '<div class="row">';
    cats.forEach(function (cat) {
        let checked = selectedCategories.includes(parseInt(cat.id)) ? 'checked' : '';
        let colorClass = cat.color || 'secondary';

        html += `
            <div class="col-md-6 mb-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input category-check" id="cat_${cat.id}" value="${cat.id}" ${checked}>
                    <label class="custom-control-label" for="cat_${cat.id}">
                        <i class="${cat.icon || 'fas fa-tag'}" style="color: ${colorClass}"></i>
                        ${cat.name}
                    </label>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.html(html);
}

function saveFilterPolicy() {
    let deptId = $('#filteringDept').val();
    if (!deptId) { Swal.fire('Error', 'Seleccione un departamento.', 'warning'); return; }

    let categoryIds = [];
    $('.category-check:checked').each(function () {
        categoryIds.push($(this).val());
    });

    $.post(base_url + '/munired/saveFilterPolicy', {
        dept_id: deptId,
        'category_ids[]': categoryIds,
        action: 'block'
    }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Guardado' : 'Error', res.msg, res.status === 'success' ? 'success' : 'error');
    });
}

function loadWhitelist(deptId) {
    $.post(base_url + '/munired/getWhitelist', { dept_id: deptId || '' }, function (response) {
        let data = JSON.parse(response);
        let tbody = $('#tableWhitelist tbody');
        tbody.empty();

        data.forEach(function (w) {
            let type = w.department_id ? 'Departamento' : '<span class="badge badge-dark">Global</span>';
            let removeBtn = `<button class="btn btn-outline-danger btn-sm" onclick="removeWhitelist('${w.id}')"><i class="fas fa-times"></i></button>`;

            tbody.append(`
                <tr>
                    <td>${w.domain}</td>
                    <td>${type}</td>
                    <td><small>${w.added_by_name || 'Sistema'}</small></td>
                    <td class="text-center">${removeBtn}</td>
                </tr>
            `);
        });
    });
}

function addWhitelist() {
    let deptId = $('#filteringDept').val();
    let domain = $('#whitelistDomain').val().trim();

    if (!domain) { Swal.fire('Error', 'Ingrese un dominio.', 'warning'); return; }

    $.post(base_url + '/munired/addWhitelistDomain', {
        dept_id: deptId || '',
        domain: domain
    }, function (response) {
        let res = JSON.parse(response);
        if (res.status === 'success') {
            Swal.fire('Agregado', res.msg, 'success');
            $('#whitelistDomain').val('');
            loadWhitelist(deptId);
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    });
}

function removeWhitelist(id) {
    $.post(base_url + '/munired/removeWhitelistDomain', { id: id }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Eliminado' : 'Error', res.msg, res.status === 'success' ? 'success' : 'error');
        loadWhitelist($('#filteringDept').val());
    });
}

function syncFilteringRules() {
    let routerId = $('#filteringRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando filtrado...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncFiltering', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
    });
}

// =============================================
// CONFIG
// =============================================

function loadConfigRouters() {
    $.post(base_url + '/munired/getRouters', function (response) {
        let routers = JSON.parse(response);
        let tbody = $('#tableRouters tbody');
        tbody.empty();

        routers.forEach(function (r) {
            let statusBadge = r.status === 'connected'
                ? '<span class="badge badge-success">Conectado</span>'
                : '<span class="badge badge-secondary">' + (r.status || 'N/A') + '</span>';

            let testBtn = `<button class="btn btn-outline-info btn-sm" onclick="testRouterConnection(${r.id})"><i class="fas fa-plug"></i></button>`;

            tbody.append(`
                <tr>
                    <td>${r.name}</td>
                    <td><code>${r.ip}</code></td>
                    <td>${r.port}</td>
                    <td>${r.ip_range || 'N/A'}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${testBtn}</td>
                </tr>
            `);
        });
    });
}

function testRouterConnection(routerId) {
    Swal.fire({ title: 'Probando conexion...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munidashboard/getRouterStatus', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        if (res.connected) {
            let d = res.data;
            Swal.fire('Conectado', `Version: ${d.version}\nBoard: ${d.board_name}\nCPU: ${d.cpu_load}%\nUptime: ${d.uptime}`, 'success');
        } else {
            Swal.fire('Error', res.msg || 'No se pudo conectar', 'error');
        }
    });
}

function syncAllFromConfig() {
    let routerId = $('#configRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando todo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncAll', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
    });
}

function syncQoSFromConfig() {
    let routerId = $('#configRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando QoS...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncQoS', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
    });
}

function syncFilteringFromConfig() {
    let routerId = $('#configRouter').val();
    if (!routerId) { Swal.fire('Error', 'Seleccione un router.', 'warning'); return; }

    Swal.fire({ title: 'Sincronizando filtrado...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.post(base_url + '/munired/syncFiltering', { router_id: routerId }, function (response) {
        let res = JSON.parse(response);
        Swal.fire(res.status === 'success' ? 'Completado' : 'Atencion', res.msg, res.status === 'success' ? 'success' : 'warning');
    });
}

// =============================================
// UTILS
// =============================================

function debounce(func, wait) {
    let timeout;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}
