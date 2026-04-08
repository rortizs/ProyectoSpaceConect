/**
 * Municipal Dashboard - Control Room Interface
 * Redesigned with actionable insights and visual urgency hierarchy
 */

(function () {
    'use strict';

    let selectedRouterId = null;
    let routerConnected = false;
    
    document.addEventListener('DOMContentLoaded', function () {
        loadRouters();
    });
    
    // =============================================
    // HELPERS - Formatters & Calculators
    // =============================================
    
    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        const idx = Math.min(i, units.length - 1);
        return (bytes / Math.pow(1024, idx)).toFixed(2) + ' ' + units[idx];
    }
    
    /**
     * Parse MikroTik rate string (e.g., "10M", "512K") to bytes
     */
    function parseMikroTikRate(rateStr) {
        if (!rateStr) return 0;
        rateStr = rateStr.toString().trim().toUpperCase();
        let multiplier = 1;
        if (rateStr.endsWith('K')) { multiplier = 1024; rateStr = rateStr.slice(0, -1); }
        else if (rateStr.endsWith('M')) { multiplier = 1024 * 1024; rateStr = rateStr.slice(0, -1); }
        else if (rateStr.endsWith('G')) { multiplier = 1024 * 1024 * 1024; rateStr = rateStr.slice(0, -1); }
        return parseFloat(rateStr) * multiplier || 0;
    }
    
    /**
     * Format MikroTik limit string for human readability
     * "10000000/40000000" -> "10M / 40M"
     */
    function formatLimit(limitStr) {
        if (!limitStr) return '--';
        const parts = limitStr.split('/');
        if (parts.length !== 2) return limitStr;
        
        const upload = parseMikroTikRate(parts[0]);
        const download = parseMikroTikRate(parts[1]);
        
        return `${formatRate(upload)} / ${formatRate(download)}`;
    }
    
    function formatRate(bytes) {
        if (bytes === 0) return '0';
        const units = ['bps', 'Kbps', 'Mbps', 'Gbps'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        const idx = Math.min(i, units.length - 1);
        const value = (bytes / Math.pow(1024, idx)).toFixed(idx > 0 ? 1 : 0);
        return `${value}${units[idx].replace('bps', '')}`;
    }
    
    /**
     * Calculate usage percentage vs limit
     */
    function calculateUsagePercent(currentBytes, limitStr) {
        if (!limitStr) return 0;
        const parts = limitStr.split('/');
        if (parts.length !== 2) return 0;
        
        const limitBytes = parseMikroTikRate(parts[1]); // Download limit
        if (limitBytes === 0) return 0;
        
        return Math.min(Math.round((currentBytes / limitBytes) * 100), 100);
    }
    
    /**
     * Determine risk level based on usage percentage
     */
    function getRiskLevel(percent) {
        if (percent >= 90) return 'critical';
        if (percent >= 70) return 'warning';
        return 'ok';
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
    
                // Calculate risk stats
                let riskCount = 0;
                let criticalCount = 0;
                res.data.queues.forEach(q => {
                    const percent = calculateUsagePercent(q.download_bytes, q.max_limit);
                    const risk = getRiskLevel(percent);
                    if (risk === 'warning' || risk === 'critical') riskCount++;
                    if (risk === 'critical') criticalCount++;
                });
    
                // Update stat cards
                $('#statActiveUsers').text(res.data.active_count);
                $('#statRiskUsers').text(riskCount);
                $('#statTotalConsumption').text(formatBytes(res.data.total_download));
                $('#bandwidthTimestamp').text('Actualizado: ' + new Date().toLocaleTimeString());
    
                // Update risk card styling
                const riskCard = $('#statRiskCard');
                riskCard.removeClass('muni-stat-card--ok muni-stat-card--warning muni-stat-card--critical');
                if (criticalCount > 0) {
                    riskCard.addClass('muni-stat-card--critical');
                    $('#statRiskMeta').html('<i class="fas fa-exclamation-circle"></i> <span>Requiere atención inmediata</span>');
                } else if (riskCount > 0) {
                    riskCard.addClass('muni-stat-card--warning');
                    $('#statRiskMeta').html('<i class="fas fa-exclamation-triangle"></i> <span>&gt; 70% del límite</span>');
                } else {
                    riskCard.addClass('muni-stat-card--ok');
                    $('#statRiskMeta').html('<i class="fas fa-check-circle"></i> <span>Todos dentro del rango normal</span>');
                }
    
                // Populate table with new design
                renderEmployeeTable(res.data.queues);
            } else {
                // Router offline — fallback to DB stats
                routerConnected = false;
                $('#routerOfflineAlert').show();
                $('#statTotalConsumption').text('--');
    
                $.post(base_url + '/munidashboard/getStats', { router_id: selectedRouterId }, function (r2) {
                    let r2d = JSON.parse(r2);
                    if (r2d.status === 'success') {
                        $('#statActiveUsers').text(r2d.data.active_users);
                        $('#statRiskUsers').text('--');
                    }
                });
    
                $('#topConsumersBody').html(
                    '<tr><td colspan="7" class="text-center text-muted" style="padding: 48px;">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b; opacity: 0.5;"></i>' +
                    '<div style="margin-top: 16px; color: #718096;">Router desconectado — datos de consumo no disponibles</div>' +
                    '</td></tr>'
                );
            }
        }).fail(function () {
            routerConnected = false;
            $('#routerOfflineAlert').show();
            $('#topConsumersBody').html(
                '<tr><td colspan="7" class="text-center" style="padding: 48px;">' +
                '<i class="fas fa-times-circle" style="font-size: 2rem; color: #ef4444; opacity: 0.5;"></i>' +
                '<div style="margin-top: 16px; color: #ef4444;">Error al conectar con el servidor</div>' +
                '</td></tr>'
            );
        });
    }
    
    /**
     * Render employee table with gauges and risk indicators
     */
    function renderEmployeeTable(queues) {
        let tbody = $('#topConsumersBody');
        tbody.empty();
    
        if (!queues || queues.length === 0) {
            tbody.html(
                '<tr><td colspan="7" class="muni-empty-state">' +
                '<div class="muni-empty-state__icon"><i class="fas fa-inbox"></i></div>' +
                '<div class="muni-empty-state__text">No hay queues activas en este router</div>' +
                '</td></tr>'
            );
            return;
        }
    
        // Sort by usage percentage (critical first)
        let enriched = queues.map(q => {
            const percent = calculateUsagePercent(q.download_bytes, q.max_limit);
            return { ...q, usagePercent: percent, riskLevel: getRiskLevel(percent) };
        });
        
        enriched.sort((a, b) => {
            // Critical first, then warning, then by percentage
            const riskOrder = { critical: 0, warning: 1, ok: 2 };
            if (riskOrder[a.riskLevel] !== riskOrder[b.riskLevel]) {
                return riskOrder[a.riskLevel] - riskOrder[b.riskLevel];
            }
            return b.usagePercent - a.usagePercent;
        });
    
        enriched.forEach(function (q, idx) {
            const row = `
                <tr>
                    <td class="muni-table__rank">${idx + 1}</td>
                    <td class="muni-table__employee">
                        <div class="muni-table__employee-name">${escapeHtml(q.name)}</div>
                        ${q.department ? `<div class="muni-table__employee-dept">${escapeHtml(q.department)}</div>` : ''}
                    </td>
                    <td><span class="muni-table__ip">${q.ip}</span></td>
                    <td>${renderGauge(q)}</td>
                    <td>
                        <div style="font-family: var(--font-mono); font-size: 0.875rem; color: var(--ink);">
                            <strong>${formatBytes(q.download_bytes)}</strong> / ${formatBytes(q.upload_bytes)}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--ink-muted); margin-top: 2px;">
                            Límite: ${formatLimit(q.max_limit)}
                        </div>
                    </td>
                    <td>${renderRiskBadge(q)}</td>
                    <td>${renderActions(q)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    /**
     * Render gauge component (signature element)
     */
    function renderGauge(queue) {
        const percent = queue.usagePercent;
        const risk = queue.riskLevel;
        
        // SVG circle parameters
        const radius = 20;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (percent / 100) * circumference;
        
        return `
            <div class="muni-gauge">
                <div class="muni-gauge__visual">
                    <svg class="muni-gauge__circle" width="56" height="56" viewBox="0 0 56 56">
                        <circle class="muni-gauge__bg" cx="28" cy="28" r="${radius}"></circle>
                        <circle class="muni-gauge__fill muni-gauge__fill--${risk}" 
                                cx="28" cy="28" r="${radius}"
                                stroke-dasharray="${circumference}"
                                stroke-dashoffset="${offset}">
                        </circle>
                    </svg>
                    <div class="muni-gauge__label">${percent}%</div>
                </div>
                <div class="muni-gauge__info">
                    <div class="muni-gauge__current">${formatBytes(queue.download_bytes)}</div>
                    <div class="muni-gauge__limit">de ${formatLimit(queue.max_limit).split(' / ')[1]}</div>
                </div>
            </div>
        `;
    }
    
    /**
     * Render risk badge
     */
    function renderRiskBadge(queue) {
        if (queue.disabled) {
            return `
                <div class="muni-risk-badge muni-risk-badge--disabled">
                    <span class="muni-risk-badge__icon"></span>
                    Inactivo
                </div>
            `;
        }
        
        const risk = queue.riskLevel;
        const labels = {
            ok: 'Normal',
            warning: 'Alerta',
            critical: 'Crítico'
        };
        
        return `
            <div class="muni-risk-badge muni-risk-badge--${risk}">
                <span class="muni-risk-badge__icon"></span>
                ${labels[risk]}
            </div>
        `;
    }
    
    /**
     * Render action buttons
     */
    function renderActions(queue) {
        return `
            <div class="muni-actions">
                <button class="muni-action-btn muni-action-btn--primary" 
                        onclick="viewDetails('${escapeHtml(queue.ip)}')">
                    <i class="fas fa-chart-line"></i> Ver
                </button>
                ${queue.riskLevel === 'critical' ? `
                    <button class="muni-action-btn muni-action-btn--danger" 
                            onclick="handleCriticalUser('${escapeHtml(queue.ip)}', '${escapeHtml(queue.name)}')">
                        <i class="fas fa-exclamation-triangle"></i> Acción
                    </button>
                ` : ''}
            </div>
        `;
    }
    
    function loadAlerts() {
        $.post(base_url + '/munidashboard/getAlerts', { limit: 8 }, function (response) {
            let res = JSON.parse(response);
            let container = $('#recentAlertsContainer');
            container.empty();
    
            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(function (alert) {
                    // Determine alert severity
                    let severity = 'ok';
                    if (alert.action.includes('block') || alert.action.includes('exceed')) severity = 'critical';
                    else if (alert.action.includes('warning') || alert.action.includes('sync')) severity = 'warning';
                    
                    const icons = {
                        ok: 'fa-check-circle',
                        warning: 'fa-exclamation-triangle',
                        critical: 'fa-exclamation-circle'
                    };
                    
                    container.append(`
                        <div class="muni-alert-item">
                            <div class="muni-alert-item__icon muni-alert-item__icon--${severity}">
                                <i class="fas ${icons[severity]}"></i>
                            </div>
                            <div class="muni-alert-item__content">
                                <div class="muni-alert-item__title">${escapeHtml(alert.action)}</div>
                                ${alert.details ? `<div class="muni-alert-item__detail">${escapeHtml(alert.details.substring(0, 100))}</div>` : ''}
                                <div class="muni-alert-item__time">${alert.created_at || ''}</div>
                            </div>
                        </div>
                    `);
                });
            } else {
                container.html(`
                    <div class="muni-empty-state">
                        <div class="muni-empty-state__icon"><i class="fas fa-bell-slash"></i></div>
                        <div class="muni-empty-state__text">Sin actividad reciente</div>
                    </div>
                `);
            }
        });
    }
    
    function checkRouterStatus() {
        $.post(base_url + '/munidashboard/getRouterStatus', { router_id: selectedRouterId }, function (response) {
            let res = JSON.parse(response);
            let badge = $('#routerStatusBadge');
            let card = $('#routerStatusCard');
    
            if (res.connected) {
                badge.removeClass('badge-secondary badge-danger').addClass('badge-success').text('Conectado');
    
                let d = res.data;
                let cpuLoad = d.cpu_load || 0;
                
                $('#statRouterStatus').text(cpuLoad + '%');
                $('#statRouterMeta').text('Uso de CPU');
                
                // Update card color based on CPU load
                card.removeClass('muni-stat-card--ok muni-stat-card--warning muni-stat-card--critical');
                if (cpuLoad > 80) {
                    card.addClass('muni-stat-card--critical');
                } else if (cpuLoad > 50) {
                    card.addClass('muni-stat-card--warning');
                } else {
                    card.addClass('muni-stat-card--ok');
                }
            } else {
                badge.removeClass('badge-secondary badge-success').addClass('badge-danger').text('Desconectado');
                card.removeClass('muni-stat-card--ok muni-stat-card--warning').addClass('muni-stat-card--critical');
                $('#statRouterStatus').text('Offline');
                $('#statRouterMeta').text('Sin conexión');
            }
        });
    }
    
    // =============================================
    // ACTIONS
    // =============================================
    
    window.viewDetails = function(ip) {
        Swal.fire({
            title: 'Detalles de ' + ip,
            text: 'Funcionalidad en desarrollo - se mostrará histórico de consumo y estadísticas detalladas.',
            icon: 'info'
        });
    };
    
    window.handleCriticalUser = function(ip, name) {
        Swal.fire({
            title: '⚠️ Usuario en estado crítico',
            html: `
                <p><strong>${name}</strong> (${ip}) ha excedido el 90% de su límite de ancho de banda.</p>
                <p>¿Qué acción desea tomar?</p>
            `,
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-chart-line"></i> Ampliar límite',
            denyButtonText: '<i class="fas fa-ban"></i> Bloquear usuario',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Funcionalidad en desarrollo', 'Se abrirá diálogo para ampliar límite de ancho de banda.', 'info');
            } else if (result.isDenied) {
                Swal.fire('Funcionalidad en desarrollo', 'Se bloqueará el usuario temporalmente.', 'info');
            }
        });
    };
    
    window.quickBlock = function() {
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
            title: '¿Bloquear dominio?',
            text: `Se bloqueará ${domain} vía DNS en el router.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, bloquear',
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
                        loadAlerts(); // Refresh alerts
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                });
            }
        });
    };
    
    window.syncAll = function() {
        if (!selectedRouterId) {
            Swal.fire('Error', 'Seleccione un router primero', 'warning');
            return;
        }
    
        Swal.fire({
            title: '¿Sincronizar todo?',
            text: 'Esto sincronizará colas de usuarios y filtrado con el router.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ 
                    title: 'Sincronizando...', 
                    allowOutsideClick: false, 
                    didOpen: () => Swal.showLoading() 
                });
    
                $.post(base_url + '/munired/syncAll', { router_id: selectedRouterId }, function (response) {
                    let res = JSON.parse(response);
                    Swal.fire(
                        res.status === 'success' ? 'Completado' : 'Atención', 
                        res.msg, 
                        res.status === 'success' ? 'success' : 'warning'
                    );
                    loadBandwidthStats();
                    loadAlerts();
                });
            }
        });
    };

})();
