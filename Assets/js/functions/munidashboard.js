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
    
    /**
     * Simple base64 encoding (compatible with PHP decrypt())
     */
    function encrypt(str) {
        return btoa(str);
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
        const userId = queue.user_id || null;
        const userIdEncrypted = userId ? encrypt(userId.toString()) : null;
        
        return `
            <div class="muni-actions">
                <button class="muni-action-btn muni-action-btn--primary" 
                        onclick="viewDetails('${escapeHtml(queue.ip)}', '${escapeHtml(queue.name)}', '${escapeHtml(queue.department)}', '${userIdEncrypted || ''}')">
                    <i class="fas fa-chart-line"></i> Ver
                </button>
                ${queue.riskLevel === 'critical' && userIdEncrypted ? `
                    <button class="muni-action-btn muni-action-btn--danger" 
                            onclick="handleCriticalUser('${userIdEncrypted}', '${escapeHtml(queue.ip)}', '${escapeHtml(queue.name)}', '${escapeHtml(queue.max_limit)}')">
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
    
    window.viewDetails = function(ip, name, department, userId) {
        // Show loading while fetching data
        Swal.fire({
            title: 'Cargando detalles...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Fetch user details from backend
        $.post(base_url + '/munired/getUserDetails', { ip: ip }, function(response) {
            Swal.close();
            let data = JSON.parse(response);
            
            if (data.status === 'success') {
                showUserDetailsModal(data.data, ip, name, department);
            } else {
                // Fallback: show basic info if backend not ready
                showBasicUserModal(ip, name, department);
            }
        }).fail(function() {
            Swal.close();
            showBasicUserModal(ip, name, department);
        });
    };
    
    /**
     * Show detailed user modal with stats
     */
    function showUserDetailsModal(userData, ip, name, department) {
        const consumption = userData.consumption || { download: 0, upload: 0 };
        const limits = userData.limits || { upload: '5M', download: '10M' };
        const history = userData.history || [];
        
        // Calculate usage percentage
        const downloadLimit = parseMikroTikRate(limits.download);
        const usagePercent = downloadLimit > 0 ? Math.round((consumption.download / downloadLimit) * 100) : 0;
        
        // Build history rows
        let historyHtml = '';
        if (history.length > 0) {
            historyHtml = history.slice(0, 7).map(day => `
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem;">${day.date}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem; font-family: var(--font-mono);">${formatBytes(day.download)}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem; font-family: var(--font-mono);">${formatBytes(day.upload)}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem;">${day.peak_time || '--'}</td>
                </tr>
            `).join('');
        } else {
            historyHtml = '<tr><td colspan="4" style="padding: 16px; text-align: center; color: #718096;">Sin historial disponible</td></tr>';
        }
        
        Swal.fire({
            title: '',
            html: `
                <div style="text-align: left; max-width: 600px;">
                    <!-- Header -->
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #6366f1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0a0f1a;">${name}</h3>
                            <p style="margin: 4px 0 0 0; color: #718096; font-size: 0.875rem;">${department || 'Sin departamento'} • <code style="background: #f7fafc; padding: 2px 6px; border-radius: 4px;">${ip}</code></p>
                        </div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
                        <div style="background: #f7fafc; padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-mono); color: #3b82f6;">${formatBytes(consumption.download)}</div>
                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px;">Descargado</div>
                        </div>
                        <div style="background: #f7fafc; padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-mono); color: #6366f1;">${formatBytes(consumption.upload)}</div>
                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px;">Subido</div>
                        </div>
                        <div style="background: ${usagePercent > 90 ? '#fee2e2' : (usagePercent > 70 ? '#fef3c7' : '#d1fae5')}; padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-mono); color: ${usagePercent > 90 ? '#ef4444' : (usagePercent > 70 ? '#f59e0b' : '#10b981')};">${usagePercent}%</div>
                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px;">Uso del límite</div>
                        </div>
                    </div>
                    
                    <!-- Limits Info -->
                    <div style="background: #f7fafc; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.875rem; color: #4a5568;">Límite asignado:</span>
                            <span style="font-family: var(--font-mono); font-weight: 600; color: #0a0f1a;">${limits.upload} / ${limits.download}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; color: #4a5568;">Estado:</span>
                            <span style="font-weight: 600; color: ${usagePercent > 90 ? '#ef4444' : (usagePercent > 70 ? '#f59e0b' : '#10b981')};">
                                ${usagePercent > 90 ? '🔴 Crítico' : (usagePercent > 70 ? '🟡 Advertencia' : '🟢 Normal')}
                            </span>
                        </div>
                    </div>
                    
                    <!-- History Table -->
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 1rem; font-weight: 600; color: #0a0f1a;">
                            <i class="fas fa-history"></i> Histórico de Consumo (Últimos 7 días)
                        </h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #edf2f7;">
                                    <th style="padding: 8px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4a5568;">Fecha</th>
                                    <th style="padding: 8px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4a5568;">Descarga</th>
                                    <th style="padding: 8px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4a5568;">Subida</th>
                                    <th style="padding: 8px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #4a5568;">Hora Pico</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${historyHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `,
            width: '700px',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'muni-details-modal'
            }
        });
    }
    
    /**
     * Fallback: Show basic user modal (when backend not ready)
     */
    function showBasicUserModal(ip, name, department) {
        Swal.fire({
            title: name || 'Detalles del Usuario',
            html: `
                <div style="text-align: left;">
                    <p><strong>IP:</strong> <code style="background: #f7fafc; padding: 4px 8px; border-radius: 4px;">${ip}</code></p>
                    ${department ? `<p><strong>Departamento:</strong> ${department}</p>` : ''}
                    <hr style="margin: 16px 0; border: 1px solid #e2e8f0;">
                    <p style="color: #718096; font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> 
                        El historial detallado de consumo se está implementando. 
                        Próximamente disponible con gráficas de tendencia.
                    </p>
                </div>
            `,
            icon: 'info',
            showCloseButton: true,
            showConfirmButton: false
        });
    }
    
    window.handleCriticalUser = function(userId, ip, name, currentLimit) {
        Swal.fire({
            title: '⚠️ Usuario en estado crítico',
            html: `
                <p><strong>${name}</strong> (${ip}) ha excedido el 90% de su límite de ancho de banda.</p>
                <p>Límite actual: <code style="font-family: var(--font-mono); background: #f7fafc; padding: 4px 8px; border-radius: 4px;">${formatLimit(currentLimit)}</code></p>
                <p style="margin-top: 16px;">¿Qué acción desea tomar?</p>
            `,
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-chart-line"></i> Ampliar límite',
            denyButtonText: '<i class="fas fa-ban"></i> Bloquear usuario',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary',
                denyButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Ampliar límite
                expandBandwidthLimit(userId, name, currentLimit);
            } else if (result.isDenied) {
                // Bloquear usuario
                blockUser(userId, name, ip);
            }
        });
    };
    
    /**
     * Expand bandwidth limit for a user
     */
    function expandBandwidthLimit(userId, userName, currentLimit) {
        const limits = formatLimit(currentLimit).split(' / ');
        const currentUpload = limits[0] || '10M';
        const currentDownload = limits[1] || '10M';
        
        Swal.fire({
            title: 'Ampliar límite de ancho de banda',
            html: `
                <p style="margin-bottom: 16px;">Usuario: <strong>${userName}</strong></p>
                <div style="text-align: left; max-width: 400px; margin: 0 auto;">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px; font-size: 0.875rem;">
                            Subida (Upload)
                        </label>
                        <input id="swal-upload" class="swal2-input" 
                               value="${currentUpload}" 
                               placeholder="Ej: 20M, 1G, 512K"
                               style="font-family: var(--font-mono); width: 100%; margin: 0;">
                        <small style="color: #718096; font-size: 0.75rem;">Formato: 10M, 512K, 1G</small>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px; font-size: 0.875rem;">
                            Bajada (Download)
                        </label>
                        <input id="swal-download" class="swal2-input" 
                               value="${currentDownload}" 
                               placeholder="Ej: 50M, 2G, 1024K"
                               style="font-family: var(--font-mono); width: 100%; margin: 0;">
                        <small style="color: #718096; font-size: 0.75rem;">Formato: 10M, 512K, 1G</small>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Aplicar nuevo límite',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            preConfirm: () => {
                const upload = document.getElementById('swal-upload').value.trim().toUpperCase();
                const download = document.getElementById('swal-download').value.trim().toUpperCase();
                
                // Validate format
                const formatRegex = /^\d+(\.\d+)?[KMG]$/;
                if (!formatRegex.test(upload) || !formatRegex.test(download)) {
                    Swal.showValidationMessage('Formato inválido. Use: 10M, 512K, 1G');
                    return false;
                }
                
                return { upload, download };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { upload, download } = result.value;
                
                Swal.fire({
                    title: 'Aplicando cambios...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.post(base_url + '/munired/updateUserBandwidth', {
                    id: userId,
                    upload: upload,
                    download: download
                }, function (response) {
                    let res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            title: '¡Límite actualizado!',
                            html: `Nuevo límite: <strong>${upload} / ${download}</strong>`,
                            icon: 'success',
                            timer: 3000
                        });
                        // Reload stats
                        setTimeout(() => loadBandwidthStats(), 1000);
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                });
            }
        });
    }
    
    /**
     * Block user (disable bandwidth)
     */
    function blockUser(userId, userName, userIp) {
        Swal.fire({
            title: '⛔ Bloquear usuario',
            html: `
                <p>¿Está seguro que desea <strong>bloquear</strong> a:</p>
                <p style="margin-top: 12px;"><strong>${userName}</strong> (${userIp})?</p>
                <p style="margin-top: 12px; color: #ef4444; font-size: 0.875rem;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    El usuario perderá acceso a internet inmediatamente.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-ban"></i> Sí, bloquear',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Bloqueando usuario...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.post(base_url + '/munired/toggleUser', {
                    id: userId,
                    status: 0  // 0 = disabled
                }, function (response) {
                    let res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Usuario bloqueado',
                            text: `${userName} ha sido bloqueado exitosamente.`,
                            icon: 'success',
                            timer: 3000
                        });
                        // Reload stats
                        setTimeout(() => loadBandwidthStats(), 1000);
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                });
            }
        });
    }
    
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
