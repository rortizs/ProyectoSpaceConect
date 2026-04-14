/**
 * MuniProgressLoader — Animated progress loader for Municipal operations
 * 
 * 3 levels:
 *   MuniLoader.instant(title)               → simple spinner (fast ops ≤1s)
 *   MuniLoader.processing(title, steps)     → step-by-step bar (1–5s)
 *   MuniLoader.sync(title, steps)           → full animated sync (5–15s)
 * 
 * Usage:
 *   const loader = MuniLoader.sync('Sincronizando router...', syncSteps);
 *   loader.next(); // advance to next step
 *   loader.done(); // complete and close
 *   loader.fail('Mensaje de error');
 */

window.MuniLoader = (function () {
    'use strict';

    // ─────────────────────────────────────────────
    // LEVEL 1 — instant: simple spinner, no bar
    // Used for: viewDetails, testRouterConnection
    // ─────────────────────────────────────────────
    function instant(title) {
        Swal.fire({
            title: title || 'Cargando...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        return {
            close: () => Swal.close(),
            fail: (msg) => Swal.fire('Error', msg, 'error')
        };
    }

    // ─────────────────────────────────────────────
    // LEVEL 2 — processing: real step-by-step bar
    // Used for: expandBandwidth, blockUser, syncFiltering
    //
    // steps = [
    //   { label: 'Validando datos...',     weight: 20 },
    //   { label: 'Aplicando en router...', weight: 60 },
    //   { label: 'Actualizando BD...',     weight: 20 },
    // ]
    // ─────────────────────────────────────────────
    function processing(title, steps) {
        steps = steps || [{ label: 'Procesando...', weight: 100 }];

        let currentStep = 0;
        let accumulatedPercent = 0;

        // Normalize weights to 100%
        const totalWeight = steps.reduce((s, st) => s + (st.weight || 1), 0);
        const normalizedSteps = steps.map(st => ({
            label: st.label,
            percent: Math.round(((st.weight || 1) / totalWeight) * 100)
        }));

        function buildHtml(stepIdx, percent) {
            const step = normalizedSteps[stepIdx] || normalizedSteps[normalizedSteps.length - 1];
            const stepsListHtml = normalizedSteps.map((s, i) => {
                let icon = '';
                if (i < stepIdx) icon = '<span class="muni-loader__step-icon muni-loader__step-icon--done">✓</span>';
                else if (i === stepIdx) icon = '<span class="muni-loader__step-icon muni-loader__step-icon--active">▶</span>';
                else icon = '<span class="muni-loader__step-icon muni-loader__step-icon--pending">○</span>';

                return `<div class="muni-loader__step ${i === stepIdx ? 'muni-loader__step--active' : ''} ${i < stepIdx ? 'muni-loader__step--done' : ''}">
                    ${icon} ${s.label}
                </div>`;
            }).join('');

            return `
                <div class="muni-loader__container">
                    <div class="muni-loader__title">${title}</div>
                    
                    <div class="muni-loader__progress-wrap">
                        <div class="muni-loader__progress-bar">
                            <div class="muni-loader__progress-fill" 
                                 id="muniProgressFill"
                                 style="width: ${percent}%">
                            </div>
                        </div>
                        <div class="muni-loader__percent" id="muniProgressPercent">${percent}%</div>
                    </div>
                    
                    <div class="muni-loader__current-step" id="muniCurrentStep">
                        ${step.label}
                    </div>
                    
                    <div class="muni-loader__steps-list" id="muniStepsList">
                        ${stepsListHtml}
                    </div>
                </div>
            `;
        }

        Swal.fire({
            html: buildHtml(0, 0),
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'muni-loader__popup' },
            didOpen: () => {
                // Animate to first step smoothly
                setTimeout(() => _animateTo(0, normalizedSteps[0].percent), 200);
            }
        });

        function _animateTo(stepIdx, targetPercent) {
            const fill = document.getElementById('muniProgressFill');
            const percentEl = document.getElementById('muniProgressPercent');
            const currentStepEl = document.getElementById('muniCurrentStep');
            const stepsListEl = document.getElementById('muniStepsList');

            if (!fill) return;

            fill.style.width = targetPercent + '%';
            percentEl && (percentEl.textContent = targetPercent + '%');

            const step = normalizedSteps[stepIdx];
            if (step && currentStepEl) {
                currentStepEl.textContent = step.label;
            }

            // Rebuild steps list
            if (stepsListEl) {
                stepsListEl.innerHTML = normalizedSteps.map((s, i) => {
                    let icon = '';
                    if (i < stepIdx) icon = '<span class="muni-loader__step-icon muni-loader__step-icon--done">✓</span>';
                    else if (i === stepIdx) icon = '<span class="muni-loader__step-icon muni-loader__step-icon--active">▶</span>';
                    else icon = '<span class="muni-loader__step-icon muni-loader__step-icon--pending">○</span>';
                    return `<div class="muni-loader__step ${i === stepIdx ? 'muni-loader__step--active' : ''} ${i < stepIdx ? 'muni-loader__step--done' : ''}">
                        ${icon} ${s.label}
                    </div>`;
                }).join('');
            }
        }

        return {
            // Advance to next step
            next: function () {
                currentStep++;
                if (currentStep >= normalizedSteps.length) return;
                accumulatedPercent += normalizedSteps[currentStep - 1].percent;
                const target = Math.min(accumulatedPercent + normalizedSteps[currentStep].percent, 99);
                _animateTo(currentStep, target);
            },

            // Set explicit percent (0-100) with optional message override
            setPercent: function (percent, message) {
                const fill = document.getElementById('muniProgressFill');
                const percentEl = document.getElementById('muniProgressPercent');
                const currentStepEl = document.getElementById('muniCurrentStep');
                if (fill) fill.style.width = Math.min(percent, 99) + '%';
                if (percentEl) percentEl.textContent = Math.min(percent, 99) + '%';
                if (message && currentStepEl) currentStepEl.textContent = message;
            },

            // Complete: 100% → then close or success
            done: function (successTitle, successMsg) {
                const fill = document.getElementById('muniProgressFill');
                const percentEl = document.getElementById('muniProgressPercent');
                const currentStepEl = document.getElementById('muniCurrentStep');
                if (fill) fill.style.width = '100%';
                if (percentEl) percentEl.textContent = '100%';
                if (currentStepEl) currentStepEl.textContent = '¡Completado!';

                // Small delay to show 100% before showing success
                setTimeout(() => {
                    if (successTitle) {
                        Swal.fire({
                            title: successTitle,
                            text: successMsg || '',
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.close();
                    }
                }, 400);
            },

            // Error: show error message
            fail: function (msg) {
                Swal.fire('Error', msg || 'Ocurrió un error inesperado', 'error');
            },

            close: () => Swal.close()
        };
    }

    // ─────────────────────────────────────────────
    // LEVEL 3 — sync: full animated progress
    // Used for: syncAll (dashboard), syncAllQueues, syncAllFromConfig
    //
    // Designed for 5-15s operations. Uses smooth animation
    // + real backend-driven updates for maximum perceived speed.
    //
    // steps = [
    //   { label: 'Conectando al router...', weight: 10 },
    //   { label: 'Leyendo colas activas...', weight: 20 },
    //   ...
    // ]
    // ─────────────────────────────────────────────
    function sync(title, steps) {
        steps = steps || [
            { label: 'Iniciando sincronización...', weight: 10 },
            { label: 'Conectando al router...', weight: 15 },
            { label: 'Leyendo colas activas...', weight: 20 },
            { label: 'Procesando usuarios...', weight: 30 },
            { label: 'Aplicando cambios...', weight: 15 },
            { label: 'Actualizando base de datos...', weight: 10 }
        ];

        // Same normalization as processing()
        const totalWeight = steps.reduce((s, st) => s + (st.weight || 1), 0);
        let cumulative = 0;
        const normalizedSteps = steps.map(st => {
            const pct = Math.round(((st.weight || 1) / totalWeight) * 100);
            const startPct = cumulative;
            cumulative += pct;
            return { label: st.label, startPct, endPct: cumulative };
        });

        let currentStepIdx = 0;
        let animationFrame = null;
        let currentDisplayPercent = 0;
        let targetPercent = 0;
        let _closed = false;

        // Smooth animation loop
        function _animLoop() {
            if (_closed) return;
            if (currentDisplayPercent < targetPercent) {
                currentDisplayPercent = Math.min(currentDisplayPercent + 0.8, targetPercent);
                const fill = document.getElementById('muniSyncFill');
                const percentEl = document.getElementById('muniSyncPercent');
                if (fill) fill.style.width = currentDisplayPercent + '%';
                if (percentEl) percentEl.textContent = Math.round(currentDisplayPercent) + '%';
            }
            animationFrame = requestAnimationFrame(_animLoop);
        }

        function _buildHtml() {
            const activeStep = normalizedSteps[currentStepIdx];
            return `
                <div class="muni-loader__sync-container">
                    <div class="muni-loader__sync-icon">
                        <div class="muni-loader__sync-spinner"></div>
                    </div>
                    
                    <div class="muni-loader__sync-title">${title}</div>
                    
                    <div class="muni-loader__sync-progress-wrap">
                        <div class="muni-loader__sync-bar">
                            <div class="muni-loader__sync-fill" id="muniSyncFill" style="width: 0%">
                                <div class="muni-loader__sync-shine"></div>
                            </div>
                        </div>
                        <div class="muni-loader__sync-percent" id="muniSyncPercent">0%</div>
                    </div>
                    
                    <div class="muni-loader__sync-step" id="muniSyncStep">
                        ${activeStep ? activeStep.label : 'Iniciando...'}
                    </div>
                    
                    <div class="muni-loader__sync-steps">
                        ${normalizedSteps.map((s, i) => `
                            <div class="muni-loader__sync-item" id="muniSyncItem_${i}">
                                <span class="muni-loader__sync-dot" id="muniSyncDot_${i}"></span>
                                <span class="muni-loader__sync-label">${s.label}</span>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="muni-loader__sync-note">
                        La operación puede tardar algunos segundos mientras se comunica con el router
                    </div>
                </div>
            `;
        }

        function _updateStep(idx) {
            // Update dots
            normalizedSteps.forEach((_, i) => {
                const dot = document.getElementById(`muniSyncDot_${i}`);
                const item = document.getElementById(`muniSyncItem_${i}`);
                if (!dot || !item) return;
                dot.className = 'muni-loader__sync-dot';
                item.className = 'muni-loader__sync-item';
                if (i < idx) {
                    dot.classList.add('muni-loader__sync-dot--done');
                    item.classList.add('muni-loader__sync-item--done');
                } else if (i === idx) {
                    dot.classList.add('muni-loader__sync-dot--active');
                    item.classList.add('muni-loader__sync-item--active');
                }
            });

            // Update step label
            const stepEl = document.getElementById('muniSyncStep');
            if (stepEl && normalizedSteps[idx]) {
                stepEl.textContent = normalizedSteps[idx].label;
            }
        }

        Swal.fire({
            html: _buildHtml(),
            allowOutsideClick: false,
            showConfirmButton: false,
            width: '480px',
            customClass: { popup: 'muni-loader__popup muni-loader__popup--sync' },
            didOpen: () => {
                _animLoop();
                // Start with first step target
                targetPercent = normalizedSteps[0] ? normalizedSteps[0].endPct * 0.5 : 5;
                _updateStep(0);
            },
            willClose: () => {
                _closed = true;
                if (animationFrame) cancelAnimationFrame(animationFrame);
            }
        });

        return {
            // Advance to next step (call as each backend stage starts)
            next: function () {
                currentStepIdx = Math.min(currentStepIdx + 1, normalizedSteps.length - 1);
                const step = normalizedSteps[currentStepIdx];
                if (step) {
                    // Advance target to ~70% of new step's range
                    targetPercent = step.startPct + (step.endPct - step.startPct) * 0.7;
                    _updateStep(currentStepIdx);
                }
            },

            // Set explicit percent (useful if backend streams progress)
            setPercent: function (percent, message) {
                targetPercent = Math.min(percent, 99);
                if (message) {
                    const stepEl = document.getElementById('muniSyncStep');
                    if (stepEl) stepEl.textContent = message;
                }
            },

            // Complete the operation
            done: function (successTitle, successHtml) {
                _closed = true;
                if (animationFrame) cancelAnimationFrame(animationFrame);

                // Flash to 100%
                const fill = document.getElementById('muniSyncFill');
                const percentEl = document.getElementById('muniSyncPercent');
                const stepEl = document.getElementById('muniSyncStep');
                if (fill) fill.style.transition = 'width 0.3s ease';
                if (fill) fill.style.width = '100%';
                if (percentEl) percentEl.textContent = '100%';
                if (stepEl) stepEl.textContent = '¡Sincronización completa!';

                // Mark all steps done
                normalizedSteps.forEach((_, i) => {
                    const dot = document.getElementById(`muniSyncDot_${i}`);
                    const item = document.getElementById(`muniSyncItem_${i}`);
                    if (dot) { dot.className = 'muni-loader__sync-dot muni-loader__sync-dot--done'; }
                    if (item) { item.className = 'muni-loader__sync-item muni-loader__sync-item--done'; }
                });

                setTimeout(() => {
                    if (successTitle) {
                        Swal.fire({
                            title: successTitle,
                            html: successHtml || '',
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.close();
                    }
                }, 500);
            },

            fail: function (msg) {
                _closed = true;
                if (animationFrame) cancelAnimationFrame(animationFrame);
                Swal.fire('Error en sincronización', msg || 'No se pudo completar la operación', 'error');
            },

            close: function () {
                _closed = true;
                if (animationFrame) cancelAnimationFrame(animationFrame);
                Swal.close();
            }
        };
    }

    // ─────────────────────────────────────────────
    // PRE-DEFINED step sets for each operation
    // ─────────────────────────────────────────────
    const STEPS = {
        syncAll: [
            { label: 'Conectando al router MikroTik...', weight: 10 },
            { label: 'Leyendo colas activas (Simple Queues)...', weight: 20 },
            { label: 'Comparando con base de datos...', weight: 15 },
            { label: 'Creando colas nuevas...', weight: 20 },
            { label: 'Actualizando límites existentes...', weight: 20 },
            { label: 'Eliminando colas huérfanas...', weight: 10 },
            { label: 'Guardando estado en BD...', weight: 5 }
        ],
        syncDept: [
            { label: 'Conectando al router...', weight: 15 },
            { label: 'Leyendo usuarios del departamento...', weight: 25 },
            { label: 'Sincronizando colas...', weight: 45 },
            { label: 'Actualizando registros...', weight: 15 }
        ],
        syncFiltering: [
            { label: 'Conectando al router...', weight: 10 },
            { label: 'Leyendo reglas de filtrado...', weight: 20 },
            { label: 'Aplicando políticas por departamento...', weight: 40 },
            { label: 'Actualizando DNS y listas...', weight: 20 },
            { label: 'Guardando configuración...', weight: 10 }
        ],
        expandBandwidth: [
            { label: 'Validando nuevos límites...', weight: 15 },
            { label: 'Conectando al router...', weight: 20 },
            { label: 'Actualizando Simple Queue...', weight: 45 },
            { label: 'Guardando en base de datos...', weight: 20 }
        ],
        blockUser: [
            { label: 'Verificando usuario...', weight: 20 },
            { label: 'Conectando al router...', weight: 20 },
            { label: 'Aplicando throttle (1k/1k)...', weight: 45 },
            { label: 'Registrando acción...', weight: 15 }
        ],
        testConnection: [
            { label: 'Enviando ping al router...', weight: 40 },
            { label: 'Leyendo información del sistema...', weight: 60 }
        ]
    };

    return { instant, processing, sync, STEPS };

})();
