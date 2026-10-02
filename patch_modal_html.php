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

// Insert BEFORE edit-questions-modal
$content = str_replace('<div id="edit-questions-modal" class="modal-overlay', $modalHtml."\n\n".'<div id="edit-questions-modal" class="modal-overlay', $content);

file_put_contents('resources/views/dashboard.blade.php', $content);
echo "Modal HTML patch applied.\n";
