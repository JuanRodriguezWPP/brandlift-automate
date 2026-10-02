<?php

$content = file_get_contents('resources/views/dashboard.blade.php');

// 1. Inject the button
$editBtnHtml = <<< 'HTML'
            const editBtnHtml = `
                <div style="margin-bottom: 20px; display: flex; justify-content: flex-start; align-items: center; gap: 15px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 16px;">
                    <button onclick="openEditQuestionsModal(${s.id})" style="background-color: var(--wpp-cyan); color: var(--wpp-navy); border: none; padding: 8px 16px; width: auto; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        Editar Preguntas
                    </button>
                </div>
            `;
HTML;

$content = str_replace("let clickActionHtmlTop = '';", $editBtnHtml."\n            let clickActionHtmlTop = '';", $content);
$content = str_replace('${clickActionHtmlTop}', "\${editBtnHtml}\n                \${clickActionHtmlTop}", $content);

// 2. Add Modal and Script at the end
$modalAndScript = <<< 'HTML'
<!-- Edit Questions Modal -->
<div id="edit-questions-modal" class="modal-overlay hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
    <div style="background: var(--bg-card); width: 600px; max-width: 90%; max-height: 90vh; overflow-y: auto; border-radius: var(--radius-md); border: 1px solid var(--border-card); padding: 25px; position: relative;">
        <button onclick="closeEditQuestionsModal()" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: var(--text-secondary); cursor: pointer;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        <h3 style="color: var(--text-primary); margin-top: 0; margin-bottom: 20px;">Editar Preguntas</h3>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-secondary); font-size: 13px; font-weight: 600; margin-bottom: 8px;">Tema de Color</label>
            <div style="display: flex; gap: 10px;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: var(--text-primary);">
                    <input type="radio" name="edit-theme" value="dark" checked> Oscuro
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: var(--text-primary);">
                    <input type="radio" name="edit-theme" value="light"> Claro
                </label>
            </div>
        </div>

        <div id="edit-questions-container" style="display: flex; flex-direction: column; gap: 20px;"></div>
        
        <div style="margin-top: 20px; display: flex; justify-content: space-between;">
            <button type="button" class="btn btn-secondary" onclick="addQuestionToEditModal()" style="font-size: 13px;">+ Agregar Pregunta (Max 4)</button>
            <button type="button" class="btn btn-primary" onclick="saveEditedQuestions()" style="font-size: 13px;">Actualizar CM360</button>
        </div>
    </div>
</div>

<script>
let editingStudy = null;

function openEditQuestionsModal(studyId) {
    editingStudy = allStudies.find(s => s.id === studyId);
    if (!editingStudy) return;

    // Set theme
    const theme = editingStudy.theme_colors || 'dark';
    document.querySelector(`input[name="edit-theme"][value="${theme}"]`).checked = true;

    // Render questions
    renderEditQuestions();

    const modal = document.getElementById('edit-questions-modal');
    modal.classList.remove('hidden');
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
}

function closeEditQuestionsModal() {
    const modal = document.getElementById('edit-questions-modal');
    modal.style.opacity = '0';
    modal.style.pointerEvents = 'none';
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function renderEditQuestions() {
    const container = document.getElementById('edit-questions-container');
    container.innerHTML = '';

    editingStudy.questions.forEach((q, idx) => {
        const qHtml = `
            <div class="question-block" data-idx="${idx}" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <label style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Pregunta ${idx + 1}</label>
                    ${idx > 0 ? `<button type="button" onclick="removeEditQuestion(${idx})" style="background: transparent; border: none; color: #f87171; cursor: pointer; font-size: 12px;">Eliminar</button>` : ''}
                </div>
                <input type="text" class="form-input edit-q-text" value="${escapeAttr(q.question_text)}" placeholder="Escribe la pregunta" style="width: 100%; margin-bottom: 15px;" required>
                
                <label style="color: var(--text-secondary); font-size: 12px; margin-bottom: 8px; display: block;">Respuestas</label>
                <div class="edit-answers-container" style="display: flex; flex-direction: column; gap: 8px;">
                    ${q.answers.map((ans, aIdx) => `
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span style="color: var(--text-secondary); font-size: 12px;">${aIdx + 1}.</span>
                            <input type="text" class="form-input edit-a-text" value="${escapeAttr(ans)}" style="width: 100%;" required>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', qHtml);
    });
}

function addQuestionToEditModal() {
    if (editingStudy.questions.length >= 4) {
        alert("Máximo 4 preguntas permitidas.");
        return;
    }
    editingStudy.questions.push({
        question_text: "",
        answers: ["", ""]
    });
    renderEditQuestions();
}

function removeEditQuestion(idx) {
    editingStudy.questions.splice(idx, 1);
    renderEditQuestions();
}

// Generate creative HTML (copied and adapted from brandlift-form)
function generateCreativeHTML(questionsData, w, h, campaign, market, groupName, tagType, theme = 'dark') {
    const isDark = theme === 'dark';
    const bgStyle = isDark
        ? 'background:linear-gradient(160deg,#0a1628 0%,#0d2b5e 35%,#1a4a8a 50%,#0d2b5e 65%,#0a1628 100%);'
        : 'background:linear-gradient(160deg,#f8fafc 0%,#e2e8f0 35%,#cbd5e1 50%,#e2e8f0 65%,#f8fafc 100%);';

    const textStyle = isDark ? 'color:#ffffff;' : 'color:#000050;';
    const btnBg = isDark ? '#ffffff' : '#000050';
    const glow = isDark ? 'rgba(30,100,200,0.25)' : 'rgba(255,255,255,0.6)';

    let html = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Brandlift Survey</title>
<style>
 .screen { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 20px 50px 20px; box-sizing: border-box; z-index: 1; transition: opacity 0.4s ease, transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
 .screen.slow-transition { transition: opacity 1.4s ease, transform 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
 .screen.hidden { opacity: 0; transform: scale(0.85); pointer-events: none; }
 .screen.active { opacity: 1; transform: scale(1); pointer-events: auto; }
 .btn-anim { transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.2s ease; }
 .btn-anim:hover { transform: scale(1.05); opacity: 0.9 !important; }
 .btn-anim:active { transform: scale(0.95); }
</style>
<script>
 var webhookUrl = ""; 
 var surveyData = {};
 
 window.onload = function() {
   document.getElementById('screen-q1').classList.add('slow-transition');
   setTimeout(function() { showScreen('screen-q1'); }, 150);
 };

 function showScreen(id) {
   var screens = document.getElementsByClassName('screen');
   for (var i = 0; i < screens.length; i++) {
     screens[i].classList.remove('slow-transition');
     screens[i].classList.remove('active');
     screens[i].classList.add('hidden');
   }
   if (document.getElementById(id)) {
     document.getElementById(id).classList.remove('hidden');
     document.getElementById(id).classList.add('active');
     if (id === 'screen-thanks' && window.parent) {
         window.parent.postMessage('brandlift_finished', '*');
     }
   }
 }

 function storeAnswer(qNum, qText, aText) {
   surveyData['q' + qNum] = qText;
   surveyData['a' + qNum] = aText;
   surveyData['campaign'] = "${campaign}";
   surveyData['market'] = "${market}";
   surveyData['group'] = "${groupName}";
   surveyData['tagType'] = "${tagType}";
   surveyData['userId'] = Date.now().toString() + Math.floor(Math.random() * 1000).toString();
 }

 function submitAnswers() {
   if (webhookUrl && webhookUrl.trim() !== "") {
     fetch(webhookUrl, {
       method: 'POST',
       headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
       body: new URLSearchParams(surveyData).toString()
     }).catch(function(e) { console.error(e); });
   }
 }
<\/script>
</head>
<body style="margin:0;padding:0;overflow:hidden;">
<div style="width:${w}px;height:${h}px;${bgStyle}position:relative;overflow:hidden;font-family:Arial,Helvetica,sans-serif;box-sizing:border-box;">
 <div style="position:absolute;width:200%;height:200%;top:-80%;left:-50%;background:radial-gradient(ellipse at center,${glow} 0%,transparent 60%);pointer-events:none;"></div>`;

    questionsData.forEach((q, idx) => {
        const qNum = idx + 1;
        const nextScreen = qNum < questionsData.length ? `screen-q${qNum+1}` : `screen-thanks`;
        const display = qNum === 1 ? 'active' : 'hidden';

        const ansCount = q.answers.length;
        let qFontSize, btnFontSize, btnPadding, btnGap, qMarginBottom, btnMaxWidth;
        if (ansCount <= 3) {
            qFontSize = 18; btnFontSize = 14; btnPadding = '10px 28px'; btnGap = 8; qMarginBottom = 28; btnMaxWidth = 260;
        } else if (ansCount === 4) {
            qFontSize = 16; btnFontSize = 13; btnPadding = '9px 24px'; btnGap = 7; qMarginBottom = 20; btnMaxWidth = 250;
        } else if (ansCount === 5) {
            qFontSize = 14; btnFontSize = 12; btnPadding = '8px 20px'; btnGap = 6; qMarginBottom = 16; btnMaxWidth = 240;
        } else {
            qFontSize = 12; btnFontSize = 11; btnPadding = '6px 16px'; btnGap = 5; qMarginBottom = 12; btnMaxWidth = 230;
        }

        html += `
 <div id="screen-q${qNum}" class="screen ${display}">
  <div style="${textStyle}font-size:${qFontSize}px;font-weight:800;text-align:center;line-height:1.35;margin-bottom:${qMarginBottom}px;padding:0 10px;max-width:90%;">${q.question}</div>
  <div style="display:flex;flex-direction:column;align-items:center;gap:${btnGap}px;width:100%;">`;

        q.answers.forEach(a => {
            html += `
   <div onclick="storeAnswer('${qNum}', '${q.question}', '${a}'); setTimeout(function(){ showScreen('${nextScreen}'); ${nextScreen === 'screen-thanks' ? 'submitAnswers();' : ''} }, 300);" class="btn-anim" style="background:${btnBg};border-radius:100px;padding:${btnPadding};text-align:center;cursor:pointer;font-family:Arial,Helvetica,sans-serif;font-size:${btnFontSize}px;font-weight:700;color:${theme==='dark'?'#000050':'#ffffff'};letter-spacing:0.3px;width:80%;max-width:${btnMaxWidth}px;box-sizing:border-box;box-shadow:0 4px 10px rgba(0,0,0,0.15);">${a}</div>`;
        });

        html += `
  </div>
 </div>`;
    });

    html += `
 <div id="screen-thanks" class="screen hidden">
  <div style="${textStyle}font-size:22px;font-weight:800;text-align:center;line-height:1.4;margin-bottom:15px;text-shadow:0 2px 4px rgba(0,0,0,0.3);">¡Muchas gracias<br>por su opinión!</div>
 </div>
 <div style="position:absolute;bottom:10px;right:14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:${isDark?'rgba(255,255,255,0.6)':'rgba(0,0,80,0.6)'};letter-spacing:0.5px;z-index:2;"><span style="font-weight:800;">WPP</span><span style="font-weight:400;"> Media</span></div>
</div>
</body>
</html>`;
    return html;
}

async function saveEditedQuestions() {
    // Validate and gather new data
    const theme = document.querySelector('input[name="edit-theme"]:checked').value;
    const blocks = document.querySelectorAll('.question-block');
    const newQuestions = [];
    
    for (const block of blocks) {
        const qText = block.querySelector('.edit-q-text').value.trim();
        const aTexts = Array.from(block.querySelectorAll('.edit-a-text')).map(i => i.value.trim()).filter(v => v);
        
        if (!qText || aTexts.length < 2) {
            alert('Asegúrate de llenar todas las preguntas y al menos 2 respuestas por pregunta.');
            return;
        }
        newQuestions.push({ question: qText, answers: aTexts });
    }

    if (!editingStudy.creatives || editingStudy.creatives.length === 0) {
        alert("Esta campaña no tiene creativos guardados para editar.");
        return;
    }

    const btn = document.querySelector('#edit-questions-modal .btn-primary');
    const oldBtnText = btn.innerText;
    btn.innerText = "Actualizando...";
    btn.disabled = true;

    // Generate new HTML for each creative
    const updatedCreatives = [];
    editingStudy.creatives.forEach(c => {
        // Extract groupName and tagType from existing HTML if possible
        const oldHtml = c.creative_html || '';
        const groupMatch = oldHtml.match(/surveyData\['group'\]\s*=\s*"([^"]*)"/);
        const tagTypeMatch = oldHtml.match(/surveyData\['tagType'\]\s*=\s*"([^"]*)"/);
        const groupName = groupMatch ? groupMatch[1] : '';
        const tagType = tagTypeMatch ? tagTypeMatch[1] : 'Ad_Exposed';

        // Assuming question_number is always 1 for the whole creative bundle (as generated initially)
        const newHtml = generateCreativeHTML(
            newQuestions,
            editingStudy.creative_width || 300,
            editingStudy.creative_height || 250,
            editingStudy.campaign_name,
            editingStudy.market,
            groupName,
            tagType,
            theme
        );

        updatedCreatives.push({
            question_number: c.question_number,
            variant_key: c.variant_key,
            html: newHtml
        });
    });

    try {
        const res = await fetch('/api/brandlift/update-creatives', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                study_id: editingStudy.id,
                creatives: updatedCreatives,
                questions: newQuestions.map(q => ({ text: q.question, answers: q.answers }))
            })
        });

        const data = await res.json();
        if (data.success) {
            alert('¡Preguntas actualizadas exitosamente en CM360 sin afectar tus tags!');
            window.location.reload();
        } else {
            alert('Error al actualizar: ' + JSON.stringify(data));
            btn.innerText = oldBtnText;
            btn.disabled = false;
        }
    } catch (e) {
        console.error(e);
        alert('Error de conexión');
        btn.innerText = oldBtnText;
        btn.disabled = false;
    }
}
</script>
HTML;

$content = str_replace('</body>', $modalAndScript."\n</body>", $content);

file_put_contents('resources/views/dashboard.blade.php', $content);
echo 'Done appending modal';
