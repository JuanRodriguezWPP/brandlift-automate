    // ====================================================================
    // DASHBOARD — Brandlift History
    // ====================================================================

    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);

    const MONTHS_ES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const MARKET_NAMES = {
        'PE': 'Perú', 'PRI': 'Puerto Rico', 'ARG': 'Argentina',
        'MIA': 'Miami', 'MEX': 'México', 'CHL': 'Chile',
        'COL': 'Colombia', 'ECU': 'Ecuador'
    };
    const STATUS_LABELS = { 'created': 'Creado', 'pushed': 'Subido', 'cm360_pushed': 'Subido', 'error': 'Error' };

    let currentPage = 1;
    let deleteTargetId = null;
    let debounceTimer = null;

    // ===== FETCH HISTORY =====
    async function fetchHistory(page = 1) {
        currentPage = page;
        const search = $('#filter-search').value.trim();
        const market = $('#filter-market').value;
        const status = $('#filter-status').value;
        const dateFrom = $('#filter-date-from').value;
        const dateTo = $('#filter-date-to').value;

        const params = new URLSearchParams();
        params.set('page', page);
        params.set('per_page', 15);
        if (search) params.set('search', search);
        if (market) params.set('market', market);
        if (status) params.set('status', status);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);

        // Show/hide clear button
        const hasFilters = search || market || status || dateFrom || dateTo;
        $('#btn-clear-filters').classList.toggle('visible', !!hasFilters);

        // Show skeleton while loading
        showSkeleton();

        try {
            const res = await fetch(`/api/brandlift/history?${params.toString()}`);
            const data = await res.json();

            updateKPIs(data.stats);
            renderTable(data.studies);
        } catch (e) {
            console.error('Error fetching history:', e);
            showToast('❌ Error al cargar el historial', true);
        }
    }

    // ===== UPDATE KPIs =====
    function updateKPIs(stats) {
        animateCounter('kpi-total', stats.total);
        animateCounter('kpi-month', stats.this_month);
        animateCounter('kpi-active', stats.active);

        const now = new Date();
        $('#kpi-month-label').textContent = `Brandlifts en ${MONTHS_ES[now.getMonth()]}`;
    }

    function animateCounter(id, target) {
        const el = $(`#${id}`);
        if (!el) return;
        const current = parseInt(el.textContent) || 0;
        if (current === target) { el.textContent = target; return; }

        const duration = 600;
        const start = performance.now();

        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(current + (target - current) * eased);
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    // ===== RENDER TABLE =====
    function renderTable(paginatedData) {
        const tbody = $('#table-body');
        const data = paginatedData.data || [];
        
        // Expose globally for the edit modal
        window.allStudies = data;

        if (data.length === 0) {
            tbody.innerHTML = '';
            $('#empty-state').style.display = 'block';
            $('#pagination-bar').style.display = 'none';
            return;
        }

        $('#empty-state').style.display = 'none';

        tbody.innerHTML = data.map((study, idx) => {
            const date = new Date(study.created_at);
            const dateStr = date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeStr = date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            const statusClass = `badge-${study.status}`;
            const statusLabel = STATUS_LABELS[study.status] || study.status;

            const audiencesText = (study.audiences || []).join(', ');
            const dpsTagsText = (study.dps_tags || []).join(', ');
            const endDateStr = study.end_date ? new Date(study.end_date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

            let vigenciaHtml = '-';
            if (study.end_date) {
                const now = new Date();
                now.setHours(0, 0, 0, 0);
                const [y, m, d] = study.end_date.split('-');
                const endDate = new Date(y, m - 1, d);

                if (endDate >= now) {
                    vigenciaHtml = `<span class="badge badge-pushed"><span class="badge-dot"></span>Activo</span>`;
                } else {
                    vigenciaHtml = `<span class="badge badge-error"><span class="badge-dot"></span>Inactivo</span>`;
                }
            }

            return `
                <tr data-id="${study.id}" style="animation: fadeInUp 0.4s ease-out ${idx * 40}ms both">
                    <td class="td-id">${study.id}</td>
                    <td class="td-campaign" title="${escapeHtml(study.campaign_name)}">${escapeHtml(study.campaign_name)}</td>
                    <td title="${escapeHtml(study.client_name || '-')}">${escapeHtml(study.client_name || '-')}</td>
                    <td title="${escapeHtml(audiencesText || '-')}">${escapeHtml(audiencesText || '-')}</td>
                    <td title="${escapeHtml(dpsTagsText || '-')}">${escapeHtml(dpsTagsText || '-')}</td>
                    <td>${study.question_count}</td>
                    <td>${endDateStr}</td>
                    <td>${vigenciaHtml}</td>
                    <td><span class="badge ${statusClass}"><span class="badge-dot"></span>${statusLabel}</span></td>
                    <td>
                        <div style="font-size:13px">${dateStr}</div>
                        <div style="font-size:11px;color:var(--text-muted)">${timeStr}</div>
                    </td>
                    <td>
                        <div class="actions-cell" onclick="event.stopPropagation()">
                            <button class="btn-action" title="Ver detalle" onclick="openDetail(${study.id})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn-action" title="Historial de Edición" onclick="openHistory(${study.id})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                            </button>
                            <button class="btn-action danger" title="Eliminar" onclick="confirmDelete(${study.id}, '${escapeHtml(study.campaign_name)}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Row click → detail
        tbody.querySelectorAll('tr').forEach(row => {
            row.addEventListener('click', () => openDetail(row.dataset.id));
        });

        // Pagination
        renderPagination(paginatedData);
    }

    function showSkeleton() {
        const tbody = $('#table-body');
        tbody.innerHTML = Array.from({ length: 5 }, () => `
            <tr class="skeleton-row">
                <td><div class="skeleton-bar" style="width:30px"></div></td>
                <td><div class="skeleton-bar" style="width:180px"></div></td>
                <td><div class="skeleton-bar" style="width:50px"></div></td>
                <td><div class="skeleton-bar" style="width:20px"></div></td>
                <td><div class="skeleton-bar" style="width:70px"></div></td>
                <td><div class="skeleton-bar" style="width:80px"></div></td>
                <td><div class="skeleton-bar" style="width:60px"></div></td>
            </tr>
        `).join('');
    }

    // ===== PAGINATION =====
    function renderPagination(paginatedData) {
        const bar = $('#pagination-bar');
        const info = $('#pagination-info');
        const buttons = $('#pagination-buttons');

        if (paginatedData.last_page <= 1) {
            bar.style.display = 'none';
            return;
        }

        bar.style.display = 'flex';
        info.textContent = `Mostrando ${paginatedData.from}–${paginatedData.to} de ${paginatedData.total}`;

        buttons.innerHTML = '';

        // Prev
        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn-page';
        prevBtn.textContent = '←';
        prevBtn.disabled = paginatedData.current_page === 1;
        prevBtn.onclick = () => fetchHistory(paginatedData.current_page - 1);
        buttons.appendChild(prevBtn);

        // Page numbers
        const maxVisible = 5;
        let start = Math.max(1, paginatedData.current_page - Math.floor(maxVisible / 2));
        let end = Math.min(paginatedData.last_page, start + maxVisible - 1);
        if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);

        for (let p = start; p <= end; p++) {
            const btn = document.createElement('button');
            btn.className = `btn-page ${p === paginatedData.current_page ? 'active' : ''}`;
            btn.textContent = p;
            btn.onclick = () => fetchHistory(p);
            buttons.appendChild(btn);
        }

        // Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn-page';
        nextBtn.textContent = '→';
        nextBtn.disabled = paginatedData.current_page === paginatedData.last_page;
        nextBtn.onclick = () => fetchHistory(paginatedData.current_page + 1);
        buttons.appendChild(nextBtn);
    }

    
    // ===== HISTORY MODAL =====
    function closeHistoryModal() {
        $('#history-modal').classList.remove('active');
    }

    async function openHistory(studyId) {
        const modal = $('#history-modal');
        const body = $('#history-modal-body');

        body.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;">⏳</div><p style="color:var(--text-muted)">Cargando historial...</p></div>';
        modal.classList.add('active');

        try {
            const res = await fetch(`/api/brandlift/history/${studyId}`);
            const data = await res.json();
            const logs = data.study.edit_logs || [];

            if (logs.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted)">No hay registros de edición para esta campaña.</div>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 15px;">';
            
            logs.forEach(log => {
                const date = new Date(log.created_at);
                const dateStr = date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                const userName = log.user ? log.user.name : 'Desconocido';
                
                let details = '';
                if (log.changes_made && log.changes_made.old_questions && log.changes_made.new_questions) {
                    details = `<div style="font-size: 12px; margin-top: 5px; color: var(--text-secondary); background: rgba(0,0,0,0.02); padding: 8px; border-radius: 4px;">
                        <strong>Antes:</strong> ${log.changes_made.old_questions.length} preguntas<br>
                        <strong>Después:</strong> ${log.changes_made.new_questions.length} preguntas<br>
                        (Revisar base de datos para detalle completo de preguntas y respuestas)
                    </div>`;
                }

                html += `
                    <div style="border-left: 3px solid var(--wpp-lime); padding-left: 15px; background: var(--bg-card); padding: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                            <strong style="color: var(--wpp-navy); font-size: 14px;">${escapeHtml(userName)}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">${dateStr}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-primary);">Editó las preguntas del Brandlift</div>
                        ${details}
                    </div>
                `;
            });

            html += '</div>';
            body.innerHTML = html;
        } catch (e) {
            console.error('Error in openHistory:', e);
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#fca5a5">❌ Error al cargar el historial</div>';
        }
    }

    $('#history-modal').addEventListener('click', (e) => {
        if (e.target === $('#history-modal')) closeHistoryModal();
    });

    // ===== DETAIL MODAL =====
    async function openDetail(id) {
        const modal = $('#detail-modal');
        const body = $('#modal-body');

        body.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;">⏳</div><p style="color:var(--text-muted)">Cargando...</p></div>';
        modal.classList.add('active');

        try {
            const res = await fetch(`/api/brandlift/history/${id}`);
            const data = await res.json();
            const s = data.study;

            const date = new Date(s.created_at);
            const dateStr = date.toLocaleDateString('es-ES', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
            const timeStr = date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            const statusClass = `badge-${s.status}`;
            const statusLabel = STATUS_LABELS[s.status] || s.status;

            let cm360Html = '';
            let downloadTagsBtn = '';
            if (s.cm360_pushed) {
                                const pushedDate = s.cm360_pushed_at ? new Date(s.cm360_pushed_at).toLocaleString('es-ES') : '—';
                
                if (s.cm360_tags) {
                    const tagsDataStr = typeof s.cm360_tags === 'string' ? escapeAttr(s.cm360_tags) : escapeAttr(JSON.stringify(s.cm360_tags));
                    downloadTagsBtn = `
                        <button class="btn btn-secondary" onclick="downloadTagsFromDashboard(this)" data-tags="\${tagsDataStr}" data-market="\${escapeAttr(s.market)}" data-client="\${escapeAttr(s.client_name || '')}" data-campaign="\${escapeAttr(s.campaign_name)}" style="font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--wpp-lime); color: var(--wpp-navy); border: none; font-weight: 600; flex: 1;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Descargar Tags CM360
                        </button>
                    `;
                }
                

                cm360Html = `
                    <div class="detail-section">
                        <h4>• Campaign Manager 360</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Campaign ID</div>
                                <div class="detail-value">${s.cm360_campaign_id || '—'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Subido</div>
                                <div class="detail-value">${pushedDate}</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            let questionsHtml = '';
            if (s.questions && s.questions.length > 0) {
                const qHtml = s.questions.map(q => {
                    const answers = (q.answers || []).map(a => escapeHtml(a)).join(' · ');
                    return `
                        <div class="question-detail-block">
                            <div class="q-num">Pregunta ${q.question_number}</div>
                            <div class="q-text">${escapeHtml(q.question_text)}</div>
                            <div style="font-size: 13px; color: var(--text-muted);"><strong>Respuestas:</strong> <span style="color: var(--text-secondary);">${answers}</span></div>
                        </div>
                    `;
                }).join('');
                questionsHtml = `<div class="detail-grid" style="margin-bottom: 0;">${qHtml}</div>`;
            }

            let previewHtml = '';
            const editBtnHtml = `
                <button onclick="openEditQuestionsModal(${s.id})" style="background-color: var(--wpp-navy); color: #ffffff; border: none; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease; flex: 1;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    Editar Preguntas
                </button>
            `;
            
            const accionesBlockHtml = `
                <div class="detail-section">
                    <h4>• Acciones</h4>
                    <div style="display: flex; gap: 10px; width: 100%;">
                        ${editBtnHtml}
                        ${downloadTagsBtn}
                    </div>
                </div>
            `;
            let clickActionHtmlTop = '';
            const firstQ = s.questions && s.questions.length > 0 ? s.questions[0] : null;
            if (firstQ && firstQ.creative_html) {
                const hasClickEvent = firstQ.creative_html.includes('clickTag');
                if (hasClickEvent) {
                    clickActionHtmlTop = `
                        <div style="margin-bottom: 20px; display: flex; justify-content: flex-start; align-items: center; gap: 15px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 16px;">
                            <button style="background-color: var(--wpp-navy); color: white; border: none; padding: 8px 16px; width: auto; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;" onclick="removeClickEvent(${s.id}, '${escapeHtml(s.campaign_name)}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/></svg>
                                Retirar redirección de click
                            </button>
                            <div style="background-color: #fef2f2; border: 1px solid #f87171; color: #dc2626; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span><strong>Importante:</strong> Después de pasar el audit recuerda retirar el evento de click</span>
                            </div>
                        </div>
                    `;
                }

                previewHtml = `
                    <div class="detail-section">
                        <h4>• Vista Previa</h4>
                        <div style="text-align: center; margin-bottom: 10px;">
                            <button type="button" class="btn btn-secondary btn-restart-preview" onclick="const f=document.querySelector('#dash-preview-frame iframe'); if(f){const src=f.srcdoc; f.srcdoc=''; setTimeout(()=>f.srcdoc=src,10);} this.style.display='none';" style="display: none; font-size: 12px; padding: 6px 12px; align-items: center; gap: 6px; cursor: pointer; background: transparent; border: 1px solid var(--border-card); border-radius: var(--radius-sm); color: var(--text-secondary); margin: 0 auto;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                Reiniciar
                            </button>
                        </div>
                        <div class="preview-mini-frame" id="dash-preview-frame">
                            <iframe srcdoc="${escapeAttr(firstQ.creative_html)}" width="${s.creative_width}" height="${s.creative_height}"></iframe>
                        </div>
                    </div>
                `;
            }



            body.innerHTML = `
                ${clickActionHtmlTop}
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Campaña</div>
                        <div class="detail-value">${escapeHtml(s.campaign_name)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mercado</div>
                        <div class="detail-value"><span class="badge badge-market">${s.market}</span> ${MARKET_NAMES[s.market] || ''}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value"><span class="badge ${statusClass}"><span class="badge-dot"></span>${statusLabel}</span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tamaño</div>
                        <div class="detail-value">${s.creative_width}×${s.creative_height}</div>
                    </div>
                    <div class="detail-item" style="grid-column: span 2">
                        <div class="detail-label">Creado</div>
                        <div class="detail-value">${dateStr} — ${timeStr}</div>
                    </div>
                </div>

                ${cm360Html}

                <div class="detail-section">
                    <h4>• Preguntas y Respuestas</h4>
                    ${questionsHtml || '<p style="color:var(--text-muted);font-size:13px">Sin preguntas registradas</p>'}
                </div>

                ${accionesBlockHtml}
                ${previewHtml}
            `;
        } catch (e) {
            console.error('Error in openDetail:', e);
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#fca5a5">❌ Error al cargar los detalles</div>';
        }
    }

    function closeModal() {
        $('#detail-modal').classList.remove('active');
    }

    $('#modal-close-btn').addEventListener('click', closeModal);
    $('#detail-modal').addEventListener('click', (e) => {
        if (e.target === $('#detail-modal')) closeModal();
    });

    // ===== DELETE =====
    function confirmDelete(id, name) {
        deleteTargetId = id;
        $('#confirm-message').textContent = `¿Estás seguro de eliminar "${name}"? Esta acción no se puede deshacer.`;
        $('#confirm-dialog').classList.add('active');
    }

    $('#btn-confirm-cancel').addEventListener('click', () => {
        $('#confirm-dialog').classList.remove('active');
        deleteTargetId = null;
    });

    $('#btn-confirm-delete').addEventListener('click', async () => {
        if (!deleteTargetId) return;

        const btn = $('#btn-confirm-delete');
        btn.textContent = 'Eliminando...';
        btn.disabled = true;

        try {
            const res = await fetch(`/api/brandlift/history/${deleteTargetId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                showToast('✅ Brandlift eliminado');
                fetchHistory(currentPage);
            } else {
                showToast('❌ Error al eliminar', true);
            }
        } catch (e) {
            showToast('❌ Error al eliminar', true);
        } finally {
            $('#confirm-dialog').classList.remove('active');
            deleteTargetId = null;
            btn.textContent = 'Sí, eliminar';
            btn.disabled = false;
        }
    });

    // ===== REMOVE CLICK EVENT =====
    async function removeClickEvent(id, name) {
        if (!confirm(`¿Estás seguro de que deseas retirar la redirección de click para "${name}"?\n\nSi está subida a CM360, esto tardará unos segundos mientras se actualizan los creativos.`)) {
            return;
        }

        const modalBody = $('#modal-body');
        modalBody.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;">⏳</div><p style="color:var(--text-muted)">Actualizando creativos en Base de Datos y CM360...</p><p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Por favor espera, no cierres esta ventana.</p></div>';

        try {
            const res = await fetch(`/api/brandlift/history/${id}/remove-click`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();
            
            if (res.ok && data.success) {
                showToast('✅ ' + data.message);
                openDetail(id); // Reload modal details
            } else {
                showToast('⚠️ Completado con advertencias: ' + (data.message || 'Error desconocido'), true);
                if (data.cm360_errors && data.cm360_errors.length > 0) {
                    console.error("CM360 Errors:", data.cm360_errors);
                }
                openDetail(id);
            }
        } catch (e) {
            showToast('❌ Error al retirar el evento', true);
            openDetail(id);
        }
    }

    // ===== FILTERS =====
    $('#filter-search').addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchHistory(1), 350);
    });

    $('#filter-market').addEventListener('change', () => fetchHistory(1));
    $('#filter-status').addEventListener('change', () => fetchHistory(1));
    $('#filter-date-from').addEventListener('change', () => fetchHistory(1));
    $('#filter-date-to').addEventListener('change', () => fetchHistory(1));

    $('#btn-clear-filters').addEventListener('click', () => {
        $('#filter-search').value = '';
        $('#filter-market').value = '';
        $('#filter-status').value = '';
        $('#filter-date-from').value = '';
        $('#filter-date-to').value = '';
        $('#btn-clear-filters').classList.remove('visible');
        fetchHistory(1);
    });

    // ===== KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            $('#confirm-dialog').classList.remove('active');
        }
    });

    // ===== UTILS =====
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function escapeAttr(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function showToast(message, isError = false) {
        const t = $('#toast');
        $('#toast-message').textContent = message;
        t.style.background = isError ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)';
        t.style.borderColor = isError ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)';
        t.style.color = isError ? '#fca5a5' : '#6ee7b7';
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // ===== EXCEL TAGS DOWNLOAD FROM DASHBOARD =====
    window.downloadTagsFromDashboard = function(btn) {
        try {
            const tagsStr = btn.getAttribute('data-tags');
            const tagsData = JSON.parse(tagsStr);
            if (!tagsData || tagsData.length === 0) {
                showToast('No hay tags disponibles para descargar');
                return;
            }
            
            const dataForExcel = [];
            dataForExcel.push(["Placement", "Placement ID", "Ad", "Ad ID", "Creative", "Creative ID", "Creative Type", "Tag Format", "Tag"]);
            
            tagsData.forEach(r => {
                if (r.ad_tags && r.ad_tags.length > 0) {
                    r.ad_tags.forEach(tag => {
                        const tagString = tag.impression_tag || tag.click_tag || '';
                        const placementName = r.creative_name || '';
                        const adName = r.creative_name || '';
                        const creativeName = r.creative_name || '';
                        const creativeId = r.creative_id || '';
                        const placementId = r.placement_id || '';
                        const adId = r.ad_id || '';
                        const creativeType = 'HTML5_BANNER';
                        const tagFormat = tag.format || 'PLACEMENT_TAG_IFRAME_JAVASCRIPT';
                        
                        dataForExcel.push([
                            placementName, placementId, adName, adId, creativeName, creativeId, creativeType, tagFormat, tagString
                        ]);
                    });
                }
            });
            
            const ws = XLSX.utils.aoa_to_sheet(dataForExcel);

            // Add styles for headers and wrapping
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let R = range.s.r; R <= range.e.r; ++R) {
                for (let C = range.s.c; C <= range.e.c; ++C) {
                    const cellRef = XLSX.utils.encode_cell({r: R, c: C});
                    if (!ws[cellRef]) continue;
                    
                    const cellStyle = {
                        font: { sz: 11, name: 'Calibri' },
                        alignment: { vertical: 'top' },
                        border: {
                            top: { style: 'thin', color: { rgb: "E0E0E0" } },
                            bottom: { style: 'thin', color: { rgb: "E0E0E0" } },
                            left: { style: 'thin', color: { rgb: "E0E0E0" } },
                            right: { style: 'thin', color: { rgb: "E0E0E0" } }
                        }
                    };

                    if (R === 0) {
                        cellStyle.font.bold = true;
                        cellStyle.fill = { fgColor: { rgb: "F0F0F0" } };
                    }

                    if (C === 8) { // Tag column
                        cellStyle.alignment.wrapText = true;
                    }

                    ws[cellRef].s = cellStyle;
                }
            }
            
            // Define column widths
            ws['!cols'] = [
                { wch: 35 }, // Placement
                { wch: 15 }, // Placement ID
                { wch: 35 }, // Ad
                { wch: 15 }, // Ad ID
                { wch: 35 }, // Creative
                { wch: 15 }, // Creative ID
                { wch: 18 }, // Creative Type
                { wch: 35 }, // Tag Format
                { wch: 90 }  // Tag
            ];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Tags");
            
            const market = btn.getAttribute('data-market') || '';
            let client = btn.getAttribute('data-client') || '';
            client = client.trim();
            let campaignName = btn.getAttribute('data-campaign') || 'Brandlift';
            campaignName = campaignName.trim();
            const year = new Date().getFullYear();
            const month = String(new Date().getMonth() + 1).padStart(2, '0');
            const fileName = `${year}_${month}_MCS_${market}_${client.replace(/\s+/g,'_')}_${campaignName}_Tags`;
            
            XLSX.writeFile(wb, `${fileName}.xlsx`);
        } catch (e) {
            console.error(e);
            showToast('Error al descargar tags');
        }
    };

    // ===== INIT =====
    fetchHistory(1);
        window.addEventListener('message', function(event) {
            if (event.data === 'brandlift_finished') {
                document.querySelectorAll('.btn-restart-preview').forEach(btn => btn.style.display = 'inline-flex');
            }
        });
    </script>
            </div><!-- .content-area -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
<!-- Edit Questions Modal -->
<div id="edit-questions-modal" class="modal-overlay hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
    <div style="background: var(--bg-card); width: 950px; max-width: 95%; max-height: 90vh; border-radius: var(--radius-md); border: 1px solid var(--border-card); padding: 25px; position: relative; display: flex; gap: 30px;">
        <button onclick="closeEditQuestionsModal()" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: var(--text-secondary); cursor: pointer; z-index: 10;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        
        <!-- Left: Form -->
        <div style="flex: 1; overflow-y: auto; padding-right: 15px;">
            <h3 style="color: var(--text-primary); margin-top: 0; margin-bottom: 20px;">Editar Preguntas</h3>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-secondary); font-size: 13px; font-weight: 600; margin-bottom: 8px;">Tema de Color</label>
                <div style="display: flex; gap: 10px;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: var(--text-primary);">
                        <input type="radio" name="edit-theme" value="dark" onchange="updateModalPreview()" checked> Oscuro
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: var(--text-primary);">
                        <input type="radio" name="edit-theme" value="light" onchange="updateModalPreview()"> Claro
                    </label>
                </div>
            </div>

            <div id="edit-questions-container" style="display: flex; flex-direction: column; gap: 20px;"></div>
            
            <!-- Actions moved to the right column -->
        </div>

        <!-- Right: Preview -->
        <div style="width: 330px; display: flex; flex-direction: column; align-items: center; border-left: 1px solid var(--border-card); padding-left: 20px;">

            
            <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; margin-bottom: 10px; align-self: flex-start;">Vista Previa</label>
            <div style="text-align: center; margin-bottom: 10px; width: 100%;">
                <button type="button" class="btn btn-secondary" onclick="updateModalPreview()" style="font-size: 12px; padding: 6px 12px; cursor: pointer; background: transparent; border: 1px solid var(--border-card); border-radius: var(--radius-sm); color: var(--text-secondary);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    Reiniciar
                </button>
            </div>
            <div style="border-radius: 4px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                <iframe id="edit-modal-preview-iframe" width="300" height="250" frameborder="0" style="display: block;"></iframe>
            </div>

            <div style="width: 100%; margin-top: 25px;">
                <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; margin-bottom: 10px; display: block;">Acciones</label>
                <div style="display: flex; gap: 10px; width: 100%;">
                    <button type="button" class="btn btn-secondary" onclick="addQuestionToEditModal()" style="font-size: 12px; flex: 1; padding: 8px 5px; justify-content: center; display: flex; align-items: center;">
                        + Agregar Pregunta
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedQuestions()" style="font-size: 12px; flex: 1; padding: 8px 5px; justify-content: center; display: flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

