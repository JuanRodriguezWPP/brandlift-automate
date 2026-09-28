<?php
$content = file_get_contents('resources/views/dashboard.blade.php');

// 1. Extract downloadTagsBtn logic and remove from cm360Html
$search1 = <<< 'TEXT'
                let downloadTagsHtml = '';
                if (s.cm360_tags) {
                    const tagsDataStr = typeof s.cm360_tags === 'string' ? escapeAttr(s.cm360_tags) : escapeAttr(JSON.stringify(s.cm360_tags));
                    downloadTagsHtml = `
                        <div style="margin-top: 15px;">
                            <button class="btn btn-secondary" onclick="downloadTagsFromDashboard(this)" data-tags="\${tagsDataStr}" data-market="\${escapeAttr(s.market)}" data-client="\${escapeAttr(s.client_name || '')}" data-campaign="\${escapeAttr(s.campaign_name)}" style="font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px; background: var(--wpp-teal); color: #fff; border: none; font-weight: 600;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Descargar Tags CM360
                            </button>
                        </div>
                    `;
                }
                
                cm360Html = `
                    <div class="detail-section">
                        <h4>• Campaign Manager 360</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Campaign ID</div>
                                <div class="detail-value">\${s.cm360_campaign_id || '—'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Subido</div>
                                <div class="detail-value">\${pushedDate}</div>
                            </div>
                        </div>
                        \${downloadTagsHtml}
                    </div>
                `;
TEXT;

$replace1 = <<< 'TEXT'
                
                cm360Html = `
                    <div class="detail-section">
                        <h4>• Campaign Manager 360</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Campaign ID</div>
                                <div class="detail-value">\${s.cm360_campaign_id || '—'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Subido</div>
                                <div class="detail-value">\${pushedDate}</div>
                            </div>
                        </div>
                    </div>
                `;
TEXT;

$content = str_replace($search1, $replace1, $content);

// Ensure downloadTagsBtn is declared early
$search2 = "let cm360Html = '';";
$replace2 = "let cm360Html = '';\n            let downloadTagsBtn = '';";
$content = str_replace($search2, $replace2, $content);

// Populate downloadTagsBtn
$search3 = "const pushedDate = s.cm360_pushed_at ? new Date(s.cm360_pushed_at).toLocaleString('es-ES') : '—';";
$replace3 = <<< 'TEXT'
                const pushedDate = s.cm360_pushed_at ? new Date(s.cm360_pushed_at).toLocaleString('es-ES') : '—';
                
                if (s.cm360_tags) {
                    const tagsDataStr = typeof s.cm360_tags === 'string' ? escapeAttr(s.cm360_tags) : escapeAttr(JSON.stringify(s.cm360_tags));
                    downloadTagsBtn = `
                        <button class="btn btn-secondary" onclick="downloadTagsFromDashboard(this)" data-tags="\${tagsDataStr}" data-market="\${escapeAttr(s.market)}" data-client="\${escapeAttr(s.client_name || '')}" data-campaign="\${escapeAttr(s.campaign_name)}" style="font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--wpp-teal); color: #fff; border: none; font-weight: 600; flex: 1;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Descargar Tags CM360
                        </button>
                    `;
                }
TEXT;
$content = str_replace($search3, $replace3, $content);


// 2. Modify editBtnHtml and create accionesBlockHtml
$search4 = <<< 'TEXT'
                        const editBtnHtml = `
                <div style="margin-bottom: 20px; display: flex; justify-content: flex-start; align-items: center; gap: 15px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 16px;">
                    <button onclick="openEditQuestionsModal(\${s.id})" style="background-color: var(--wpp-cyan); color: var(--wpp-navy); border: none; padding: 8px 16px; width: auto; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        Editar Preguntas
                    </button>
                </div>
            `;
TEXT;

$replace4 = <<< 'TEXT'
            const editBtnHtml = `
                <button onclick="openEditQuestionsModal(\${s.id})" style="background-color: var(--wpp-cyan); color: var(--wpp-navy); border: none; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease; flex: 1;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    Editar Preguntas
                </button>
            `;
            
            const accionesBlockHtml = `
                <div class="detail-section">
                    <h4>• Acciones</h4>
                    <div style="display: flex; gap: 10px; width: 100%;">
                        \${editBtnHtml}
                        \${downloadTagsBtn}
                    </div>
                </div>
            `;
TEXT;
$content = str_replace($search4, $replace4, $content);

// 3. Update body.innerHTML to remove editBtnHtml from top and put accionesBlockHtml before previewHtml
$search5 = <<< 'TEXT'
            body.innerHTML = `
                ${editBtnHtml}
                ${clickActionHtmlTop}
TEXT;
$replace5 = <<< 'TEXT'
            body.innerHTML = `
                ${clickActionHtmlTop}
TEXT;
$content = str_replace($search5, $replace5, $content);

$search6 = <<< 'TEXT'
                ${previewHtml}
            `;
TEXT;
$replace6 = <<< 'TEXT'
                ${accionesBlockHtml}
                ${previewHtml}
            `;
TEXT;
$content = str_replace($search6, $replace6, $content);

file_put_contents('resources/views/dashboard.blade.php', $content);
echo "Patch applied!";
?>
