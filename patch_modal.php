<?php
$content = file_get_contents('resources/views/dashboard.blade.php');

$modalHtml = <<< 'TEXT'
<!-- History Modal -->
<div class="modal-overlay" id="history-modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Historial de Edición</h3>
            <button class="modal-close" onclick="closeHistoryModal()">×</button>
        </div>
        <div class="modal-body" id="history-modal-body" style="max-height: 400px; overflow-y: auto;">
            <div style="text-align:center;padding:40px;"><p style="color:var(--text-muted)">Cargando...</p></div>
        </div>
    </div>
</div>
TEXT;

// Insert after edit-questions-modal
$content = str_replace('<div class="modal-overlay" id="edit-questions-modal">', $modalHtml . "\n\n" . '<div class="modal-overlay" id="edit-questions-modal">', $content);

$jsCode = <<< 'TEXT'

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

TEXT;

// Insert JS
$content = str_replace('// ===== DETAIL MODAL =====', $jsCode . "\n    // ===== DETAIL MODAL =====", $content);

file_put_contents('resources/views/dashboard.blade.php', $content);
echo "Modal patch applied.\n";
?>
