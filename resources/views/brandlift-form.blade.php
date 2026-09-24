<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Brandlift Creator — WPP Media</title>
    <meta name="description" content="Herramienta para crear creativos de Brandlift automáticos para Campaign Manager 360">
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <style>
        /* ===== PAGE-SPECIFIC VARIABLE ALIASES (backward compat) ===== */
        :root {
            --bg-card-border: var(--border-card);
            --accent-blue: var(--wpp-lime);
            --accent-blue-light: var(--wpp-cyan);
            --accent-cyan: var(--wpp-cyan);
            --accent-gradient: var(--gradient-lime-cyan);
            --success-green: var(--wpp-lime);
            --danger-red: var(--color-error);
            --warning-amber: #f59e0b;
        }

        body {
            background-color: var(--bg-secondary);
        }

        /* ===== ANIMATED BACKGROUND ===== */
        .bg-animation {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-animation .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            animation: orbFloat 20s ease-in-out infinite;
        }

        .bg-animation .orb:nth-child(1) {
            width: 600px; height: 600px;
            background: var(--wpp-lime);
            top: -15%; left: -10%;
        }

        .bg-animation .orb:nth-child(2) {
            width: 500px; height: 500px;
            background: var(--wpp-cyan);
            bottom: -20%; right: -10%;
            animation-delay: -7s;
        }

        .bg-animation .orb:nth-child(3) {
            width: 400px;
            height: 400px;
            background: var(--wpp-lime);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -14s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -40px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(40px, 30px) scale(1.02); }
        }




        /* ===== HEADER ===== */
        .app-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .app-header .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 100px;
            background: rgba(176, 244, 103, 0.1);
            border: 1px solid rgba(176, 244, 103, 0.2);
            color: var(--accent-blue-light);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .app-header .badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--success-green);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .app-header h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--wpp-navy);
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .app-header p {
            color: var(--text-muted);
            font-size: 15px;
        }

        /* ===== MAIN GRID ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: start;
        }
        .main-grid > div {
            min-width: 0;
        }

        @media (max-width: 1024px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        /* ===== CARD ===== */
        .card {
            background: #ffffff;
            border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-lg);
            padding: 32px;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-card);
            transition: box-shadow var(--transition-med);
        }

        .card:hover {
            box-shadow: var(--shadow-card), var(--shadow-glow);
        }

        .card + .card { margin-top: 24px; }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(176, 244, 103, 0.08);
        }

        .card-header .icon-wrapper {
            width: 44px; height: 44px;
            border-radius: var(--radius-md);
            background: rgba(176, 244, 103, 0.2);
            color: var(--wpp-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
        }

        .card-header h2 small {
            display: block;
            font-size: 13px;
            font-weight: 400;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ===== STEPPER ===== */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 32px;
            padding: 0 8px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all var(--transition-med);
        }

        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            border: 2px solid rgba(176, 244, 103, 0.2);
            background: transparent;
            color: var(--text-muted);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
        }

        .step-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: color var(--transition-med);
            white-space: nowrap;
        }

        .step-line {
            width: 48px;
            height: 2px;
            background: rgba(176, 244, 103, 0.12);
            margin: 0 12px;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .step-line::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--wpp-lime);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
        }

        .step-line.filled::after {
            transform: scaleX(1);
        }

        .step-item.active .step-circle {
            border-color: var(--wpp-lime);
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(176, 244, 103, 0.35);
        }

        .step-item.active .step-label {
            color: var(--wpp-navy);
        }

        .step-item.completed .step-circle {
            border-color: var(--wpp-navy);
            background: rgba(0, 0, 80, 0.05);
            color: var(--wpp-navy);
        }

        .step-item.completed .step-label {
            color: var(--text-secondary);
        }

        /* ===== WIZARD STEPS ===== */
        .wizard-viewport {
            overflow: hidden;
            position: relative;
            transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .wizard-track {
            display: flex;
            align-items: flex-start;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        @media (min-width: 768px) {
            .questions-grid { grid-template-columns: 1fr 1fr !important; }
        }

        .wizard-step {
            min-width: 100%;
            opacity: 0;
            transform: scale(0.96);
            transition: opacity 0.4s ease, transform 0.4s ease;
            pointer-events: none;
        }

        .wizard-step.active {
            opacity: 1;
            transform: scale(1);
            pointer-events: all;
        }

        /* ===== QUESTION BLOCK ===== */
        .question-block {
            background: transparent;
            border: 1px solid rgba(176, 244, 103, 0.08);
            border-radius: var(--radius-md);
            padding: 24px;
            transition: border-color var(--transition-fast);
        }

        .question-block:hover {
            border-color: rgba(176, 244, 103, 0.18);
        }

        .question-block .q-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .q-label-blue {
            background: rgba(176, 244, 103, 0.2);
            color: var(--wpp-navy);
            border: 1px solid rgba(176, 244, 103, 0.5);
        }

        .q-label-cyan {
            background: rgba(147, 223, 227, 0.2);
            color: var(--wpp-navy);
            border: 1px solid rgba(147, 223, 227, 0.5);
        }

        /* ===== STEP NAVIGATION ===== */
        .step-nav {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-next {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--wpp-navy);
            color: var(--wpp-lime);
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.2);
            transition: all var(--transition-fast);
        }

        .btn-next:hover:not(:disabled) {
            box-shadow: 0 6px 24px rgba(176, 244, 103, 0.45);
            transform: translateY(-1px);
        }

        .btn-next:disabled {
            opacity: 0.35;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-next .ripple {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }

        .btn-next:disabled .ripple {
            display: none;
        }

        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }

        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            background: rgba(176, 244, 103, 0.08);
            border: 1px solid rgba(176, 244, 103, 0.15);
            color: var(--text-secondary);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-back:hover {
            background: rgba(176, 244, 103, 0.15);
            color: var(--text-primary);
            border-color: rgba(176, 244, 103, 0.3);
        }

        /* ===== COMPLETION CHECK ===== */
        .field-status {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px; height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateY(-50%) scale(0.5);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .field-status.show {
            opacity: 1;
            transform: translateY(-50%) scale(1);
        }

        .field-status.valid {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-green);
        }

        /* ===== AUTO-ADVANCE INDICATOR ===== */
        .auto-advance-bar {
            height: 3px;
            background: rgba(176, 244, 103, 0.1);
            border-radius: 3px;
            margin-top: 20px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .auto-advance-bar.active {
            opacity: 1;
        }

        .auto-advance-bar .progress {
            height: 100%;
            background: var(--wpp-lime);
            border-radius: 3px;
            width: 0%;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auto-advance-bar.active .progress {
            width: 100%;
        }

        .auto-advance-text {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .auto-advance-text.show {
            opacity: 1;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group:last-child { margin-bottom: 0; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .form-group label .required {
            color: var(--danger-red);
            margin-left: 2px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            transition: all var(--transition-fast);
            outline: none;
        }

        .form-textarea { resize: vertical; min-height: 72px; }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--border-input-focus);
            box-shadow: 0 0 0 3px rgba(176, 244, 103, 0.1);
        }

        .form-input.valid, .form-textarea.valid {
            border-color: rgba(16, 185, 129, 0.4);
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: var(--text-muted);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12' fill='none'%3E%3Cpath d='M3 4.5L6 7.5L9 4.5' stroke='%2364748b' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
        }

        /* ===== ANSWERS ===== */
        .answers-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .answer-item {
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out forwards;
            opacity: 0;
            transform: translateY(8px);
        }

        @keyframes slideIn {
            to { opacity: 1; transform: translateY(0); }
        }

        .answer-item .answer-number {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: rgba(176, 244, 103, 0.12);
            border: 1px solid rgba(176, 244, 103, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--accent-blue-light);
            flex-shrink: 0;
        }

        .answer-item .form-input { flex: 1; }

        /* ===== SIZE SELECTOR ===== */
        .size-options {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .size-option {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            color: var(--text-secondary);
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .size-option:hover {
            border-color: rgba(176, 244, 103, 0.4);
            color: var(--text-primary);
        }

        .size-option.active {
            background: rgba(176, 244, 103, 0.15);
            border-color: var(--accent-blue);
            color: var(--accent-blue-light);
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all var(--transition-fast);
        }

        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--wpp-navy);
            color: var(--wpp-lime);
            width: 100%;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            border-radius: var(--radius-md);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.2);
            transition: all var(--transition-fast);
        }

        .btn-primary:hover { 
            background: #000070; 
            box-shadow: 0 6px 16px rgba(0, 0, 80, 0.3); 
            transform: translateY(-1px); 
        }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

        .btn-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--wpp-navy);
            flex: 1;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            color: var(--wpp-navy);
        }

        .btn-cm360 {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: var(--wpp-navy);
            width: 100%;
            padding: 14px 24px;
            font-size: 15px;
            border-radius: var(--radius-md);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
            margin-top: 12px;
        }

        .btn-cm360:hover { box-shadow: 0 6px 24px rgba(245, 158, 11, 0.45); transform: translateY(-1px); }
        .btn-cm360:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

        .btn-group { display: flex; gap: 12px; margin-top: 12px; }

        /* ===== PREVIEW ===== */
        .preview-wrapper { position: sticky; top: 24px; }

        .preview-tabs {
            display: flex; gap: 4px;
            margin-bottom: 16px;
            background: #e2e8f0;
            border-radius: var(--radius-sm);
            padding: 4px;
        }

        .preview-tab {
            flex: 1;
            padding: 10px 16px;
            border-radius: 6px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .preview-tab:hover { color: var(--text-secondary); }
        .preview-tab.active { background: rgba(176, 244, 103, 0.15); color: var(--accent-blue-light); }

        .preview-frame {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            background: transparent;
            border-radius: var(--radius-md);
            border: 1px dashed rgba(176, 244, 103, 0.15);
            min-height: 320px;
            margin-bottom: 16px;
            transition: all var(--transition-med);
        }

        .preview-frame.active {
            border-style: solid;
            border-color: rgba(176, 244, 103, 0.25);
            background: transparent;
        }

        .preview-placeholder { text-align: center; color: var(--text-muted); }
        .preview-placeholder .icon { font-size: 48px; margin-bottom: 12px; opacity: 0.3; }
        .preview-placeholder p { font-size: 14px; line-height: 1.6; }

        .preview-content { display: none; }
        .preview-content.visible { display: block; animation: fadeIn 0.5s ease-out; }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* ===== HTML OUTPUT ===== */
        .html-output-section { margin-top: 16px; display: none; }
        .html-output-section.visible { display: block; animation: slideIn 0.3s ease-out forwards; opacity: 0; }

        .html-output-label {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;
        }
        .html-output-label span { font-size: 13px; font-weight: 600; color: var(--text-secondary); }

        .html-output {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(176, 244, 103, 0.1);
            border-radius: var(--radius-sm);
            padding: 16px;
            max-height: 180px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.6;
            color: var(--accent-blue-light);
            white-space: pre-wrap;
            word-break: break-all;
            scrollbar-width: thin;
            scrollbar-color: rgba(176, 244, 103, 0.2) transparent;
        }

        /* ===== CM360 ===== */
        .cm360-config {
            background: rgba(245, 158, 11, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: var(--radius-md);
            padding: 24px;
            margin-top: 20px;
        }

        .cm360-config .config-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }

        .cm360-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 100px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #fbbf24; font-size: 11px; font-weight: 700;
        }

        .cm360-config h3 { font-size: 15px; font-weight: 700; }

        .cm360-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }

        /* ===== STATUS / TOAST / SPINNER ===== */
        .status-bar {
            display: none; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: var(--radius-sm);
            margin-top: 12px; font-size: 13px; font-weight: 500;
        }
        .status-bar.visible { display: flex; animation: slideIn 0.3s ease-out forwards; opacity: 0; }
        .status-bar.loading { background: rgba(176,244,103,0.1); border: 1px solid rgba(176,244,103,0.2); color: var(--wpp-navy); }
        .status-bar.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #6ee7b7; }
        .status-bar.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; }

        .spinner {
            width: 16px; height: 16px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .divider { height: 1px; background: rgba(59,130,246,0.08); margin: 24px 0; }

        .toast {
            position: fixed; bottom: 32px; right: 32px;
            padding: 14px 24px; border-radius: var(--radius-md);
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            color: #6ee7b7; font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
            z-index: 1000; transform: translateY(20px); opacity: 0;
            transition: all 0.3s ease-out; backdrop-filter: blur(12px);
        }
        .toast.show { transform: translateY(0); opacity: 1; }

        @media (max-width: 640px) {
            .app-container { padding: 24px 16px; }
            .card { padding: 24px; }
            .app-header h1 { font-size: 28px; }
            .btn-group { flex-direction: column; }
            .cm360-grid { grid-template-columns: 1fr; }
            .stepper { flex-wrap: wrap; gap: 8px; }
            .step-line { width: 24px; margin: 0 4px; }
        }

        /* ===== GROUPS & TAG TYPES ===== */
        .tag-type-options {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .tag-type-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            user-select: none;
        }

        .tag-type-option:hover {
            background: rgba(0, 0, 80, 0.05);
        }

        .tag-type-option input[type="checkbox"] {
            accent-color: var(--accent-blue);
            width: 16px;
            height: 16px;
        }

        .tag-type-option label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
        }

        .groups-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }

        .group-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            border-radius: var(--radius-sm);
            animation: fadeSlideIn 0.3s ease;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .group-item .group-number {
            font-size: 11px;
            font-weight: 700;
            color: var(--wpp-navy);
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .group-item input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            font-weight: 500;
        }

        .group-item input::placeholder {
            color: var(--text-muted);
        }

        .group-item .btn-remove-group {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger-red);
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all var(--transition-fast);
        }

        .group-item .btn-remove-group:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .btn-add-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--bg-input);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-sm);
            color: var(--wpp-navy);
            font-family: 'WPP', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            margin-top: 4px;
        }

        .btn-add-group:hover {
            background: rgba(0, 0, 80, 0.05);
            border-color: var(--border-input);
        }

        .variant-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(0, 0, 80, 0.05);
            border: 1px solid var(--border-input);
            border-radius: var(--radius-sm);
            color: var(--wpp-navy);
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        /* ===== PREVIEW VARIANT SELECTOR ===== */
        .variant-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }

        .variant-btn {
            padding: 6px 12px;
            background: rgba(176, 244, 103, 0.08);
            border: 1px solid rgba(176, 244, 103, 0.15);
            border-radius: 20px;
            color: var(--text-secondary);
            font-family: 'WPP', sans-serif;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .variant-btn:hover {
            background: rgba(176, 244, 103, 0.15);
            color: var(--text-primary);
        }

        .variant-btn.active {
            background: var(--wpp-navy);
            border-color: transparent;
            color: #fff;
        }

        @media (max-width: 768px) {
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">WPP MEDIA SOLUTIONS<br><span style="color: var(--wpp-lime); font-size: 14px; font-weight: normal; margin-top: 4px; display: inline-block;">| Creative Services LATAM</span></div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Menu</span>
                <a href="/dashboard" class="sidebar-link">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="/brandlift" class="sidebar-link active sidebar-link-cta">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Crear Tags
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="/users" class="sidebar-link">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Usuarios
                </a>
                @endif
            </div>

            <div class="sidebar-spacer"></div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">General</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link sidebar-link-danger">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <h1 class="top-header-title">Crear Creativo Brandlift</h1>
                <div class="top-header-user">
                    <div class="top-header-user-info">
                        <div class="top-header-user-name">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="top-header-user-email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="top-header-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                </div>
            </header>

            <div class="content-area">

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- ==================== LEFT: FORM ==================== -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h2>Configurar Brandlift <small>Sigue los pasos para crear tus creativos</small></h2>
                    </div>

                    <!-- Stepper -->
                    <div class="stepper">
                        <div class="step-item active" data-step="1">
                            <div class="step-circle">1</div>
                            <span class="step-label">Paso 1: Digitar información</span>
                        </div>
                        <div class="step-line" id="step-line-1"></div>
                        
                        <div class="step-item" data-step="2" id="step-item-2">
                            <div class="step-circle">2</div>
                            <span class="step-label">Paso 2: Digitar preguntas</span>
                        </div>

                    </div>

                    <!-- Wizard Viewport -->
                    <div class="wizard-viewport">
                        <div class="wizard-track" id="wizard-track">

                            <!-- ===== STEP 1: Question 1 ===== -->
                            <div class="wizard-step active" id="step-1">
                                <div class="question-block">
                                    <div class="q-label q-label-blue">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        Configuración Inicial
                                    </div>



                                    @php
                                        $isMercado = auth()->user()->role === 'mercado';
                                        $userMarket = auth()->user()->market;
                                    @endphp
                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2); {{ $isMercado ? 'display: none;' : '' }}">
                                        <label for="bl-market-step1">Mercado <span class="required">*</span></label>
                                        <select id="bl-market-step1" class="form-select" required {{ $isMercado ? 'disabled' : '' }}>
                                            <option value="" data-country="">Selecciona un mercado...</option>
                                            <option value="PE" data-country="Peru" {{ ($isMercado && $userMarket === 'PE') ? 'selected' : '' }}>Perú (PE)</option>
                                            <option value="PRI" data-country="Puerto Rico" {{ ($isMercado && $userMarket === 'PRI') ? 'selected' : '' }}>Puerto Rico (PRI)</option>
                                            <option value="ARG" data-country="Argentina" {{ ($isMercado && $userMarket === 'ARG') ? 'selected' : '' }}>Argentina (ARG)</option>
                                            <option value="MIA" data-country="Miami" {{ ($isMercado && $userMarket === 'MIA') ? 'selected' : '' }}>Miami (MIA)</option>
                                            <option value="MEX" data-country="Mexico" {{ ($isMercado && $userMarket === 'MEX') ? 'selected' : '' }}>México (MEX)</option>
                                            <option value="CHL" data-country="Chile" {{ ($isMercado && $userMarket === 'CHL') ? 'selected' : '' }}>Chile (CHL)</option>
                                            <option value="COL" data-country="Colombia" {{ ($isMercado && $userMarket === 'COL') ? 'selected' : '' }}>Colombia (COL)</option>
                                            <option value="ECU" data-country="Ecuador" {{ ($isMercado && $userMarket === 'ECU') ? 'selected' : '' }}>Ecuador (ECU)</option>
                                        </select>
                                    </div>

                                    <!-- CM360 Integration Fields -->
                                    <div class="form-group" style="display:none;">
                                        <label for="cm360-profile-id">Profile (Agencia/Red) <span id="profile-loader" style="font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-profile-id" class="form-select">
                                            <option value="">Cargando perfiles...</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="cm360-advertiser-id">Anunciante (Advertiser CM360) <span class="required">*</span> <span id="advertiser-loader" style="display:none; font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-advertiser-id" class="form-select" disabled>
                                            <option value="">Selecciona un mercado primero...</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: none; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="cm360-site-id">Medio (Site CM360) <span class="required">*</span> <span id="site-loader" style="display:none; font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-site-id" class="form-select" disabled>
                                            <option value="">Selecciona un mercado primero...</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: none; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-client-step1">Cliente <span class="required">*</span></label>
                                        <input type="text" id="bl-client-step1" class="form-control" placeholder="Se autocompleta con el anunciante">
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-investment-step1">Inversión / Bonificado <span class="required">*</span></label>
                                        <div style="position: relative; display: flex; align-items: center;">
                                            <span style="position: absolute; left: 16px; color: var(--text-muted); font-weight: 600; font-size: 15px;">$</span>
                                            <input type="number" id="bl-investment-step1" class="form-input" style="padding-left: 32px;" placeholder="Ej: 5000" required min="0" step="0.01">
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label>¿Para qué DSP requieres TAGS?<span class="required">*</span></label>
                                        <div class="tag-type-options" style="flex-wrap: wrap;">
                                            <div class="tag-type-option">
                                                <input type="checkbox" id="dps-dv360" value="DV360" class="dps-checkbox">
                                                <label for="dps-dv360">DV360</label>
                                            </div>
                                            <div class="tag-type-option">
                                                <input type="checkbox" id="dps-ttd" value="TTD" class="dps-checkbox">
                                                <label for="dps-ttd">TTD - The Trade Desk</label>
                                            </div>
                                            <div class="tag-type-option">
                                                <input type="checkbox" id="dps-sonata" value="Sonata" class="dps-checkbox">
                                                <label for="dps-sonata">Sonata</label>
                                            </div>
                                            <div class="tag-type-option">
                                                <input type="checkbox" id="dps-amazon" value="Amazon" class="dps-checkbox">
                                                <label for="dps-amazon">Amazon</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-campaign-name-step1">Nombre de la Campaña <span class="required">*</span></label>
                                        <input type="text" id="bl-campaign-name-step1" class="form-input" placeholder="Ej: Campaña Verano 2026" required>
                                    </div>

                                    <!-- Groups -->
                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label>Grupos de Audiencia</label>
                                        <div class="groups-container" id="groups-container">
                                            <div class="group-item" data-group-index="0">
                                                <span class="group-number">Grupo 1</span>
                                                <input type="text" class="group-name-input" value="General" placeholder="Nombre del grupo...">
                                            </div>
                                        </div>
                                        <button type="button" class="btn-add-group" id="btn-add-group">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                            Agregar Grupo
                                        </button>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label>Color del brandlift <span class="required">*</span></label>
                                        <div style="display:flex; gap:4px; align-items:center; background:var(--bg-input); padding:4px; border-radius:20px; border:1px solid rgba(0,0,0,0.05); width: fit-content;">
                                            <button type="button" id="theme-dark" style="border:none; padding:6px 16px; border-radius:16px; font-size:13px; cursor:pointer; background:var(--wpp-navy); color:white; font-weight:600; transition:all 0.2s;">Oscuro</button>
                                            <button type="button" id="theme-light" style="border:none; padding:6px 16px; border-radius:16px; font-size:13px; cursor:pointer; background:transparent; color:var(--text-secondary); font-weight:600; transition:all 0.2s;">Claro</button>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-question-count">¿Cuántas preguntas tendrá el Brandlift? <span class="required">*</span></label>
                                        <select id="bl-question-count" class="form-select">
                                            <option value="1" selected>1 Pregunta</option>
                                            <option value="2">2 Preguntas</option>
                                            <option value="3">3 Preguntas</option>
                                            <option value="4">4 Preguntas</option>
                                        </select>
                                        <div id="q-count-warning" style="display:none; margin-top:12px; padding:12px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); border-radius:6px; color:#b91c1c; font-size:13px; font-weight:600; gap:8px; align-items:flex-start;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0; margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            Seleccionar más de 2 preguntas puede generar poca recordación y uso del creativo.
                                        </div>
                                    </div>

                                </div>

                                <div class="step-nav">
                                    <button type="button" class="btn-next" id="btn-next-1" disabled>
                                        <span class="ripple"></span>
                                        <span id="btn-next-1-text">Digitar Preguntas</span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- ===== STEP 2: Preguntas ===== -->
                            <div class="wizard-step" id="step-2">
                                <div class="question-block">
                                    <div class="q-label q-label-cyan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        Preguntas
                                    </div>
                                    <div id="questions-grid" class="questions-grid" style="display: grid; gap: 24px; grid-template-columns: 1fr;">
                                        
                                        <!-- Pregunta 1 -->
                                        <div class="q-col" id="q-col-1">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 1</div>
                                            <div class="form-group">
                                                <label for="bl-question-1">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-1" class="form-textarea" placeholder="Ej: ¿Recordás haber visto un anuncio de cápsulas La Virginia en el último tiempo?" rows="2" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="bl-num-answers-1">Número de respuestas <span class="required">*</span></label>
                                                <select id="bl-num-answers-1" class="form-select" data-question="1">
                                                    <option value="2" selected>2 respuestas</option>
                                                    <option value="3">3 respuestas</option>
                                                    <option value="4">4 respuestas</option>
                                                    <option value="5">5 respuestas</option>
                                                    <option value="6">6 respuestas</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-1" class="answers-container"></div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 2 -->
                                        <div class="q-col" id="q-col-2" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 2</div>
                                            <div class="form-group">
                                                <label for="bl-question-2">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-2" class="form-textarea" placeholder="Ej: ¿Cuál de las siguientes marcas de café conocés?" rows="2" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="bl-num-answers-2">Número de respuestas <span class="required">*</span></label>
                                                <select id="bl-num-answers-2" class="form-select" data-question="2">
                                                    <option value="2" selected>2 respuestas</option>
                                                    <option value="3">3 respuestas</option>
                                                    <option value="4">4 respuestas</option>
                                                    <option value="5">5 respuestas</option>
                                                    <option value="6">6 respuestas</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-2" class="answers-container"></div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 3 -->
                                        <div class="q-col" id="q-col-3" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 3</div>
                                            <div class="form-group">
                                                <label for="bl-question-3">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-3" class="form-textarea" placeholder="Ej: ¿Qué tan probable es que recomiendes nuestra marca?" rows="2" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="bl-num-answers-3">Número de respuestas <span class="required">*</span></label>
                                                <select id="bl-num-answers-3" class="form-select" data-question="3">
                                                    <option value="2" selected>2 respuestas</option>
                                                    <option value="3">3 respuestas</option>
                                                    <option value="4">4 respuestas</option>
                                                    <option value="5">5 respuestas</option>
                                                    <option value="6">6 respuestas</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-3" class="answers-container"></div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 4 -->
                                        <div class="q-col" id="q-col-4" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 4</div>
                                            <div class="form-group">
                                                <label for="bl-question-4">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-4" class="form-textarea" placeholder="Ej: ¿Dónde viste nuestro último anuncio?" rows="2" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="bl-num-answers-4">Número de respuestas <span class="required">*</span></label>
                                                <select id="bl-num-answers-4" class="form-select" data-question="4">
                                                    <option value="2" selected>2 respuestas</option>
                                                    <option value="3">3 respuestas</option>
                                                    <option value="4">4 respuestas</option>
                                                    <option value="5">5 respuestas</option>
                                                    <option value="6">6 respuestas</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-4" class="answers-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="step-nav">
                                    <button type="button" class="btn-back" id="btn-back-2">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                        Atrás
                                    </button>
                                    <button type="button" class="btn-next" id="btn-create" style="flex:2;" disabled>
                                        <span class="ripple"></span>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                        <span id="btn-create-text">Crear Tags Brandlift</span>
                                    </button>
                                </div>

                                <!-- ===== CM360 RESULTS ===== -->
                                <div id="cm360-status" class="status-bar" style="margin-top: 20px;"></div>

                                <!-- Excel Download Only -->
                                <div id="cm360-tags-output" style="display:none; margin-top: 16px;">
                                    <div style="display:flex; justify-content:center; align-items:center;">
                                        <button type="button" id="btn-download-excel-tags" class="btn-next" style="display:none; gap:8px; align-items:center; padding:12px 24px; font-size:14px; width:100%;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Descargar Excel de Tags
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== RIGHT: PREVIEW ==================== -->
            <div class="preview-wrapper">
                <!-- Summary Widget -->
                <div class="card" id="summary-widget" style="display: none; margin-bottom: 24px;">
                    <div class="card-header" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 12px; margin-bottom: 0;">
                        <h2>Resumen <small>Tus datos actuales</small></h2>
                    </div>
                    <div id="summary-widget-content" style="padding: 16px;">
                        <!-- Generated dynamically by JS -->
                    </div>
                </div>

                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header">
                        <h2>Vista Previa <small>Previsualización de los creativos</small></h2>
                    </div>

                    <div class="preview-tabs" id="preview-tabs" style="display:none;">
                        <button type="button" class="preview-tab active" data-preview="1">Vista Previa Interactiva</button>
                    </div>

                    <div id="preview-frame" class="preview-frame">
                        <div id="preview-placeholder" class="preview-placeholder">
                            <div class="icon">🎨</div>
                            <p>Digita las preguntas y respuestas<br>para ver la preview en tiempo real</p>
                        </div>
                        <div style="text-align: center; margin-bottom: 10px;">
                            <button type="button" class="btn btn-secondary btn-restart-preview" onclick="const f=document.querySelector('#preview-content-1 iframe'); if(f){const src=f.srcdoc; f.srcdoc=''; setTimeout(()=>f.srcdoc=src,10);} this.style.display='none';" style="display: none; font-size: 12px; padding: 6px 12px; align-items: center; gap: 6px; cursor: pointer; background: transparent; border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-secondary); margin: 0 auto;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                Reiniciar
                            </button>
                        </div>
                        <div id="preview-content-1" class="preview-content"></div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="toast-message"></span>
    </div>

    <script>
    // ====================================================================
    // BRANDLIFT WIZARD — Dynamic multi-step with auto-advance
    // ====================================================================

    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);

    // ===== STATE =====
    const state = {
        currentStep: 1,
        totalSteps: 2,
        questionCount: 1,
        selectedSize: { width: 300, height: 250 },
        generatedHTML: { 1: '', 2: '' },
        generatedCreatives: [], // [{key, groupName, tagType, html}]
        activePreview: 1,
        activeVariantIndex: 0,
        creativesGenerated: false,
        autoAdvanceTimers: {},
        studyId: null,
        creativesPushed: false,
        theme: 'dark',
        groups: [{ name: 'General' }],
        tagTypes: ['Ad_Exposed', 'Control'],
        dpsSelections: [],
        totalVariants: 2
    };

    // ===== WIZARD NAVIGATION =====
    function goToStep(stepNum, animate = true) {
        if (stepNum < 1 || stepNum > state.totalSteps) return;

        const prevStep = state.currentStep;
        state.currentStep = stepNum;

        // Update track position
        const track = $('#wizard-track');
        track.style.transform = `translateX(-${(stepNum - 1) * 100}%)`;

        // Update step visibility
        $$('.wizard-step').forEach((el, i) => {
            el.classList.toggle('active', i === stepNum - 1);
        });

        // Update stepper indicators
        $$('.step-item').forEach((item, i) => {
            const step = i + 1;
            item.classList.remove('active', 'completed');
            if (step === stepNum) {
                item.classList.add('active');
            } else if (step < stepNum) {
                item.classList.add('completed');
            }
        });

        // Update connecting lines
        for (let i = 1; i < state.totalSteps; i++) {
            const line = $(`#step-line-${i}`);
            if (line) {
                line.classList.toggle('filled', i < stepNum);
            }
        }

        // Focus first input on new step
        if (animate) {
            setTimeout(() => {
                const step = $(`#step-${stepNum}`);
                const firstInput = step.querySelector('textarea, input');
                if (firstInput) firstInput.focus();
            }, 500);
        }

        // Update summary based on current step
        populateSummary();
        updateViewportHeight();

        // Scroll form card to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateViewportHeight() {
        setTimeout(() => {
            const step = $(`#step-${state.currentStep}`);
            const viewport = $('.wizard-viewport');
            if (step && viewport) {
                viewport.style.height = step.offsetHeight + 'px';
            }
        }, 50); // slight delay to allow DOM render
    }

    // ===== STEPPER CLICK NAVIGATION =====
    $$('.step-item').forEach(item => {
        item.addEventListener('click', () => {
            const target = parseInt(item.dataset.step);
            // Only allow clicking completed steps or the next available step
            if (target <= state.currentStep || (target === state.currentStep + 1 && isStepComplete(state.currentStep))) {
                goToStep(target);
            }
        });
    });

    // ===== BUTTON NAVIGATION =====
    $('#btn-next-1').addEventListener('click', () => goToStep(2));
    $('#btn-back-2').addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); goToStep(1); });

    // ===== RENDER ANSWERS =====
    function renderAnswerInputs(questionNum, count) {
        const container = $(`#answers-container-${questionNum}`);
        container.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            const item = document.createElement('div');
            item.className = 'answer-item';
            item.style.animationDelay = `${(i - 1) * 0.06}s`;
            item.innerHTML = `
                <span class="answer-number">${i}</span>
                <input type="text" class="form-input answer-input"
                    placeholder="Respuesta ${i}"
                    data-question="${questionNum}" data-index="${i}" required />
            `;
            container.appendChild(item);
        }
        // Attach listeners for auto-advance checking
        container.querySelectorAll('.answer-input').forEach(input => {
            input.addEventListener('input', () => checkStepCompletion(questionNum));
        });
    }

    renderAnswerInputs(1, 2);
    renderAnswerInputs(2, 2);
    renderAnswerInputs(3, 2);
    renderAnswerInputs(4, 2);

    $$('.form-select[data-question]').forEach(select => {
        select.addEventListener('change', (e) => {
            const qNum = parseInt(e.target.dataset.question);
            renderAnswerInputs(qNum, parseInt(e.target.value));
            checkStepCompletion(qNum);
            updateViewportHeight();
        });
    });

    // ===== CHECK STEP COMPLETION =====
    function isStepComplete(stepNum) {
        if (stepNum === 1) {
            const market = $('#bl-market-step1').value;
            const client = $('#bl-client-step1').value.trim();
            const campaign = $('#bl-campaign-name-step1').value.trim();
            const investment = $('#bl-investment-step1').value.trim();
            return market && state.dpsSelections.length > 0 && client && campaign && investment;
        }
        if (stepNum === 2) {
            for (let i = 1; i <= state.questionCount; i++) {
                const question = $(`#bl-question-${i}`).value.trim();
                if (!question) return false;

                const answers = $$(`#answers-container-${i} .answer-input`);
                for (const input of answers) {
                    if (!input.value.trim()) return false;
                }
            }
            return true;
        }
        return true;
    }

    function checkStepCompletion(questionNum) {
        const complete = isStepComplete(2);
        const btnNext = $(`#btn-create`);

        if (btnNext) {
            btnNext.disabled = !complete;
        }

        // Mark inputs as valid
        const questionInput = $(`#bl-question-${questionNum}`);
        if (questionInput) {
            questionInput.classList.toggle('valid', questionInput.value.trim().length > 0);
        }

        $$(`#answers-container-${questionNum} .answer-input`).forEach(input => {
            input.classList.toggle('valid', input.value.trim().length > 0);
        });

        // Auto-advance logic disabled for grid layout since it's multiple questions
        // Or we can auto-advance if all questions are filled.
        
        // Update summaries live
        populateSummary();
        validateCurrentStep();
    }

    function validateCurrentStep() {
        if (state.currentStep >= 1 && state.currentStep <= 2) {
            const btnNext = state.currentStep === 1 ? $('#btn-next-1') : $('#btn-create');
            if (btnNext) {
                btnNext.disabled = !isStepComplete(state.currentStep);
            }
        }
    }

    // ===== AUTO-ADVANCE =====
    function triggerAutoAdvance(questionNum) {
        // Don't re-trigger if already advancing
        if (state.autoAdvanceTimers[questionNum]) return;

        const bar = $(`#auto-advance-${questionNum}`);
        const text = $(`#auto-advance-text-${questionNum}`);

        bar.classList.add('active');
        text.classList.add('show');

        // Auto-advance after 1.5 seconds
        state.autoAdvanceTimers[questionNum] = setTimeout(() => {
            let nextStep = getNextStep(questionNum);

            if (nextStep <= state.totalSteps) {
                goToStep(nextStep);
            }
            // Reset
            bar.classList.remove('active');
            text.classList.remove('show');
            bar.querySelector('.progress').style.width = '0%';
            // Force reflow then re-animate for future use
            void bar.offsetWidth;
            state.autoAdvanceTimers[questionNum] = null;
        }, 1500);
    }

    function cancelAutoAdvance(questionNum) {
        if (state.autoAdvanceTimers[questionNum]) {
            clearTimeout(state.autoAdvanceTimers[questionNum]);
            state.autoAdvanceTimers[questionNum] = null;
        }

        const bar = $(`#auto-advance-${questionNum}`);
        const text = $(`#auto-advance-text-${questionNum}`);
        if (bar) {
            bar.classList.remove('active');
            bar.querySelector('.progress').style.width = '0%';
        }
        if (text) text.classList.remove('show');
    }

    // ===== ATTACH LISTENERS TO QUESTION INPUTS =====
    ['1', '2', '3', '4'].forEach(qNum => {
        $(`#bl-question-${qNum}`).addEventListener('input', () => checkStepCompletion(parseInt(qNum)));
    });

    $('#bl-market-step1').addEventListener('change', () => {
        populateSummary();
        filterAdvertisers();
        filterSites();
    });
    $$('.dps-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            updateVariantCount();
        });
    });
    $('#bl-campaign-name-step1').addEventListener('input', populateSummary);
    $('#bl-investment-step1').addEventListener('input', populateSummary);

    // ===== QUESTION COUNT TOGGLE =====
    $('#bl-question-count').addEventListener('change', (e) => {
        const count = parseInt(e.target.value);
        state.questionCount = count;
        
        // Show/hide warning
        $('#q-count-warning').style.display = count >= 3 ? 'flex' : 'none';

        // Show/hide columns
        for(let i=2; i<=4; i++) {
            if (i <= count) {
                $(`#q-col-${i}`).style.display = 'block';
            } else {
                $(`#q-col-${i}`).style.display = 'none';
            }
        }
        checkStepCompletion(1);
        updateViewportHeight();
    });

    // ===== GROUPS & TAG TYPE MANAGEMENT =====
    function updateVariantCount() {
        const tagTypes = ['Ad_Exposed', 'Control'];
        state.tagTypes = tagTypes;

        const dpsSelections = [];
        $$('.dps-checkbox').forEach(cb => {
            if (cb.checked) dpsSelections.push(cb.value);
        });
        state.dpsSelections = dpsSelections;

        const groupInputs = $$('.group-name-input');
        state.groups = [...groupInputs].map(input => ({ name: input.value.trim() || 'Sin nombre' }));

        state.totalVariants = state.groups.length * tagTypes.length * Math.max(1, dpsSelections.length);
        populateSummary();
        updateViewportHeight();
    }

    // Add group button
    $('#btn-add-group').addEventListener('click', () => {
        const container = $('#groups-container');
        const index = container.querySelectorAll('.group-item').length;
        const groupItem = document.createElement('div');
        groupItem.className = 'group-item';
        groupItem.dataset.groupIndex = index;
        groupItem.innerHTML = `
            <span class="group-number">Grupo ${index + 1}</span>
            <input type="text" class="group-name-input" placeholder="Nombre del grupo..." value="">
            <button type="button" class="btn-remove-group" title="Eliminar grupo">✕</button>
        `;
        container.appendChild(groupItem);

        // Listen for name changes
        groupItem.querySelector('.group-name-input').addEventListener('input', updateVariantCount);

        // Remove button
        groupItem.querySelector('.btn-remove-group').addEventListener('click', () => {
            groupItem.remove();
            // Re-number groups
            $$('.group-item').forEach((item, i) => {
                item.querySelector('.group-number').textContent = `Grupo ${i + 1}`;
                item.dataset.groupIndex = i;
            });
            updateVariantCount();
        });

        updateVariantCount();
        groupItem.querySelector('.group-name-input').focus();
    });

    // Listen to default group name changes
    // Prevent Enter key from triggering unintended button clicks in group inputs
    $('#groups-container').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    document.querySelector('.group-name-input').addEventListener('input', updateVariantCount);

    // ===== POPULATE SUMMARY =====
    function populateSummary() {
        const inlineContainer = $('#inline-summary-container');
        const widgetContainer = $('#summary-widget-content');
        const summaryWidget = $('#summary-widget');

        if (!widgetContainer) return;

        let inlineHTML = '';
        let widgetHTML = '';
        let hasData = false;

        // --- Configuración Unificada ---
        const market = $('#bl-market-step1').value;
        const dps = state.dpsSelections.join(', ');
        const client = $('#bl-client-step1').value.trim();
        const campaign = $('#bl-campaign-name-step1').value.trim();
        const investment = $('#bl-investment-step1').value.trim();
        
        let clickEventSummary = 'Desactivado';
        const hasClickDPS = state.dpsSelections.some(d => ['TTD', 'Sonata', 'Amazon'].includes(d));
        if (hasClickDPS) {
            clickEventSummary = 'Activado';
        }

        const showConfig = market || client || state.dpsSelections.length > 0 || campaign || investment;
        if (showConfig) {
            hasData = true;
            widgetHTML += `
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div style="flex: 1; overflow: hidden;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px; font-size: 14px;">Configuración</div>
                        ${market ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Mercado:</strong> ${market}</div>` : ''}
                        ${client ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Anunciante:</strong> ${client}</div>` : ''}
                        ${dps ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>DPS:</strong> ${dps}</div>` : ''}
                        ${campaign ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Campaña:</strong> ${campaign}</div>` : ''}
                        ${investment ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Inversión:</strong> $${investment}</div>` : ''}
                        ${(market || dps || campaign) ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Preguntas:</strong> ${state.questionCount}</div>` : ''}
                        ${(state.groups.length > 0) ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Grupos:</strong> ${state.groups.map(g => g.name).join(', ')}</div>` : ''}
                        ${(state.tagTypes.length > 0) ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Tags:</strong> ${state.tagTypes.join(', ')}</div>` : ''}
                        ${(state.dpsSelections.length > 0) ? `<div style="color: var(--text-secondary); margin-bottom: 2px;"><strong>Evento de Click:</strong> ${clickEventSummary}</div>` : ''}
                        ${(state.totalVariants > 0) ? `<div style="color: var(--wpp-navy); margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(0,0,0,0.05); font-weight: 700; font-size: 13px;">Total de creativos a generar: ${state.totalVariants}</div>` : ''}
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)" style="padding: 4px 0; font-size: 11px; height: auto; width: 70px; min-width: 70px; text-align: center; border-radius: 12px; border: 1px solid var(--border-input); flex-shrink: 0; background: var(--wpp-white); color: var(--wpp-navy); font-weight: 600;">
                        Editar
                    </button>
                </div>
            `;
        }

        const colors = [
            'rgba(59,130,246,0.06)', // Blue for Q1
            'rgba(6,182,212,0.06)',  // Cyan for Q2
            'rgba(217,119,6,0.06)',  // Amber for Q3
            'rgba(124,58,237,0.06)'  // Violet for Q4
        ];
        const borderColors = [
            'rgba(59,130,246,0.1)',
            'rgba(6,182,212,0.1)',
            'rgba(217,119,6,0.1)',
            'rgba(124,58,237,0.1)'
        ];

        for (let i = 1; i <= state.questionCount; i++) {
            const q = $(`#bl-question-${i}`) ? $(`#bl-question-${i}`).value.trim() : '';
            if (!q) continue;

            const answers = [...$$(`#answers-container-${i} .answer-input`)].map(input => input.value.trim()).filter(v => v);
            
            hasData = true;

            // Inline HTML for Step 5
            inlineHTML += `
                <div style="padding:12px 16px; background:${colors[i-1]}; border:1px solid ${borderColors[i-1]}; border-radius:8px; margin-bottom:8px; font-size:13px; color:var(--text-secondary); line-height:1.5;">
                    <strong style="color:var(--text-primary);">P${i}:</strong> ${q}<br>
                    <span style="color:var(--text-muted);">Respuestas:</span> ${answers.join(' · ')}
                </div>
            `;

            // Widget HTML for Right Column
            widgetHTML += `
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div style="flex: 1; overflow: hidden;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px; font-size: 14px;">Pregunta ${i}</div>
                        <div style="color: var(--text-secondary); margin-bottom: 4px;">${q}</div>
                        <div style="color: var(--text-muted); font-size: 11px;">Respuestas: ${answers.join(', ')}</div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)" style="padding: 4px 0; font-size: 11px; height: auto; width: 70px; min-width: 70px; text-align: center; border-radius: 12px; border: 1px solid var(--border-input); flex-shrink: 0; background: var(--wpp-white); color: var(--wpp-navy); font-weight: 600;">
                        Editar
                    </button>
                </div>
            `;
        }

        if (inlineContainer) inlineContainer.innerHTML = inlineHTML;
        if (!hasData) {
            summaryWidget.style.display = 'none';
        } else {
            summaryWidget.style.display = 'block';
            widgetContainer.innerHTML = widgetHTML;
        }
    }

    // Update summary and validate live as user types
    let livePreviewTimer = null;
    function triggerLivePreview() {
        if (state.currentStep !== 2) return;
        clearTimeout(livePreviewTimer);
        livePreviewTimer = setTimeout(() => {
            if (state.currentStep !== 2) return; // Re-check inside timeout in case user navigated away
            // Check if at least Q1 has text
            const q1 = $('#bl-question-1')?.value?.trim();
            if (q1) {
                generateAndShowPreviews('');
            }
        }, 500);
    }

    document.addEventListener('input', () => {
        populateSummary();
        validateCurrentStep();
        triggerLivePreview();
    });
    document.addEventListener('change', () => {
        populateSummary();
        validateCurrentStep();
        triggerLivePreview();
    });

    // Initial population and validation
    populateSummary();
    validateCurrentStep();

    // ===== GENERATE CREATIVE HTML =====
    function generateCreativeHTML(questionsData, w, h, sheetId, campaign, market, groupName, tagType, clickUrl, theme = 'dark') {
        const isDark = theme === 'dark';
        
        const bgStyle = isDark 
            ? 'background:linear-gradient(160deg,#0a1628 0%,#0d2b5e 35%,#1a4a8a 50%,#0d2b5e 65%,#0a1628 100%);'
            : 'background:linear-gradient(160deg,#f8fafc 0%,#e2e8f0 35%,#cbd5e1 50%,#e2e8f0 65%,#f8fafc 100%);';
            
        const textStyle = isDark ? 'color:#ffffff;' : 'color:#000050;';
        const btnBg = isDark ? '#ffffff' : '#000050';
        const btnText = isDark ? '#000050' : '#ffffff';
        const glow = isDark ? 'rgba(30,100,200,0.25)' : 'rgba(255,255,255,0.6)';
        const footerColor = isDark ? 'rgba(255,255,255,0.6)' : 'rgba(0,0,80,0.6)';
        
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
 ${clickUrl ? `var clickTag = "${clickUrl}";` : ''}
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
            
            // Dynamic scaling based on answer count to prevent overflow
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
   <div onclick="storeAnswer('${qNum}', '${q.question.replace(/'/g,"\\'").replace(/"/g,"&quot;")}', '${a.replace(/'/g,"\\'").replace(/"/g,"&quot;")}'); setTimeout(function(){ showScreen('${nextScreen}'); ${qNum === questionsData.length ? 'submitAnswers();' : ''} }, 300);" class="btn-anim" style="background:${btnBg};border-radius:100px;padding:${btnPadding};text-align:center;cursor:pointer;font-family:Arial,Helvetica,sans-serif;font-size:${btnFontSize}px;font-weight:700;color:${btnText};letter-spacing:0.3px;width:80%;max-width:${btnMaxWidth}px;box-sizing:border-box;box-shadow:0 4px 10px rgba(0,0,0,0.15);">${a}</div>`;
            });
            
            html += `
  </div>
 </div>`;
        });

        html += `
 <div id="screen-thanks" class="screen hidden">
  <div style="${textStyle}font-size:22px;font-weight:800;text-align:center;line-height:1.4;margin-bottom:15px;text-shadow:0 2px 4px rgba(0,0,0,0.3);">¡Muchas gracias<br>por su opinión!</div>
 </div>
 <div style="position:absolute;bottom:10px;right:14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:${footerColor};letter-spacing:0.5px;z-index:2;"><span style="font-weight:800;">WPP</span><span style="font-weight:400;"> Media</span></div>
</div>
</body>
</html>`;
        return html;
    }

    // ===== GET QUESTION DATA =====
    function getQuestionData(i) {
        const qInput = $(`#bl-question-${i}`);
        const question = qInput ? qInput.value.trim() : '';
        const answers = [];
        const answerInputs = $$(`#answers-container-${i} .answer-input`);
        
        let allFilled = true;
        answerInputs.forEach(input => {
            const val = input.value.trim();
            if (!val) allFilled = false;
            answers.push(val);
        });
        return { question, answers, allFilled };
    }

    function generateAndShowPreviews(sheetId = '') {
        const questionsData = [];
        for (let i = 1; i <= state.questionCount; i++) {
            questionsData.push(getQuestionData(i));
        }
        
        const market = $('#bl-market-step1').value || 'TEST';
        const campaignNameStep1 = $('#bl-campaign-name-step1').value.trim() || 'TEST';
        
        updateVariantCount();

        // Use default tags/groups for early preview if empty
        const tagTypes = state.tagTypes.length > 0 ? state.tagTypes : ['Ad_Exposed'];
        const dpsSelections = state.dpsSelections.length > 0 ? state.dpsSelections : ['DPS_Test'];
        const groups = state.groups.length > 0 ? state.groups : [{name: 'General'}];
        
        state.generatedCreatives = [];

        for (const dps of dpsSelections) {
            const clickEnabled = (dps === 'TTD' || dps === 'Sonata' || dps === 'Amazon');
            const clickUrl = clickEnabled ? 'https://www.wppmedia.com' : '';

            for (const group of groups) {
                for (const tagType of tagTypes) {
                    const key = `${dps}_${group.name}_${tagType}`;
                    const html = generateCreativeHTML(questionsData, state.selectedSize.width, state.selectedSize.height, sheetId, campaignNameStep1, market, group.name, tagType, clickUrl, state.theme);
                    state.generatedCreatives.push({ key, groupName: group.name, tagType, html, dps });
                }
            }
        }

        state.generatedHTML[1] = state.generatedCreatives[0]?.html || '';

        const previewTabs = $('#preview-tabs');
        previewTabs.innerHTML = '';
        previewTabs.style.display = 'none'; // Hidden per user request
        previewTabs.style.flexWrap = 'wrap';
        previewTabs.style.gap = '6px';

        state.generatedCreatives.forEach((variant, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `variant-btn ${idx === 0 ? 'active' : ''}`;
            btn.textContent = variant.key;
            btn.dataset.variantIndex = idx;
            btn.addEventListener('click', () => {
                switchVariant(idx);
            });
            previewTabs.appendChild(btn);
        });

        $('#preview-placeholder').style.display = 'none';
        switchVariant(0);
        $('#preview-frame').classList.add('active');
        state.creativesGenerated = true;
    }

    // ===== CREATE BRANDLIFT =====
    $('#btn-create').addEventListener('click', async (e) => {
        if ($('#btn-create').disabled || window.cm360TagsData) {
            e.preventDefault();
            return;
        }
        generateAndShowPreviews();
        const questionsData = [];
        for (let i = 1; i <= state.questionCount; i++) {
            questionsData.push(getQuestionData(i));
        }

        const market = $('#bl-market-step1').value;
        const campaignNameStep1 = $('#bl-campaign-name-step1').value.trim();
        const investmentStep1 = $('#bl-investment-step1').value.trim();
        const profileId = $('#cm360-profile-id').value.trim();
        const advertiserId = $('#cm360-advertiser-id').value.trim();
        const siteId = $('#cm360-site-id').value.trim();

        if (!market) { showToast('⚠️ Selecciona un mercado', true); goToStep(1); return; }
        if (state.dpsSelections.length === 0) { showToast('⚠️ Selecciona al menos un DPS', true); goToStep(1); return; }
        if (!campaignNameStep1) { showToast('⚠️ Ingresa el nombre de la campaña', true); goToStep(1); return; }
        if (!investmentStep1) { showToast('⚠️ Ingresa la inversión / bonificado', true); goToStep(1); return; }
        if (!profileId || !advertiserId || !siteId) { showToast('⚠️ Selecciona Profile, Advertiser y Site en la Configuración Inicial', true); goToStep(1); return; }
        
        for (let i = 0; i < state.questionCount; i++) {
            const q = questionsData[i];
            if (!q.question || !q.allFilled) {
                showToast(`⚠️ Completa la Pregunta ${i + 1}`, true);
                goToStep(2);
                return;
            }
        }

        const btn = $('#btn-create');
        btn.disabled = true;
        btn.innerHTML = `<div class="spinner"></div> Creando Tags...`;
        
        // 1. Automatizar el Google Sheet clonado en el Backend
        let sheetId = null;
        try {
            const clientName = $('#bl-client-step1').value.trim();
            const res = await fetch('/api/brandlift/automate-sheet', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ market, campaign_name: campaignNameStep1, client_name: clientName })
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Error desconocido al configurar Workspace');
            }
            sheetId = data.sheet_id;
            showToast('✅ Excel configurado exitosamente');
        } catch (e) {
            console.error(e);
            showToast('❌ Error Google Drive: ' + e.message, true);
            btn.disabled = false;
            btn.innerHTML = `Crear Tags`;
            return;
        }

        btn.innerHTML = `<div class="spinner"></div> Generando creativos...`;
        await new Promise(r => setTimeout(r, 800));

        try {
            updateVariantCount();
            if (state.tagTypes.length === 0) {
                showToast('⚠️ Selecciona al menos un tipo de tag (Ad_Exposed o Control)', true);
                btn.disabled = false;
                btn.innerHTML = `Crear Tags`;
                return;
            }

            // Generate previews with actual sheetId
            generateAndShowPreviews(sheetId);

            // 5. Guardar en la base de datos
            try {
                const questionsPayload = [];
                for (let i = 0; i < state.questionCount; i++) {
                    const q = questionsData[i];
                    questionsPayload.push({
                        question_number: i + 1,
                        question_text: q.question,
                        answers: q.answers,
                        creative_html: i === 0 ? (state.generatedCreatives[0]?.html || '') : null
                    });
                }

                const storeRes = await fetch('/api/brandlift/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        market: market,
                        campaign_name: campaignNameStep1,
                        question_count: state.questionCount,
                        creative_width: state.selectedSize.width,
                        creative_height: state.selectedSize.height,
                        client_name: clientName,
                        audiences: state.groups.map(g => g.name),
                        dps_tags: state.dpsSelections,
                        sheet_id: sheetId,
                        questions: questionsPayload
                    })
                });
                const storeData = await storeRes.json();
                if (storeData.success) {
                    state.studyId = storeData.study_id;
                    console.log('Brandlift guardado en DB, ID:', state.studyId);
                }
            } catch (storeErr) {
                console.error('Error guardando en DB (no crítico):', storeErr);
            }

            /* CM360 Push Logic */
        const statusBar = $('#cm360-status');
        
        btn.innerHTML = `<div class="spinner"></div> Subiendo a CM360...`;
        statusBar.className = 'status-bar visible loading';
        statusBar.innerHTML = `<div class="spinner"></div> Creando campaña, placements, ads y generando tags...`;

            statusBar.innerHTML = `<div class="spinner"></div> Generando captura de backup...`;
            
            // Generate screenshot of the first variant for the Default Ad
            let backupImageBase64 = null;
            if (state.generatedCreatives.length > 0) {
                try {
                    // Create a hidden container div with exact creative dimensions
                    const captureContainer = document.createElement('div');
                    captureContainer.style.cssText = `
                        position: fixed; left: -9999px; top: 0; z-index: -1;
                        width: ${state.selectedSize.width}px;
                        height: ${state.selectedSize.height}px;
                        overflow: hidden;
                    `;

                    // Create a shadow-isolated wrapper to prevent style leaks
                    const shadowHost = document.createElement('div');
                    captureContainer.appendChild(shadowHost);
                    document.body.appendChild(captureContainer);

                    // Parse the creative HTML and extract just the body content + styles
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(state.generatedCreatives[0].html, 'text/html');

                    // Build a self-contained render div
                    const renderDiv = document.createElement('div');
                    renderDiv.style.cssText = `
                        width: ${state.selectedSize.width}px;
                        height: ${state.selectedSize.height}px;
                        position: relative; overflow: hidden;
                    `;

                    // Copy <style> tags from parsed doc
                    doc.querySelectorAll('style').forEach(s => {
                        const style = document.createElement('style');
                        style.textContent = s.textContent;
                        renderDiv.appendChild(style);
                    });

                    // Copy <body> inner HTML and its inline styles
                    const bodyEl = doc.querySelector('body');
                    const bodyWrapper = document.createElement('div');
                    bodyWrapper.style.cssText = (bodyEl?.getAttribute('style') || '') + `; width: ${state.selectedSize.width}px; height: ${state.selectedSize.height}px; position: relative; overflow: hidden; margin: 0; padding: 0;`;
                    bodyWrapper.innerHTML = bodyEl?.innerHTML || '';

                    // Force the first screen to be visible for the screenshot
                    const screens = bodyWrapper.querySelectorAll('.screen');
                    screens.forEach(screen => {
                        if (screen.id === 'screen-q1') {
                            screen.style.opacity = '1';
                            screen.style.transform = 'scale(1)';
                            screen.style.pointerEvents = 'auto';
                        } else {
                            screen.style.opacity = '0';
                            screen.style.display = 'none';
                        }
                    });

                    renderDiv.appendChild(bodyWrapper);
                    captureContainer.appendChild(renderDiv);

                    // Wait for rendering
                    await new Promise(r => setTimeout(r, 500));

                    const canvas = await html2canvas(renderDiv, {
                        useCORS: true,
                        width: state.selectedSize.width,
                        height: state.selectedSize.height,
                        scale: 1,
                        backgroundColor: null,
                        logging: false
                    });
                    backupImageBase64 = canvas.toDataURL('image/jpeg', 0.9);
                    console.log('✅ Backup image captured successfully, size:', backupImageBase64.length);

                    // Cleanup
                    document.body.removeChild(captureContainer);
                } catch (e) {
                    console.error("Error capturing backup image:", e);
                }
            }
            
            statusBar.innerHTML = `<div class="spinner"></div> Creando campaña, placements, ads y generando tags...`;

            const response = await fetch('/api/brandlift/push-to-cm360', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
                body: JSON.stringify({
                    profile_id: profileId,
                    advertiser_id: advertiserId,
                    site_id: siteId,
                    market: $('#bl-market-step1').value,
                    client_name: $('#bl-client-step1').value.trim(),
                    campaign_name: $('#bl-campaign-name-step1').value.trim(),
                    creative_name: 'Brandlift Creative',
                    study_id: state.studyId || null,
                    backup_image: backupImageBase64,
                    creatives: state.generatedCreatives.map((variant, idx) => ({
                        question_number: idx + 1,
                        html: variant.html,
                        width: state.selectedSize.width,
                        height: state.selectedSize.height,
                        variant_key: variant.key
                    }))
                })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                statusBar.className = 'status-bar visible success';
                statusBar.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> ${data.message || 'Creativos subidos exitosamente'}`;
                showToast('✅ ¡Creativos subidos y tags generados!');

                window.cm360TagsData = data.results;
                $('#btn-download-excel-tags').style.display = 'flex';
                
                // Disable the create button so it can't be clicked again
                const btnCreate = $('#btn-create');
                btnCreate.disabled = true;
                btnCreate.style.pointerEvents = 'none';
                btnCreate.style.opacity = '0.5';
                btnCreate.style.cursor = 'not-allowed';
                
                // Trigger auto download
                downloadExcelTags(
                    window.cm360TagsData,
                    $('#bl-market-step1').value, 
                    $('#bl-client-step1').value, 
                    $('#bl-campaign-name-step1').value
                );
                $('#cm360-tags-output').style.display = 'block';

            } else {
                // Show detailed error per creative
                statusBar.className = 'status-bar visible error';
                let errorHtml = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> `;
                errorHtml += `<div>${data.message || 'Error al subir creativos'}`;
                
                if (data.results && data.results.length > 0) {
                    const failedResults = data.results.filter(r => r.status === 'error');
                    if (failedResults.length > 0) {
                        errorHtml += '<div style="margin-top:8px; font-size:12px; opacity:0.85;">';
                        failedResults.forEach(r => {
                            errorHtml += `<div style="margin-top:4px;">❌ <strong>Q${r.question_number}</strong> (${r.creative_name}): ${r.error}</div>`;
                        });
                        errorHtml += '</div>';
                    }
                    
                    // Show Excel for any successful creatives
                    const successResults = data.results.filter(r => r.status === 'success');
                    if (successResults.length > 0) {
                        window.cm360TagsData = successResults;
                        $('#btn-download-excel-tags').style.display = 'flex';
                        $('#cm360-tags-output').style.display = 'block';
                    }
                }
                
                errorHtml += '</div>';
                statusBar.innerHTML = errorHtml;
                showToast('❌ Error al subir a CM360', true);
            }


            showToast(`✅ ¡${state.generatedCreatives.length} creativos generados exitosamente!`);
        } catch (error) {
            console.error(error);
            showToast('❌ Error al generar los creativos', true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span class="ripple"></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Crear Tags`;
            updateViewportHeight();
        }
    });

    // ===== VARIANT SWITCHING =====
    function switchVariant(index) {
        state.activeVariantIndex = index;
        const variant = state.generatedCreatives[index];
        if (!variant) return;

        // Update active button
        $$('.variant-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.querySelector(`.variant-btn[data-variant-index="${index}"]`);
        if (activeBtn) activeBtn.classList.add('active');

        // Update iframe preview only if HTML changed
        const container = $('#preview-content-1');
        const existingIframe = container.querySelector('iframe');
        
        if (existingIframe && existingIframe.dataset.lastHtml === variant.html) {
            // HTML hasn't changed, do not recreate iframe to preserve state!
            return;
        }

        container.innerHTML = '';
        container.classList.add('visible');
        const iframe = document.createElement('iframe');
        iframe.srcdoc = variant.html;
        iframe.dataset.lastHtml = variant.html;
        iframe.style.cssText = `border:none;width:${state.selectedSize.width}px;height:${state.selectedSize.height}px;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.4);`;
        container.appendChild(iframe);
    }
    // ===== CM360 AUTO-FETCH LOGIC =====
    window.cm360Advertisers = [];
    window.cm360Sites = [];

    async function loadProfiles() {
        const profileSelect = $('#cm360-profile-id');
        const loader = $('#profile-loader');

        loader.style.display = 'inline';

        try {
            const res = await fetch('/api/cm360/profiles');
            const profiles = await res.json();

            profileSelect.innerHTML = '<option value="">Selecciona un perfil...</option>';
            if (profiles && profiles.length > 0) {
                // Auto-select wmcs-campaign-tracker if found
                let selectedId = profiles[0].id; // fallback to first
                profiles.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.name} (${p.id})`;
                    profileSelect.appendChild(opt);
                    if (p.name.toLowerCase().includes('wmcs-campaign-tracker') || p.name.toLowerCase().includes('wmcs campaign tracker')) {
                        selectedId = p.id;
                    }
                });
                
                profileSelect.value = selectedId;
                loadAdvertisers(selectedId);
                loadSites(selectedId);
            } else {
                profileSelect.innerHTML = '<option value="">No hay perfiles disponibles</option>';
            }
        } catch (e) {
            console.error('Error fetching profiles', e);
            profileSelect.innerHTML = '<option value="">Error cargando perfiles</option>';
        } finally {
            loader.style.display = 'none';
        }
    }

    async function loadAdvertisers(profileId) {
        const advSelect = $('#cm360-advertiser-id');
        const loader = $('#advertiser-loader');

        if (!profileId) return;

        loader.style.display = 'inline';

        try {
            const res = await fetch(`/api/cm360/advertisers/${profileId}`);
            const advertisers = await res.json();
            
            if (advertisers && advertisers.length > 0) {
                window.cm360Advertisers = advertisers.sort((a, b) => a.name.localeCompare(b.name));
                filterAdvertisers(); // Filter based on current market selection
            } else {
                window.cm360Advertisers = [];
                advSelect.innerHTML = '<option value="">No hay anunciantes</option>';
            }
        } catch (e) {
            console.error('Error fetching advertisers', e);
            advSelect.innerHTML = '<option value="">Error cargando anunciantes</option>';
        } finally {
            loader.style.display = 'none';
        }
    }

    function filterAdvertisers() {
        const advSelect = $('#cm360-advertiser-id');
        const market = $('#bl-market-step1').value;
        
        if (!market) {
            advSelect.innerHTML = '<option value="">Selecciona un mercado primero...</option>';
            advSelect.disabled = true;
            return;
        }

        if (window.cm360Advertisers.length === 0) return;

        advSelect.innerHTML = '<option value="">Selecciona un anunciante...</option>';
        
        let filteredCount = 0;
        window.cm360Advertisers.forEach(a => {
            // Filter by prefix (e.g. COL)
            if (a.name.toUpperCase().startsWith(market.toUpperCase())) {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = `${a.name} (${a.id})`;
                advSelect.appendChild(opt);
                filteredCount++;
            }
        });

        if (filteredCount === 0) {
            advSelect.innerHTML = `<option value="">No se encontraron anunciantes para ${market}</option>`;
        }
        advSelect.disabled = false;
    }

    async function loadSites(profileId) {
        const siteSelect = $('#cm360-site-id');
        const loader = $('#site-loader');

        if (!profileId) return;

        loader.style.display = 'inline';

        try {
            const res = await fetch(`/api/cm360/sites/${profileId}`);
            const sites = await res.json();

            if (sites && sites.length > 0) {
                window.cm360Sites = sites.sort((a, b) => a.name.localeCompare(b.name));
                filterSites();
            } else {
                window.cm360Sites = [];
                siteSelect.innerHTML = '<option value="">No hay sites disponibles</option>';
            }
        } catch (e) {
            console.error('Error fetching sites', e);
            siteSelect.innerHTML = '<option value="">Error cargando sites</option>';
        } finally {
            loader.style.display = 'none';
        }
    }

    function filterSites() {
        const siteSelect = $('#cm360-site-id');
        const marketSelect = $('#bl-market-step1');
        const marketVal = marketSelect.value;
        
        if (!marketVal) {
            siteSelect.innerHTML = '<option value="">Selecciona un mercado primero...</option>';
            siteSelect.disabled = true;
            return;
        }

        if (window.cm360Sites.length === 0) return;

        const selectedOption = marketSelect.options[marketSelect.selectedIndex];
        const countryName = selectedOption.getAttribute('data-country');
        
        let targetSiteName = `Xaxis ${countryName}`.toLowerCase();
        if (marketVal === 'PE') {
            targetSiteName = 'adwords';
        } else if (marketVal === 'ARG') {
            targetSiteName = 'xaxis_argentina';
        }

        siteSelect.innerHTML = '<option value="">Selecciona un site...</option>';
        
        let foundMatchId = null;
        window.cm360Sites.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.name} (${s.id})`;
            siteSelect.appendChild(opt);

            // Using includes since site names might have prefixes/suffixes
            if (s.name.toLowerCase().includes(targetSiteName)) {
                foundMatchId = s.id;
            }
        });

        if (foundMatchId) {
            siteSelect.value = foundMatchId;
        }
        
        siteSelect.disabled = false;
    }

    $('#cm360-advertiser-id').addEventListener('change', (e) => {
        const advId = e.target.value;
        if (!advId) {
            $('#bl-client-step1').value = '';
            populateSummary();
            return;
        }

        const adv = window.cm360Advertisers.find(a => a.id === advId);
        if (adv) {
            // Parse client name: take the last part after _, |, or -
            const parts = adv.name.split(/[_\|-]/);
            if (parts.length > 0) {
                const clientName = parts[parts.length - 1].trim();
                $('#bl-client-step1').value = clientName;
                populateSummary();
            }
        }
    });

    $('#cm360-profile-id').addEventListener('change', (e) => {
        loadAdvertisers(e.target.value);
        loadSites(e.target.value);
    });

    // Load profiles on init
    loadProfiles();

    

    // ===== TOAST =====
    function showToast(message, isError = false) {
        const t = $('#toast');
        $('#toast-message').textContent = message;
        t.style.background = isError ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)';
        t.style.borderColor = isError ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)';
        t.style.color = isError ? '#fca5a5' : '#6ee7b7';
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // ===== DOWNLOAD CM360 TAGS AS EXCEL (CSV) =====
    function downloadExcelTags(tagsData, market, client, campaignName) {
        if (!tagsData || tagsData.length === 0) return;
        
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
        
        market = market || '';
        client = client ? client.trim() : '';
        campaignName = campaignName ? campaignName.trim() : 'Brandlift';
        const year = new Date().getFullYear();
        const month = String(new Date().getMonth() + 1).padStart(2, '0');
        const fileName = `${year}_${month}_MCS_${market}_${client.replace(/\s+/g,'_')}_${campaignName}_Tags`;
        
        XLSX.writeFile(wb, `${fileName}.xlsx`);
    }

    $('#btn-download-excel-tags').addEventListener('click', () => {
        downloadExcelTags(
            window.cm360TagsData, 
            $('#bl-market-step1').value, 
            $('#bl-client-step1').value, 
            $('#bl-campaign-name-step1').value
        );
    });

    // ===== THEME TOGGLE =====
    function regenerateThemeFast() {
        if (!state.creativesGenerated) return;
        
        const questionsData = [];
        for (let i = 1; i <= state.questionCount; i++) {
            questionsData.push(getQuestionData(i));
        }
        const market = $('#bl-market-step1').value;
        const campaignNameStep1 = $('#bl-campaign-name-step1').value.trim();
        const sheetOption = document.querySelector('input[name="sheet-option"]:checked')?.value || 'manual';
        const sheetId = sheetOption === 'manual' ? ($('#bl-sheet-id')?.value?.trim() || '') : (state.studyId || '');

        state.generatedCreatives.forEach(variant => {
            const isClickEvent = ['TTD', 'Sonata', 'Amazon'].includes(variant.dps);
            const clickUrl = isClickEvent ? 'https://www.wppmedia.com/es' : '';
            variant.html = generateCreativeHTML(
                questionsData,
                state.selectedSize.width,
                state.selectedSize.height,
                sheetId,
                campaignNameStep1,
                market,
                variant.groupName,
                variant.tagType,
                clickUrl,
                state.theme
            );
        });

        // Update the iframe directly
        switchVariant(state.activeVariantIndex);
    }

    $('#theme-dark').addEventListener('click', () => {
        state.theme = 'dark';
        $('#theme-dark').style.background = 'var(--wpp-navy)';
        $('#theme-dark').style.color = 'white';
        $('#theme-light').style.background = 'transparent';
        $('#theme-light').style.color = 'var(--text-secondary)';
        regenerateThemeFast();
    });

    $('#theme-light').addEventListener('click', () => {
        state.theme = 'light';
        $('#theme-light').style.background = 'var(--wpp-navy)';
        $('#theme-light').style.color = 'white';
        $('#theme-dark').style.background = 'transparent';
        $('#theme-dark').style.color = 'var(--text-secondary)';
        regenerateThemeFast();
    });

    // Initialize height on load
    window.addEventListener('load', () => {
        updateViewportHeight();
    });

    window.addEventListener('message', function(event) {
        if (event.data === 'brandlift_finished') {
            document.querySelectorAll('.btn-restart-preview').forEach(btn => btn.style.display = 'inline-flex');
        }
    });
    </script>
            </div><!-- .content-area -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
</body>
</html>
