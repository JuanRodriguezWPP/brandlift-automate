<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Creador de brandlifts — WPP Media</title>
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
            margin-bottom: 20px;
            padding-bottom: 16px;
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

        /* ===== RUIXEN WIZARD STEPPER ===== */
        .ruixen-stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 18px;
            padding: 10px 18px;
            background: rgba(0, 0, 80, 0.02);
            border: 1px solid rgba(0, 0, 80, 0.06);
            border-radius: var(--radius-md);
            gap: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .ruixen-step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 6px 10px;
            border-radius: var(--radius-sm);
            flex-shrink: 0;
        }

        .ruixen-step-item:hover {
            background: rgba(0, 0, 80, 0.04);
        }

        .ruixen-step-circle-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ruixen-step-halo {
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 2px solid rgba(176, 244, 103, 0.5);
            opacity: 0;
            transform: scale(0.85);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }

        .ruixen-step-item.active .ruixen-step-halo {
            opacity: 1;
            transform: scale(1);
            animation: ruixenPulse 2.5s infinite ease-in-out;
        }

        @keyframes ruixenPulse {
            0%, 100% { transform: scale(1); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 0.25; }
        }

        .ruixen-step-circle {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            background: rgba(0, 0, 80, 0.05);
            color: var(--text-muted);
            border: 1px solid rgba(0, 0, 80, 0.12);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }

        .ruixen-step-check {
            display: none;
            width: 15px;
            height: 15px;
        }

        .ruixen-step-text {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .ruixen-step-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            line-height: 1.3;
            transition: color 0.25s ease;
        }

        .ruixen-step-desc {
            font-size: 11px;
            color: var(--text-muted);
            opacity: 0.75;
            line-height: 1.2;
            transition: color 0.25s ease, opacity 0.25s ease;
        }

        /* Active State */
        .ruixen-step-item.active .ruixen-step-circle {
            background: var(--wpp-navy);
            color: var(--wpp-lime);
            border-color: var(--wpp-navy);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.25);
        }

        .ruixen-step-item.active .ruixen-step-title {
            color: var(--wpp-navy);
            font-weight: 700;
        }

        .ruixen-step-item.active .ruixen-step-desc {
            color: var(--text-secondary);
            opacity: 1;
        }

        /* Completed State */
        .ruixen-step-item.completed .ruixen-step-circle {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            border-color: var(--wpp-lime);
            box-shadow: 0 2px 8px rgba(176, 244, 103, 0.35);
        }

        .ruixen-step-item.completed .ruixen-step-number {
            display: none;
        }

        .ruixen-step-item.completed .ruixen-step-check {
            display: block;
        }

        .ruixen-step-item.completed .ruixen-step-title {
            color: var(--text-primary);
        }

        .ruixen-step-item.completed .ruixen-step-desc {
            color: var(--text-muted);
            opacity: 0.9;
        }

        /* Connector */
        .ruixen-step-connector {
            flex: 1;
            height: 2px;
            background: rgba(0, 0, 80, 0.08);
            border-radius: 9999px;
            position: relative;
            overflow: hidden;
            margin: 0 12px;
        }

        .ruixen-connector-line {
            position: absolute;
            inset: 0;
            background: var(--wpp-lime);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ruixen-step-connector.filled .ruixen-connector-line {
            transform: scaleX(1);
        }

        @media (max-width: 640px) {
            .ruixen-stepper {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .ruixen-step-connector {
                display: none;
            }
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
            padding: 20px 24px 24px;
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
            position: relative;
            overflow: hidden;
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
            pointer-events: none;
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

        .custom-tooltip {
            position: relative;
            cursor: help;
            display: inline-flex;
            align-items: center;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .custom-tooltip:hover {
            color: var(--accent-blue);
        }

        .custom-tooltip::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background: #1e293b;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            width: max-content;
            max-width: 280px;
            text-align: center;
            white-space: normal;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            pointer-events: none;
            line-height: 1.4;
        }

        .custom-tooltip:hover::before {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-8px);
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

        .btn-add-answer {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(176, 244, 103, 0.15);
            border: 1px dashed var(--wpp-lime);
            border-radius: var(--radius-sm);
            color: var(--wpp-navy);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .btn-add-answer:hover {
            background: rgba(176, 244, 103, 0.3);
            border-style: solid;
        }

        .btn-remove-answer {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .btn-remove-answer:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-red, #ef4444);
        }

        .form-input.input-error, .form-textarea.input-error {
            border-color: var(--danger-red, #ef4444) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        #theme-selector-container.disabled {
            opacity: 0.45 !important;
            pointer-events: none !important;
            filter: grayscale(0.25);
            user-select: none;
        }

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

        /* ===== SUMMARY TIMELINE (Tracking Stepper) ===== */
        .summary-timeline {
            position: relative;
            padding: 0;
        }

        .timeline-step {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .timeline-step:last-child {
            padding-bottom: 0;
        }

        /* Vertical connector line connecting dots */
        .timeline-step:not(:last-child)::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 6.75px;
            bottom: -2px;
            width: 1.5px;
            background: #e2e8f0;
            z-index: 1;
        }

        /* Marker container (15x15 circle, centered) */
        .timeline-marker {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            background: #ffffff;
            border-radius: 50%;
            margin-top: 1px;
            transition: all 0.2s ease;
        }

        /* State: Completed - Solid Dark Dot */
        .timeline-step.is-completed .timeline-marker::after {
            content: '';
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background: #0f172a;
        }

        /* State: Active - Concentric Circle (Outer ring + inner solid dot) */
        .timeline-step.is-active .timeline-marker {
            box-shadow: 0 0 0 1.5px #94a3b8;
        }

        .timeline-step.is-active .timeline-marker::after {
            content: '';
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background: #0f172a;
        }

        /* State: Pending - Soft Gray Dot */
        .timeline-step.is-pending .timeline-marker::after {
            content: '';
            width: 5.5px;
            height: 5.5px;
            border-radius: 50%;
            background: #cbd5e1;
        }

        /* State: Error - Red Alert Indicator */
        .timeline-step.is-error .timeline-marker {
            box-shadow: 0 0 0 1.5px var(--danger-red, #ef4444);
            background: rgba(239, 68, 68, 0.1);
        }

        .timeline-step.is-error .timeline-marker::after {
            content: '';
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background: var(--danger-red, #ef4444);
        }

        .timeline-step.is-error .timeline-title {
            color: var(--danger-red, #ef4444);
        }

        .timeline-step.is-error .timeline-meta {
            color: var(--danger-red, #ef4444);
            font-weight: 600;
        }

        /* Body & Content */
        .timeline-body {
            flex: 1;
            min-width: 0;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 10px;
        }

        .timeline-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            line-height: 1.25;
            transition: color 0.15s ease;
        }

        .timeline-step.is-pending .timeline-title {
            color: #94a3b8;
            font-weight: 500;
        }

        .timeline-meta {
            font-size: 11.5px;
            color: #94a3b8;
            white-space: nowrap;
            text-align: right;
            font-weight: 400;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .timeline-step.is-pending .timeline-meta {
            color: #94a3b8;
            opacity: 0.75;
        }

        .meta-edit-hint {
            color: var(--wpp-cornflower, #5465FF);
            opacity: 0;
            transform: translateX(-3px);
            transition: all 0.15s ease;
            font-size: 11px;
            font-weight: 600;
        }

        .timeline-step:hover .meta-edit-hint {
            opacity: 1;
            transform: translateX(0);
        }

        .timeline-step:hover .timeline-title {
            color: var(--wpp-cornflower, #5465FF);
        }

        .timeline-step.is-pending:hover .timeline-title {
            color: #64748b;
        }

        .timeline-desc {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.35;
            word-break: break-word;
        }

        .timeline-step.is-pending .timeline-desc {
            color: #cbd5e1;
        }

        .timeline-subitems {
            margin-top: 4px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .timeline-subitem {
            font-size: 11px;
            color: #475569;
            background: #f8fafc;
            padding: 3px 7px;
            border-radius: 4px;
            border: 1px solid #f1f5f9;
            line-height: 1.3;
        }

        .timeline-subitem.is-error {
            border-color: rgba(239, 68, 68, 0.35) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            color: #b91c1c !important;
        }

        .q-duplicate-warning, .answers-warning {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: var(--radius-sm, 6px);
            padding: 7px 12px;
            color: var(--danger-red, #ef4444);
            font-size: 12px;
            font-weight: 600;
            display: none;
            align-items: center;
            gap: 7px;
            margin-top: 8px;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fieldPulseHighlight {
            0% {
                border-color: #0066FF !important;
                box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.28), 0 4px 16px rgba(0, 102, 255, 0.12) !important;
                transform: translateY(-1px);
            }
            35% {
                border-color: #0066FF !important;
                box-shadow: 0 0 0 5px rgba(0, 102, 255, 0.35), 0 6px 20px rgba(0, 102, 255, 0.18) !important;
                transform: translateY(-1px);
            }
            70% {
                border-color: #0066FF !important;
                box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.2), 0 4px 12px rgba(0, 102, 255, 0.08) !important;
                transform: translateY(0);
            }
            100% {
                box-shadow: none;
                transform: translateY(0);
            }
        }

        .field-focus-highlight {
            animation: fieldPulseHighlight 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            transition: all 0.3s ease;
        }

        .form-group.field-focus-highlight,
        .groups-tag-wrapper.field-focus-highlight,
        .answers-container.field-focus-highlight {
            border-radius: var(--radius-md, 8px);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.22), 0 4px 16px rgba(0, 102, 255, 0.1) !important;
            background: rgba(0, 102, 255, 0.02);
        }

        .form-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-row-2col {
                grid-template-columns: 1fr;
            }
        }

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
            flex-direction: column;
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
            display: none;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-top: 16px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.45;
        }
        .status-bar.visible { display: flex; flex-direction: column; animation: slideIn 0.3s ease-out forwards; opacity: 0; }
        .status-bar.loading { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .status-bar.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .status-bar.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

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

        /* ===== GROUPS & DSP TOGGLES ===== */
        .dps-toggles-grid, .tag-type-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 20px;
            margin-top: 8px;
            align-items: center;
        }

        .dps-toggle-card, .tag-type-option {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 4px 10px 4px 4px;
            background: transparent;
            border-radius: 9999px;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
        }

        .dps-toggle-card:hover, .tag-type-option:hover {
            background: rgba(0, 102, 255, 0.04);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
            flex-shrink: 0;
        }

        .toggle-switch input.dps-checkbox {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
            margin: 0;
            pointer-events: none;
        }

        .toggle-track {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            transition: background-color 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 22px;
            display: flex;
            align-items: center;
        }

        .toggle-thumb {
            position: absolute;
            height: 18px;
            width: 18px;
            left: 2px;
            bottom: 2px;
            background-color: #ffffff;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 0 1px rgba(0, 0, 0, 0.15);
        }

        .toggle-switch input.dps-checkbox:checked + .toggle-track {
            background-color: #0066FF;
        }

        .toggle-switch input.dps-checkbox:checked + .toggle-track .toggle-thumb {
            transform: translateX(18px);
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.35), 0 0 1px rgba(0, 0, 0, 0.15);
        }

        .toggle-switch input.dps-checkbox:focus-visible + .toggle-track {
            outline: 2px solid #0066FF;
            outline-offset: 2px;
        }

        .dps-toggle-label, .tag-type-option span {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
        }

        .groups-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .group-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: rgba(0, 0, 80, 0.04);
            border: 1.5px solid var(--border-input);
            border-radius: 9999px;
            animation: fadeSlideIn 0.25s ease;
            transition: all var(--transition-fast);
        }

        .group-item:focus-within {
            border-color: var(--wpp-lime);
            background: var(--wpp-white);
            box-shadow: 0 0 0 2px rgba(176, 244, 103, 0.25);
        }

        .group-item.duplicate-error,
        .group-item.empty-error {
            border-color: var(--danger-red) !important;
            background: rgba(239, 68, 68, 0.08) !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25) !important;
        }

        .group-item.duplicate-error input,
        .group-item.empty-error input {
            color: var(--danger-red) !important;
        }

        .group-item.duplicate-error input::placeholder,
        .group-item.empty-error input::placeholder {
            color: rgba(239, 68, 68, 0.6) !important;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .group-item .group-number {
            display: none;
        }

        .group-item input {
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 0;
            width: auto;
            min-width: 80px;
            max-width: 200px;
        }

        .group-item input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .group-item .btn-remove-group {
            background: transparent;
            border: none;
            color: var(--text-muted);
            width: 18px;
            height: 18px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
            transition: all var(--transition-fast);
        }

        .group-item .btn-remove-group:hover {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-red);
        }

        .btn-add-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: transparent;
            border: 1.5px dashed var(--border-input);
            border-radius: 9999px;
            color: var(--wpp-navy);
            font-family: 'WPP', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            height: 32px;
        }

        .btn-add-group:hover {
            background: rgba(176, 244, 103, 0.15);
            border-color: var(--wpp-lime);
            color: var(--wpp-navy);
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
        @include('partials.sidebar', ['active' => 'brandlift'])

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            @include('partials.top-header', ['title' => isset($editStudy) && $editStudy ? 'Editar brandlift' : 'Crear brandlift'])

            <div class="content-area">

        @if(isset($editStudy) && $editStudy)
        <div style="background: rgba(176, 244, 103, 0.15); border: 1px solid rgba(176, 244, 103, 0.3); border-radius: var(--radius-md); padding: 12px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: var(--wpp-lime); color: var(--wpp-navy);"><span class="material-symbols-outlined" style="font-size: 18px;">edit</span></span>
                <div style="font-size: 14px; font-weight: 700; color: var(--wpp-navy);">Modo Edición: {{ $editStudy->campaign_name }}</div>
            </div>
            <a href="/dashboard" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: var(--radius-sm); background: #ffffff; border: 1px solid var(--border-card); color: var(--text-primary); font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver al Dashboard
            </a>
        </div>
        @endif

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- ==================== LEFT: FORM ==================== -->
            <div>
                <div class="card">

                    <!-- Ruixen Wizard Stepper -->
                    <div class="ruixen-stepper" id="ruixen-stepper">
                        <div class="ruixen-step-item active" data-step="1" id="ruixen-step-1">
                            <div class="ruixen-step-circle-wrapper">
                                <div class="ruixen-step-halo"></div>
                                <div class="ruixen-step-circle" id="ruixen-circle-1">
                                    <span class="ruixen-step-number">1</span>
                                    <svg class="ruixen-step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </div>
                            <div class="ruixen-step-text">
                                <span class="ruixen-step-title">Información general</span>
                                <span class="ruixen-step-desc">Configuración inicial y campaña</span>
                            </div>
                        </div>

                        <div class="ruixen-step-connector" id="ruixen-connector-1">
                            <div class="ruixen-connector-line"></div>
                        </div>

                        <div class="ruixen-step-item pending" data-step="2" id="ruixen-step-2">
                            <div class="ruixen-step-circle-wrapper">
                                <div class="ruixen-step-halo"></div>
                                <div class="ruixen-step-circle" id="ruixen-circle-2">
                                    <span class="ruixen-step-number">2</span>
                                    <svg class="ruixen-step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </div>
                            <div class="ruixen-step-text">
                                <span class="ruixen-step-title">Preguntas</span>
                                <span class="ruixen-step-desc">Configura preguntas y creativos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Wizard Viewport -->
                    <div class="wizard-viewport">
                        <div class="wizard-track" id="wizard-track">

                            <!-- ===== STEP 1: Question 1 ===== -->
                            <div class="wizard-step active" id="step-1">
                                <div class="question-block">
                                    @php
                                        $user = auth()->user();
                                        $assignedMarkets = $user?->assigned_markets ?? [];
                                        $singleMarket = count($assignedMarkets) === 1 ? $assignedMarkets[0] : null;
                                        $isSingleMarket = !empty($singleMarket) && !$user->isAdmin();
                                        $selectedMarket = old('market', $editStudy->market ?? (session('active_market') ?? ($singleMarket ?? '')));
                                        $dpsTags = $editStudy->dps_tags ?? [];
                                        if (is_string($dpsTags)) { $dpsTags = json_decode($dpsTags, true) ?? []; }
                                        $hasDps = fn($val) => in_array(strtolower($val), array_map('strtolower', (array)$dpsTags));
                                        $qCount = (int)($editStudy->question_count ?? 1);
                                        $allMarketDefs = [
                                            'PE' => ['name' => 'Perú (PE)', 'country' => 'Peru'],
                                            'PRI' => ['name' => 'Puerto Rico (PRI)', 'country' => 'Puerto Rico'],
                                            'ARG' => ['name' => 'Argentina (ARG)', 'country' => 'Argentina'],
                                            'MIA' => ['name' => 'Miami (MIA)', 'country' => 'Miami'],
                                            'MEX' => ['name' => 'México (MEX)', 'country' => 'Mexico'],
                                            'CHL' => ['name' => 'Chile (CHL)', 'country' => 'Chile'],
                                            'COL' => ['name' => 'Colombia (COL)', 'country' => 'Colombia'],
                                            'ECU' => ['name' => 'Ecuador (ECU)', 'country' => 'Ecuador'],
                                        ];
                                        if (!empty($assignedMarkets) && !$user->isAdmin()) {
                                            $marketOptions = array_intersect_key($allMarketDefs, array_flip($assignedMarkets));
                                        } else {
                                            $marketOptions = $allMarketDefs;
                                        }
                                    @endphp
                                    <div class="form-group" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2); {{ $isSingleMarket ? 'display: none;' : '' }}">
                                        <label for="bl-market-step1">Mercado <span class="required">*</span></label>
                                        <select id="bl-market-step1" class="form-select" required>
                                            <option value="" data-country="">Selecciona un mercado...</option>
                                            @foreach($marketOptions as $code => $mDef)
                                                <option value="{{ $code }}" data-country="{{ $mDef['country'] }}" {{ $selectedMarket === $code ? 'selected' : '' }}>
                                                    {{ $mDef['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if($isSingleMarket)
                                    <input type="hidden" id="bl-market-hidden-val" value="{{ $singleMarket }}">
                                    @endif

                                    <!-- CM360 Integration Fields -->
                                    <div class="form-group" style="display:none;">
                                        <label for="cm360-profile-id">Profile (Agencia/Red) <span id="profile-loader" style="font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-profile-id" class="form-select">
                                            <option value="">Cargando perfiles...</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="cm360-advertiser-id">Anunciante (Advertiser CM360) <span class="required">*</span> <span id="advertiser-loader" style="display:none; font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-advertiser-id" class="form-select" {{ !empty($editStudy) ? '' : 'disabled' }}>
                                            @if(!empty($editStudy?->client_name) || !empty($editStudy?->cm360_advertiser_id))
                                                <option value="{{ $editStudy->cm360_advertiser_id ?? '' }}" selected>
                                                    {{ $editStudy->client_name ?? 'Anunciante' }} {{ $editStudy->cm360_advertiser_id ? '('.$editStudy->cm360_advertiser_id.')' : '' }}
                                                </option>
                                            @else
                                                <option value="">Selecciona un mercado primero...</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: none; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="cm360-site-id">Medio (Site CM360) <span class="required">*</span> <span id="site-loader" style="display:none; font-size:11px; margin-left:8px; color:var(--accent-blue-light)">Cargando...</span></label>
                                        <select id="cm360-site-id" class="form-select" {{ !empty($editStudy) ? '' : 'disabled' }}>
                                            @if(!empty($editStudy?->cm360_site_id))
                                                <option value="{{ $editStudy->cm360_site_id }}" selected>Site ({{ $editStudy->cm360_site_id }})</option>
                                            @else
                                                <option value="">Selecciona un mercado primero...</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-group" style="display: none; padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-client-step1">Cliente <span class="required">*</span></label>
                                        <input type="text" id="bl-client-step1" class="form-control" value="{{ $editStudy->client_name ?? '' }}" placeholder="Se autocompleta con el anunciante">
                                    </div>

                                    <div class="form-group" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-campaign-name-step1">Nombre de la campaña <span class="required">*</span></label>
                                        <input type="text" id="bl-campaign-name-step1" class="form-input" value="{{ $editStudy->campaign_name ?? '' }}" placeholder="Ej.: Campaña de verano 2026" required>
                                    </div>

                                    <div class="form-row-2col" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="bl-investment-step1">Inversión / bonificado <span class="required">*</span></label>
                                            <div style="position: relative; display: flex; align-items: center;">
                                                <span style="position: absolute; left: 16px; color: var(--text-muted); font-weight: 600; font-size: 15px;">$</span>
                                                <input type="text" id="bl-investment-step1" class="form-input" style="padding-left: 32px;" value="{{ $editStudy->investment ?? '' }}" placeholder="Ej: 5,000.00" required>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label for="bl-end-date-step1">Fin de la campaña <span class="required">*</span></label>
                                            <div style="position: relative; width: 100%;">
                                                <input type="text" id="bl-end-date-visual" class="form-input" value="{{ !empty($editStudy->end_date) ? date('d/m/Y', strtotime($editStudy->end_date)) : '' }}" placeholder="-- / -- / ----" readonly style="cursor: pointer; background: var(--bg-input); pointer-events: none; color: var(--text-primary);">
                                                <input type="date" id="bl-end-date-step1" class="form-input" value="{{ $editStudy->end_date ?? '' }}" min="{{ date('Y-m-d') }}" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="
                                                    const visual = document.getElementById('bl-end-date-visual');
                                                    if(this.value) {
                                                        const today = new Date().toLocaleDateString('en-CA');
                                                        if (this.value < today) {
                                                            if (typeof showToast === 'function') {
                                                                showToast('La fecha de finalización no puede ser una fecha pasada', true);
                                                            }
                                                            this.value = '';
                                                            visual.value = '';
                                                            if (typeof populateSummary === 'function') populateSummary();
                                                            return;
                                                        }
                                                        const [y, m, d] = this.value.split('-');
                                                        visual.value = `${d}/${m}/${y}`;
                                                    } else {
                                                        visual.value = '';
                                                    }
                                                ">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label>¿Para qué DSP requieres tags?<span class="required">*</span></label>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: -4px; margin-bottom: 12px; line-height: 1.45; display: flex; align-items: flex-start; gap: 6px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px; color: var(--accent-lime);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                            <span>Esto es para el click del audit de cada plataforma. En caso de TTD, Sonata, Amazon, una vez pase el audit, se debe quitar el hotspot de click.</span>
                                        </div>
                                        <div class="dps-toggles-grid tag-type-options">
                                            <label class="dps-toggle-card">
                                                <span class="toggle-switch">
                                                    <input type="checkbox" id="dps-dv360" value="DV360" class="dps-checkbox" {{ $hasDps('DV360') ? 'checked' : '' }}>
                                                    <span class="toggle-track">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </span>
                                                <span class="dps-toggle-label">DV360</span>
                                            </label>
                                            <label class="dps-toggle-card">
                                                <span class="toggle-switch">
                                                    <input type="checkbox" id="dps-ttd" value="TTD" class="dps-checkbox" {{ $hasDps('TTD') ? 'checked' : '' }}>
                                                    <span class="toggle-track">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </span>
                                                <span class="dps-toggle-label">TTD - The Trade Desk</span>
                                            </label>
                                            <label class="dps-toggle-card">
                                                <span class="toggle-switch">
                                                    <input type="checkbox" id="dps-sonata" value="Sonata" class="dps-checkbox" {{ $hasDps('Sonata') ? 'checked' : '' }}>
                                                    <span class="toggle-track">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </span>
                                                <span class="dps-toggle-label">Sonata</span>
                                            </label>
                                            <label class="dps-toggle-card">
                                                <span class="toggle-switch">
                                                    <input type="checkbox" id="dps-amazon" value="Amazon" class="dps-checkbox" {{ $hasDps('Amazon') ? 'checked' : '' }}>
                                                    <span class="toggle-track">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </span>
                                                <span class="dps-toggle-label">Amazon</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Groups -->
                                    <div class="form-group" style="padding-bottom: 20px; border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label style="display: flex; align-items: center; gap: 6px; position: relative;">
                                            Grupos de audiencia
                                            <span class="custom-tooltip" data-tooltip="Opcional: Si tu campaña cuenta con una segmentación de audiencias, completa este campo para generar tags específicos para cada una.">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            </span>
                                        </label>
                                        <div class="groups-tag-wrapper" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 8px;">
                                            <div class="groups-container" id="groups-container">
                                                @if(!empty($editStudy?->audiences) && is_array($editStudy->audiences))
                                                    @foreach($editStudy->audiences as $audIdx => $audName)
                                                        <div class="group-item" data-group-index="{{ $audIdx }}">
                                                            <input type="text" class="group-name-input" placeholder="Nombre de audiencia..." value="{{ $audName }}" size="14">
                                                            <button type="button" class="btn-remove-group" title="Eliminar audiencia"><span class="material-symbols-outlined" style="font-size:16px;">close</span></button>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <button type="button" class="btn-add-group" id="btn-add-group">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                                Agregar audiencia
                                            </button>
                                        </div>
                                        <div id="groups-duplicate-warning" style="display: none; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                            <span>No se pueden repetir los nombres de los grupos de audiencia.</span>
                                        </div>
                                    </div>

                                    <div class="form-group" style="border-bottom: 1px dashed rgba(176, 244, 103, 0.2);">
                                        <label for="bl-question-count">¿Cuántas preguntas tendrá el Brandlift? <span class="required">*</span></label>
                                        <select id="bl-question-count" class="form-select">
                                            <option value="1" {{ $qCount === 1 ? 'selected' : '' }}>1 pregunta</option>
                                            <option value="2" {{ $qCount === 2 ? 'selected' : '' }}>2 preguntas</option>
                                            <option value="3" {{ $qCount === 3 ? 'selected' : '' }}>3 preguntas</option>
                                            <option value="4" {{ $qCount === 4 ? 'selected' : '' }}>4 preguntas</option>
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
                                        <span id="btn-next-1-text">Siguiente</span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- ===== STEP 2: Preguntas ===== -->
                            <div class="wizard-step" id="step-2">
                                <div class="question-block">
                                    @php
                                        $q1 = $editStudy?->questions?->firstWhere('question_number', 1);
                                        $q2 = $editStudy?->questions?->firstWhere('question_number', 2);
                                        $q3 = $editStudy?->questions?->firstWhere('question_number', 3);
                                        $q4 = $editStudy?->questions?->firstWhere('question_number', 4);
                                    @endphp
                                    <div id="questions-grid" class="questions-grid" style="display: grid; gap: 24px; grid-template-columns: 1fr;">

                                        <!-- Pregunta 1 -->
                                        <div class="q-col" id="q-col-1">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 1</div>
                                            <div class="form-group">
                                                <label for="bl-question-1">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-1" class="form-textarea" placeholder="Ej: ¿Recordás haber visto un anuncio de cápsulas La Virginia en el último tiempo?" rows="2" required>{{ $q1?->question_text ?? '' }}</textarea>
                                                <div id="q-duplicate-warning-1" class="q-duplicate-warning" style="display: none; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span>Las preguntas no pueden ser iguales.</span>
                                                </div>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-1" class="answers-container"></div>
                                                <div style="margin-top: 10px;">
                                                    <button type="button" class="btn-add-answer" data-question="1" id="btn-add-answer-1">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                                        Agregar respuesta
                                                    </button>
                                                </div>
                                                <div id="answers-warning-1" class="answers-warning" style="display: none; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span class="warning-text"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 2 -->
                                        <div class="q-col" id="q-col-2" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 2</div>
                                            <div class="form-group">
                                                <label for="bl-question-2">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-2" class="form-textarea" placeholder="Ej: ¿Cuál de las siguientes marcas de café conocés?" rows="2" required>{{ $q2?->question_text ?? '' }}</textarea>
                                                <div id="q-duplicate-warning-2" class="q-duplicate-warning" style="display: none; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span>Las preguntas no pueden ser iguales.</span>
                                                </div>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-2" class="answers-container"></div>
                                                <div style="margin-top: 10px;">
                                                    <button type="button" class="btn-add-answer" data-question="2" id="btn-add-answer-2">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                                        Agregar respuesta
                                                    </button>
                                                </div>
                                                <div id="answers-warning-2" class="answers-warning" style="display: none; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span class="warning-text"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 3 -->
                                        <div class="q-col" id="q-col-3" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 3</div>
                                            <div class="form-group">
                                                <label for="bl-question-3">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-3" class="form-textarea" placeholder="Ej: ¿Qué tan probable es que recomiendes nuestra marca?" rows="2" required>{{ $q3?->question_text ?? '' }}</textarea>
                                                <div id="q-duplicate-warning-3" class="q-duplicate-warning" style="display: none; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span>Las preguntas no pueden ser iguales.</span>
                                                </div>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-3" class="answers-container"></div>
                                                <div style="margin-top: 10px;">
                                                    <button type="button" class="btn-add-answer" data-question="3" id="btn-add-answer-3">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                                        Agregar respuesta
                                                    </button>
                                                </div>
                                                <div id="answers-warning-3" class="answers-warning" style="display: none; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span class="warning-text"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pregunta 4 -->
                                        <div class="q-col" id="q-col-4" style="display:none;">
                                            <div style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Pregunta 4</div>
                                            <div class="form-group">
                                                <label for="bl-question-4">Texto de la pregunta <span class="required">*</span></label>
                                                <textarea id="bl-question-4" class="form-textarea" placeholder="Ej: ¿Dónde viste nuestro último anuncio?" rows="2" required>{{ $q4?->question_text ?? '' }}</textarea>
                                                <div id="q-duplicate-warning-4" class="q-duplicate-warning" style="display: none; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span>Las preguntas no pueden ser iguales.</span>
                                                </div>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Opciones de respuesta <span class="required">*</span></label>
                                                <div id="answers-container-4" class="answers-container"></div>
                                                <div style="margin-top: 10px;">
                                                    <button type="button" class="btn-add-answer" data-question="4" id="btn-add-answer-4">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                                        Agregar respuesta
                                                    </button>
                                                </div>
                                                <div id="answers-warning-4" class="answers-warning" style="display: none; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; font-weight: 600; color: var(--danger-red);">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <span class="warning-text"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Color Selector placed below questions -->
                                    <div id="theme-selector-container" class="form-group disabled" style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed rgba(176, 244, 103, 0.2); transition: all 0.3s ease;">
                                        <label>Color del brandlift <span class="required">*</span></label>
                                        <div style="display:flex; gap:4px; align-items:center; background:var(--bg-input); padding:4px; border-radius:20px; border:1px solid rgba(0,0,0,0.05); width: fit-content;">
                                            <button type="button" id="theme-dark" style="border:none; padding:6px 16px; border-radius:16px; font-size:13px; cursor:pointer; background:var(--wpp-navy); color:white; font-weight:600; transition:all 0.2s;">Oscuro</button>
                                            <button type="button" id="theme-light" style="border:none; padding:6px 16px; border-radius:16px; font-size:13px; cursor:pointer; background:transparent; color:var(--text-secondary); font-weight:600; transition:all 0.2s;">Claro</button>
                                        </div>
                                        <span id="theme-disabled-hint" style="font-size: 11.5px; color: var(--text-muted); display: block; margin-top: 6px;">Completa las preguntas y respuestas para elegir el color</span>
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
                                        <span id="btn-create-text">Crear & Cargar</span>
                                    </button>
                                </div>

                                <!-- ===== CM360 RESULTS ===== -->
                                <div id="cm360-status" class="status-bar" style="margin-top: 18px;"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== RIGHT: PREVIEW ==================== -->
            <div class="preview-wrapper">
                <!-- Summary Widget -->
                <div class="card" id="summary-widget" style="margin-bottom: 18px; padding: 20px 22px;">
                    <div class="card-header" style="border-bottom: 1px solid rgba(0,0,80,0.06); padding-bottom: 10px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h2 style="font-size: 15px; font-weight: 700; color: var(--wpp-navy); margin: 0; line-height: 1.2;">Resumen</h2>
                        </div>
                        <span id="summary-stepper-badge" style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 12px; background: rgba(0,0,80,0.05); color: var(--wpp-navy); letter-spacing: 0.3px;">
                            Paso 1 de 2
                        </span>
                    </div>
                    <div id="summary-widget-content">
                        <!-- Generated dynamically by JS -->
                    </div>
                </div>

                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header">
                        <h2>Vista previa <small>Previsualización de los creativos</small></h2>
                    </div>

                    <div class="preview-tabs" id="preview-tabs" style="display:none;">
                        <button type="button" class="preview-tab active" data-preview="1">Vista previa interactiva</button>
                    </div>

                    <div id="preview-frame" class="preview-frame">
                        <div id="preview-placeholder" class="preview-placeholder">
                            <div class="icon"><span class="material-symbols-outlined" style="font-size: 48px; color: var(--text-muted);">palette</span></div>
                            <p>Digita las preguntas y respuestas<br>para ver la preview en tiempo real</p>
                        </div>
                        <div style="text-align: center; margin-bottom: 10px;">
                            <button type="button" class="btn btn-secondary btn-restart-preview" onclick="const f=document.querySelector('#preview-content-1 iframe'); if(f){const src=f.srcdoc; f.srcdoc=''; setTimeout(()=>f.srcdoc=src,10);} window.currentPreviewStep=0; document.getElementById('preview-nav-text').innerText='Pág 1';" style="display: none; font-size: 12px; padding: 6px 12px; align-items: center; gap: 6px; cursor: pointer; background: transparent; border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-secondary); margin: 0 auto;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                Reiniciar
                            </button>
                        </div>
                        <div id="preview-content-1" class="preview-content"></div>
                        <div id="preview-nav-controls" style="display: none; align-items: center; justify-content: center; gap: 16px; margin-top: 24px;">
                            <button type="button" onclick="navigatePreview(-1)" style="border:1px solid var(--border-color); background:transparent; cursor:pointer; width:32px; height:32px; border-radius:16px; display:flex; align-items:center; justify-content:center; color:var(--text-secondary); transition:all 0.2s;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                            </button>
                            <span id="preview-nav-text" style="font-size: 13px; font-weight: 600; color: var(--text-secondary); min-width: 60px; text-align: center;">Pág 1</span>
                            <button type="button" onclick="navigatePreview(1)" style="border:1px solid var(--border-color); background:transparent; cursor:pointer; width:32px; height:32px; border-radius:16px; display:flex; align-items:center; justify-content:center; color:var(--text-secondary); transition:all 0.2s;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- AI Interactive Stepper Loading Overlay -->
    <style>
        .ai-loading-overlay {
            font-family: 'WPP', sans-serif !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 40, 0.45);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .ai-loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .ai-stepper-card {
            font-family: 'WPP', sans-serif !important;
            background: #ffffff;
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 50, 0.15), 0 0 1px 1px rgba(0, 0, 50, 0.03);
            width: 100%;
            max-width: 520px;
            padding: 28px 28px 24px 28px;
            box-sizing: border-box;
            color: #0f172a;
            transform: scale(0.95) translateY(10px);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .ai-stepper-card * {
            font-family: 'WPP', sans-serif !important;
        }
        .ai-loading-overlay.active .ai-stepper-card {
            transform: scale(1) translateY(0);
        }

        /* AI Header */
        .ai-stepper-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            position: relative;
        }

        /* Animated AI Core Icon */
        .ai-core-icon {
            width: 44px;
            height: 44px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #f8fafc;
            border-radius: 50%;
        }
        .ai-core-ring-outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px dashed #b0f467;
            animation: aiSpin 8s linear infinite;
        }
        .ai-core-ring-inner {
            position: absolute;
            inset: 5px;
            border-radius: 50%;
            border: 2px solid #93dfe3;
            border-top-color: transparent;
            animation: aiSpinReverse 3s linear infinite;
        }
        .ai-core-glow {
            width: 18px;
            height: 18px;
            background: radial-gradient(circle, #b0f467 20%, #93dfe3 80%);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(176, 244, 103, 0.6);
            animation: aiPulse 2s ease-in-out infinite alternate;
        }
        @keyframes aiSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes aiSpinReverse {
            to { transform: rotate(-360deg); }
        }
        @keyframes aiPulse {
            0% { transform: scale(0.85); box-shadow: 0 0 8px rgba(176, 244, 103, 0.4); }
            100% { transform: scale(1.12); box-shadow: 0 0 16px rgba(176, 244, 103, 0.8), 0 0 24px rgba(147, 223, 227, 0.5); }
        }

        .ai-header-content {
            flex: 1;
            min-width: 0;
        }
        .ai-header-title {
            font-size: 17px;
            font-weight: 700;
            color: #000050;
            margin: 0 0 4px 0;
            letter-spacing: -0.2px;
            line-height: 1.3;
        }
        .ai-header-subtitle {
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Progress Bar */
        .ai-progress-container {
            margin-bottom: 18px;
            background: #f8fafc;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
        }
        .ai-progress-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12.5px;
        }
        .ai-progress-meta span:first-child {
            color: #64748b;
            font-weight: 500;
        }
        .ai-progress-meta span:last-child {
            color: #000050;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .ai-progress-track {
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }
        .ai-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #0ea5e9, #10b981);
            border-radius: 999px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Stepper Steps List */
        .ai-stepper-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
        }
        .ai-step-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: none;
            transition: all 0.25s ease;
        }
        .ai-step-row.pending {
            opacity: 0.55;
        }
        .ai-step-row.active {
            background: #f0fdf4;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
            opacity: 1;
            transform: translateX(3px);
        }
        .ai-step-row.completed {
            background: #f8fafc;
            opacity: 1;
        }
        .ai-step-row.error {
            background: #fef2f2;
            opacity: 1;
        }

        /* Icon / Number Indicator */
        .ai-step-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            background: #e2e8f0;
            color: #64748b;
            border: none;
            transition: all 0.25s ease;
        }
        .ai-step-row.active .ai-step-icon {
            background: #dcfce7;
            color: #166534;
        }
        .ai-step-row.completed .ai-step-icon {
            background: #b0f467;
            color: #000050;
        }
        .ai-step-row.error .ai-step-icon {
            background: #fee2e2;
            color: #ef4444;
        }

        .ai-spinner-sm {
            width: 14px;
            height: 14px;
            border: 2px solid #86efac;
            border-top-color: #16a34a;
            border-radius: 50%;
            animation: aiSpin 0.8s linear infinite;
        }

        /* Step Content */
        .ai-step-info {
            flex: 1;
            min-width: 0;
        }
        .ai-step-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
            line-height: 1.3;
            text-transform: none;
        }
        .ai-step-row.pending .ai-step-title {
            color: #64748b;
            font-weight: 500;
        }
        .ai-step-desc {
            font-size: 11.5px;
            color: #64748b;
            margin: 2px 0 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: none;
        }
        .ai-step-row.active .ai-step-desc {
            color: #15803d;
        }

        /* Status Badge */
        .ai-step-status-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 8px;
            text-transform: none;
            letter-spacing: 0.1px;
            flex-shrink: 0;
            border: none;
        }
        .ai-step-status-badge.pending {
            background: #e2e8f0;
            color: #64748b;
        }
        .ai-step-status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
        .ai-step-status-badge.completed {
            background: rgba(176, 244, 103, 0.25);
            color: #166534;
        }
        .ai-step-status-badge.error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
    <div id="full-loading-overlay" class="ai-loading-overlay">
        <div class="ai-stepper-card">
            <!-- Header -->
            <div class="ai-stepper-header">
                <div class="ai-core-icon">
                    <div class="ai-core-ring-outer"></div>
                    <div class="ai-core-ring-inner"></div>
                    <div class="ai-core-glow"></div>
                </div>
                <div class="ai-header-content">
                    <h3 class="ai-header-title">Creando y desplegando brandlift</h3>
                    <p class="ai-header-subtitle">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #eab308; flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Por favor no cierres ni recargues esta ventana para completar el proceso.
                    </p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="ai-progress-container">
                <div class="ai-progress-meta">
                    <span>Progreso del proceso</span>
                    <span id="ai-stepper-percent">0%</span>
                </div>
                <div class="ai-progress-track">
                    <div id="ai-stepper-bar-fill" class="ai-progress-fill"></div>
                </div>
            </div>

            <!-- Stepper Steps -->
            <div class="ai-stepper-list">
                <!-- Step 1 -->
                <div class="ai-step-row pending" id="ai-step-1">
                    <div class="ai-step-icon"><span>1</span></div>
                    <div class="ai-step-info">
                        <div class="ai-step-title">1. Creación en plataforma</div>
                        <div class="ai-step-desc">Configuración y guardado en Google Sheets</div>
                    </div>
                    <span class="ai-step-status-badge pending">Pendiente</span>
                </div>

                <!-- Step 2 -->
                <div class="ai-step-row pending" id="ai-step-2">
                    <div class="ai-step-icon"><span>2</span></div>
                    <div class="ai-step-info">
                        <div class="ai-step-title">2. Creativos & Backup</div>
                        <div class="ai-step-desc">Compilación HTML5 y captura de imagen de seguridad</div>
                    </div>
                    <span class="ai-step-status-badge pending">Pendiente</span>
                </div>

                <!-- Step 3 -->
                <div class="ai-step-row pending" id="ai-step-3">
                    <div class="ai-step-icon"><span>3</span></div>
                    <div class="ai-step-info">
                        <div class="ai-step-title">3. Despliegue en CM360</div>
                        <div class="ai-step-desc">Campaña, Placements, Creativos y Anuncios</div>
                    </div>
                    <span class="ai-step-status-badge pending">Pendiente</span>
                </div>

                <!-- Step 4 -->
                <div class="ai-step-row pending" id="ai-step-4">
                    <div class="ai-step-icon"><span>4</span></div>
                    <div class="ai-step-info">
                        <div class="ai-step-title">4. Tags</div>
                        <div class="ai-step-desc">Generación de tags y exportación de excel</div>
                    </div>
                    <span class="ai-step-status-badge pending">Pendiente</span>
                </div>
            </div>

            <!-- Action Button on Finish (hidden while running) -->
            <div id="ai-modal-footer-actions" style="display: none; margin-top: 18px; gap: 10px; flex-direction: column; align-items: stretch; flex-wrap: wrap;">
                <!-- Download tags button -->
                <button type="button" id="ai-modal-download-tags-btn" onclick="downloadExcelTags(window.cm360TagsData, $('#bl-market-step1').value, $('#bl-client-step1').value, $('#bl-campaign-name-step1').value); showToast('Descargando Excel de tags...');" style="background: var(--wpp-navy); color: var(--wpp-lime); border: none; padding: 11px 20px; border-radius: 12px; font-family: 'WPP', sans-serif !important; font-size: 13.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; width: 100%;">
                    <span class="material-symbols-outlined" style="font-size: 18px; font-style: normal;">download</span>
                    Descargar tags Excel
                </button>
                <!-- Send email button -->
                <button type="button" id="ai-modal-send-tags-btn" onclick="openFormSendTagsModal()" style="background: rgba(176, 244, 103, 0.15); color: var(--wpp-navy); border: 1px solid var(--wpp-lime); padding: 11px 20px; border-radius: 12px; font-family: 'WPP', sans-serif !important; font-size: 13.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; width: 100%;">
                    <span class="material-symbols-outlined" style="font-size: 18px; font-style: normal;">send</span>
                    Enviar tags por correo
                </button>
                <!-- Close as plain link -->
                <a href="{{ route('dashboard') }}" id="ai-modal-close-btn" style="display: block; text-align: center; padding: 8px; color: var(--text-muted); font-family: 'WPP', sans-serif !important; font-size: 13px; font-weight: 600; text-decoration: none; transition: color 0.2s;">
                    Finalizar e ir al dashboard →
                </a>
            </div>
        </div>
    </div>

    <!-- Send Tags Email Modal in Form -->
    <div id="send-tags-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 80, 0.7); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div class="modal-card" style="background: #ffffff; width: 100%; max-width: 560px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid rgba(0,0,80,0.1); overflow: hidden;">
            <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--wpp-navy); display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: var(--wpp-navy);">send</span>
                    Enviar tags por correo
                </h3>
                <button type="button" onclick="closeFormSendTagsModal()" style="background: none; border: none; cursor: pointer; color: #64748b; display: flex; align-items: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div style="padding: 24px;">
                <!-- View 1: Form View -->
                <div id="form-send-tags-form-view">
                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted);">Campaña</label>
                        <div id="send-tags-campaign-title" style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 4px; word-break: break-all;"></div>
                    </div>

                    <!-- Google Sheet info block -->
                    <div id="form-send-tags-sheet-box" style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #16a34a; flex-shrink: 0;">description</span>
                            <div style="font-size: 12.5px;">
                                <strong style="color: #166534; display: block; font-size: 13px;">Hoja de respuestas (Google Sheets)</strong>
                                <span style="color: #15803d; line-height: 1.4; display: block;" id="form-send-tags-sheet-desc">El enlace directo a la hoja se enviará automáticamente en el correo.</span>
                            </div>
                        </div>
                        <a id="form-send-tags-sheet-link" href="#" target="_blank" style="color: #16a34a; font-weight: 700; font-size: 12px; white-space: nowrap; text-decoration: none; padding: 4px 10px; background: rgba(34, 197, 94, 0.14); border-radius: 4px;">Abrir hoja &rarr;</a>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="send-tags-emails" style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Destinatarios (uno o varios correos separados por comas o enter) <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea id="send-tags-emails" class="form-input" rows="3" placeholder="ejemplo1@wppmedia.com, traffic@cliente.com" style="width: 100%; border-radius: var(--radius-sm); font-size: 13px; padding: 10px; resize: vertical; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="send-tags-message" style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Mensaje personalizado de indicaciones y recomendaciones
                        </label>
                        <textarea id="send-tags-message" class="form-input" rows="4" style="width: 100%; border-radius: var(--radius-sm); font-size: 13px; padding: 10px; resize: vertical; box-sizing: border-box;">Estimado equipo,

Adjuntamos el archivo Excel con los tags de tráfico y especificaciones técnicas para la implementación de la campaña BrandLift, junto con el enlace a la hoja de Google Sheets donde se recibirán las respuestas.

Por favor verificar la correcta implementación antes del inicio de la pauta.</textarea>
                    </div>

                    <div style="background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: var(--radius-sm); padding: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span class="material-symbols-outlined" style="font-size: 24px; color: #16a34a;">table_chart</span>
                        <div style="font-size: 12px;">
                            <strong style="color: var(--text-primary);">Archivo adjunto automático:</strong>
                            <div style="color: var(--text-muted);" id="send-tags-attachment-name">Tags_BrandLift.xls (Formato Excel)</div>
                        </div>
                    </div>

                    <!-- Inline Error Alert in Modal -->
                    <div id="form-send-tags-error-alert" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 16px; color: #b91c1c; font-size: 12.5px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">error</span>
                        <span id="form-send-tags-error-text"></span>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="btn-cancel-send-tags-form" onclick="closeFormSendTagsModal()" style="padding: 10px 18px; border-radius: var(--radius-sm); border: 1px solid var(--border-input); background: transparent; cursor: pointer; font-size: 13px; font-weight: 600;">Cancelar</button>
                        <button type="button" id="btn-submit-send-tags-form" onclick="submitFormSendTags()" style="padding: 10px 20px; border-radius: var(--radius-sm); border: none; background: var(--wpp-navy); color: var(--wpp-lime); cursor: pointer; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                            <span>Enviar tags</span>
                        </button>
                    </div>
                </div>

                <!-- View 2: Success Notification View inside same modal -->
                <div id="form-send-tags-success-view" style="display: none; text-align: center; padding: 16px 8px 8px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">check_circle</span>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 8px 0;">¡Tags y enlace enviados exitosamente!</h3>
                    <p style="font-size: 13.5px; color: var(--text-secondary); margin: 0 0 16px 0; line-height: 1.5;">
                        Se ha enviado el correo con los tags en Excel y el enlace a la hoja de Google Sheets a los siguientes destinatarios:
                    </p>
                    <div id="form-send-tags-success-recipients" style="background: var(--bg-secondary); border: 1px solid var(--border-input); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 24px; font-size: 12.5px; color: var(--text-primary); text-align: left; max-height: 120px; overflow-y: auto;">
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <button type="button" onclick="closeFormSendTagsModal()" style="padding: 10px 28px; border-radius: var(--radius-sm); border: none; background: var(--wpp-navy); color: #ffffff; cursor: pointer; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                            <span>Cerrar modal</span>
                        </button>
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

    function initAIStepper() {
        const footerActions = $('#ai-modal-footer-actions');
        if (footerActions) footerActions.style.display = 'none';

        setAIStep(1, 'active', 'Iniciando proceso de creación en la plataforma...');
        setAIStep(2, 'pending');
        setAIStep(3, 'pending');
        setAIStep(4, 'pending');
        updateAIProgress(10);
        $('#full-loading-overlay').classList.add('active');
    }

    function updateAIProgress(percent) {
        const bar = $('#ai-stepper-bar-fill');
        const label = $('#ai-stepper-percent');
        if (bar) bar.style.width = Math.min(100, Math.max(0, percent)) + '%';
        if (label) label.innerText = Math.round(percent) + '%';
    }

    function setAIStep(stepNum, status, liveMsg = '') {
        const stepEl = $(`#ai-step-${stepNum}`);
        if (!stepEl) return;

        stepEl.classList.remove('pending', 'active', 'completed', 'error');
        stepEl.classList.add(status);

        const iconEl = stepEl.querySelector('.ai-step-icon');
        const badgeEl = stepEl.querySelector('.ai-step-status-badge');

        if (status === 'active') {
            if (iconEl) iconEl.innerHTML = `<div class="ai-spinner-sm"></div>`;
            if (badgeEl) { badgeEl.innerText = 'En curso'; badgeEl.className = 'ai-step-status-badge active'; }
        } else if (status === 'completed') {
            if (iconEl) iconEl.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>`;
            if (badgeEl) { badgeEl.innerText = 'Completado'; badgeEl.className = 'ai-step-status-badge completed'; }
        } else if (status === 'error') {
            if (iconEl) iconEl.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
            if (badgeEl) { badgeEl.innerText = 'Error'; badgeEl.className = 'ai-step-status-badge error'; }
        } else {
            if (iconEl) iconEl.innerHTML = `<span>${stepNum}</span>`;
            if (badgeEl) { badgeEl.innerText = 'Pendiente'; badgeEl.className = 'ai-step-status-badge pending'; }
        }

        if (liveMsg) {
            const msgEl = $('#ai-live-message');
            if (msgEl) {
                msgEl.style.opacity = '0';
                setTimeout(() => {
                    msgEl.innerText = liveMsg;
                    msgEl.style.opacity = '1';
                }, 150);
            }
        }
    }

    function addLoadingMessage(msg) {
        const msgEl = $('#ai-live-message');
        if (msgEl) {
            msgEl.innerText = msg;
        }
    }



    function openFormSendTagsModal() {
        const id = state.studyId;
        const campaignName = $('#bl-campaign-name-step1')?.value || 'BrandLift';
        $('#send-tags-campaign-title').textContent = campaignName;
        const cleanName = campaignName.replace(/[^A-Za-z0-9_\-]/g, '_');
        $('#send-tags-attachment-name').textContent = `Tags_BrandLift_${cleanName}.xls (Formato Excel)`;

        // Google Sheet box
        const sheetBox = $('#form-send-tags-sheet-box');
        const sheetLink = $('#form-send-tags-sheet-link');
        const sheetDesc = $('#form-send-tags-sheet-desc');
        const sheetId = state.sheetId || window.EDIT_STUDY_DATA?.sheet_id || ($('#bl-sheet-id')?.value?.trim() || '');
        const sheetUrl = window.EDIT_STUDY_DATA?.google_sheet_url || (sheetId ? `https://docs.google.com/spreadsheets/d/${sheetId}/edit` : '');

        if (sheetBox) {
            sheetBox.style.display = 'flex';
            if (sheetUrl) {
                sheetDesc.textContent = 'El enlace directo a la hoja de Google Sheets se incluirá automáticamente en el correo.';
                if (sheetLink) {
                    sheetLink.href = sheetUrl;
                    sheetLink.style.display = 'inline-block';
                }
            } else {
                sheetDesc.textContent = 'Nota: Este estudio aún no tiene vinculada una hoja de respuestas de Google Sheets.';
                if (sheetLink) {
                    sheetLink.style.display = 'none';
                }
            }
        }

        // Reset views and controls
        const formView = $('#form-send-tags-form-view');
        const successView = $('#form-send-tags-success-view');
        const errorAlert = $('#form-send-tags-error-alert');

        if (formView) formView.style.display = 'block';
        if (successView) successView.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';

        const btn = $('#btn-submit-send-tags-form');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px;">send</span><span>Enviar tags</span>';
        }

        const btnCancel = $('#btn-cancel-send-tags-form');
        if (btnCancel) {
            btnCancel.disabled = false;
            btnCancel.style.opacity = '1';
            btnCancel.style.cursor = 'pointer';
        }

        const emailsInput = $('#send-tags-emails');
        const msgInput = $('#send-tags-message');
        if (emailsInput) emailsInput.disabled = false;
        if (msgInput) msgInput.disabled = false;

        $('#send-tags-modal').style.display = 'flex';
    }

    function closeFormSendTagsModal() {
        $('#send-tags-modal').style.display = 'none';
    }

    async function submitFormSendTags() {
        const id = state.studyId;
        if (!id) {
            showToast('No se encontró el ID del estudio creado', true);
            return;
        }

        const emails = $('#send-tags-emails').value.trim();
        const message = $('#send-tags-message').value.trim();

        const errorAlert = $('#form-send-tags-error-alert');
        const errorText = $('#form-send-tags-error-text');
        if (errorAlert) errorAlert.style.display = 'none';

        if (!emails) {
            if (errorText && errorAlert) {
                errorText.textContent = 'Por favor ingresa al menos un correo electrónico destinatario.';
                errorAlert.style.display = 'block';
            }
            showToast('Por favor ingresa al menos un correo destinatario', true);
            $('#send-tags-emails').focus();
            return;
        }

        const btn = $('#btn-submit-send-tags-form');
        const btnCancel = $('#btn-cancel-send-tags-form');
        const emailsInput = $('#send-tags-emails');
        const messageInput = $('#send-tags-message');

        // Bloquear botón y controles durante el envío
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px; animation: spin 1s linear infinite;">progress_activity</span><span>Enviando tags...</span>';
        }
        if (btnCancel) {
            btnCancel.disabled = true;
            btnCancel.style.opacity = '0.5';
            btnCancel.style.cursor = 'not-allowed';
        }
        if (emailsInput) emailsInput.disabled = true;
        if (messageInput) messageInput.disabled = true;

        try {
            const res = await fetch(`/api/brandlift/${id}/send-tags`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ emails, message })
            });

            const data = await res.json();
            if (data.success) {
                // Notificación de envío en la misma modal
                const formView = $('#form-send-tags-form-view');
                const successView = $('#form-send-tags-success-view');

                if (formView) formView.style.display = 'none';
                if (successView) successView.style.display = 'block';

                const recipients = data.recipients || emails.split(/[\s,;]+/).filter(Boolean);
                const recipientsHtml = recipients.map(e => `
                    <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; margin: 3px 4px 3px 0;">
                        ${escapeHtml(e)}
                    </span>
                `).join('');

                const recipientsContainer = $('#form-send-tags-success-recipients');
                if (recipientsContainer) {
                    recipientsContainer.innerHTML = recipientsHtml;
                }

                if (emailsInput) emailsInput.value = '';
                showToast(data.message || 'Tags enviados exitosamente');
            } else {
                if (errorText && errorAlert) {
                    errorText.textContent = data.message || 'Error al enviar los tags. Por favor intenta de nuevo.';
                    errorAlert.style.display = 'block';
                }
                showToast(data.message || 'Error al enviar tags', true);

                if (btn) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px;">send</span><span>Reintentar envío</span>';
                }
                if (btnCancel) {
                    btnCancel.disabled = false;
                    btnCancel.style.opacity = '1';
                    btnCancel.style.cursor = 'pointer';
                }
                if (emailsInput) emailsInput.disabled = false;
                if (messageInput) messageInput.disabled = false;
            }
        } catch (e) {
            console.error(e);
            if (errorText && errorAlert) {
                errorText.textContent = 'Error de conexión al enviar el correo. Por favor verifica tu red e intenta de nuevo.';
                errorAlert.style.display = 'block';
            }
            showToast('Error de conexión al enviar el correo', true);

            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px;">send</span><span>Reintentar envío</span>';
            }
            if (btnCancel) {
                btnCancel.disabled = false;
                btnCancel.style.opacity = '1';
                btnCancel.style.cursor = 'pointer';
            }
            if (emailsInput) emailsInput.disabled = false;
            if (messageInput) messageInput.disabled = false;
        }
    }

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
        groups: [],
        tagTypes: ['Ad_Exposed', 'Control'],
        dpsSelections: [],
        totalVariants: 0,
        creationCompleted: false
    };

    // ===== WIZARD NAVIGATION =====
    function goToStep(stepNum, animate = true, shouldScrollTop = true) {
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
        $$('.ruixen-step-item, .step-item').forEach((item, i) => {
            const step = i + 1;
            item.classList.remove('active', 'completed', 'pending');
            if (step === stepNum) {
                item.classList.add('active');
            } else if (step < stepNum) {
                item.classList.add('completed');
            } else {
                item.classList.add('pending');
            }
        });

        // Update connecting lines
        for (let i = 1; i < state.totalSteps; i++) {
            const line = $(`#step-line-${i}`);
            if (line) {
                line.classList.toggle('filled', i < stepNum);
            }
            const ruixenConn = $(`#ruixen-connector-${i}`);
            if (ruixenConn) {
                ruixenConn.classList.toggle('filled', i < stepNum);
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

        // Scroll form card to top if requested
        if (shouldScrollTop) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function goToField(stepNum, target) {
        const isStepChange = state.currentStep !== stepNum;
        if (isStepChange) {
            goToStep(stepNum, false, false);
        }

        setTimeout(() => {
            let el = null;
            if (target === 'campaign') {
                el = $('#bl-campaign-name-step1');
            } else if (target === 'audiences' || target === 'dps') {
                const groupInput = $('.group-name-input');
                el = groupInput || $('#btn-add-group') || $('#dps-dv360');
            } else if (target === 'questions') {
                const qVal = validateQuestionsAndAnswers(false);
                el = (qVal && qVal.firstInvalidTarget) ? $(qVal.firstInvalidTarget) : $('#bl-question-1');
            } else if (target === 'creatives') {
                el = $('#preview-frame') || $('#step-2-actions');
            } else if (typeof target === 'string') {
                el = $(target);
            }

            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof el.focus === 'function') {
                    try { el.focus(); } catch (e) {}
                }
                const highlightTarget = (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT')
                    ? el
                    : (el.closest('.form-group') || el.closest('.groups-tag-wrapper') || el);
                highlightTarget.classList.remove('field-focus-highlight');
                void highlightTarget.offsetWidth;
                highlightTarget.classList.add('field-focus-highlight');
                setTimeout(() => highlightTarget.classList.remove('field-focus-highlight'), 1800);
            }
        }, isStepChange ? 350 : 50);
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
    $$('.ruixen-step-item, .step-item').forEach(item => {
        item.addEventListener('click', () => {
            const target = parseInt(item.dataset.step);
            if (state.currentStep === 1 && target > 1) {
                const groupValidation = validateGroups(true);
                if (!groupValidation.isValid) {
                    showToast(groupValidation.hasEmpty ? 'No se permiten grupos de audiencia con nombres vacíos' : 'No se pueden repetir los nombres de los grupos de audiencia', true);
                    goToField(1, 'audiences');
                    return;
                }
            }
            // Only allow clicking completed steps or the next available step
            if (target <= state.currentStep || (target === state.currentStep + 1 && isStepComplete(state.currentStep))) {
                goToStep(target);
            }
        });
    });

    // ===== BUTTON NAVIGATION =====
    $('#btn-next-1').addEventListener('click', () => {
        const groupValidation = validateGroups(true);
        if (!groupValidation.isValid) {
            if (groupValidation.hasEmpty && groupValidation.hasDuplicates) {
                showToast('No se permiten grupos de audiencia vacíos ni repetidos', true);
            } else if (groupValidation.hasEmpty) {
                showToast('No se permiten grupos de audiencia con nombres vacíos', true);
            } else {
                showToast('No se pueden repetir los nombres de los grupos de audiencia', true);
            }
            goToField(1, 'audiences');
            return;
        }
        const endDate = $('#bl-end-date-step1').value;
        const today = new Date().toLocaleDateString('en-CA');
        if (endDate && endDate < today) {
            showToast('La fecha de finalización no puede ser una fecha pasada', true);
            return;
        }
        goToStep(2);
    });
    $('#btn-back-2').addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); goToStep(1); });

    // ===== VALIDATE QUESTIONS AND ANSWERS =====
    function validateQuestionsAndAnswers(showErrors = false) {
        let isValid = true;
        let errorMessage = '';
        let firstInvalidTarget = null;
        const questionDetails = {};

        // 1. Detect duplicate questions across active questions
        const questionCounts = {};
        const duplicateQuestions = new Set();
        for (let i = 1; i <= state.questionCount; i++) {
            const qInput = $(`#bl-question-${i}`);
            const qText = qInput ? qInput.value.trim().toLowerCase() : '';
            if (qText) {
                questionCounts[qText] = (questionCounts[qText] || 0) + 1;
                if (questionCounts[qText] > 1) {
                    duplicateQuestions.add(qText);
                }
            }
        }

        // 2. Validate each question & its options
        for (let i = 1; i <= state.questionCount; i++) {
            const qInput = $(`#bl-question-${i}`);
            const rawQText = qInput ? qInput.value.trim() : '';
            const lowerQText = rawQText.toLowerCase();
            const qWarning = $(`#q-duplicate-warning-${i}`);

            const hasDuplicateQ = Boolean(lowerQText && duplicateQuestions.has(lowerQText));
            const hasEmptyQ = !rawQText;

            if (qWarning) {
                qWarning.style.display = hasDuplicateQ ? 'flex' : 'none';
            }

            if (qInput) {
                if (hasDuplicateQ) {
                    qInput.classList.add('input-error');
                    qInput.classList.remove('valid');
                    isValid = false;
                    if (!errorMessage) {
                        errorMessage = 'No se pueden repetir las preguntas';
                        firstInvalidTarget = `#bl-question-${i}`;
                    }
                } else if (hasEmptyQ) {
                    isValid = false;
                    qInput.classList.remove('valid');
                    if (showErrors) {
                        qInput.classList.add('input-error');
                    } else {
                        qInput.classList.remove('input-error');
                    }
                    if (!errorMessage) {
                        errorMessage = `Completa el texto de la Pregunta ${i}`;
                        firstInvalidTarget = `#bl-question-${i}`;
                    }
                } else {
                    qInput.classList.remove('input-error');
                    qInput.classList.add('valid');
                }
            }

            // Answers validation for Question i
            const answerInputs = [...$$(`#answers-container-${i} .answer-input`)];
            const aWarning = $(`#answers-warning-${i}`);

            const answerCounts = {};
            const duplicateAnswers = new Set();
            let hasEmptyA = false;

            answerInputs.forEach(input => {
                const val = input.value.trim().toLowerCase();
                if (val) {
                    answerCounts[val] = (answerCounts[val] || 0) + 1;
                    if (answerCounts[val] > 1) {
                        duplicateAnswers.add(val);
                    }
                } else {
                    hasEmptyA = true;
                }
            });

            const hasDuplicateA = duplicateAnswers.size > 0;
            const hasFewA = answerInputs.length < 2;

            answerInputs.forEach(input => {
                const rawVal = input.value.trim();
                const lowerVal = rawVal.toLowerCase();
                const isDup = Boolean(lowerVal && duplicateAnswers.has(lowerVal));
                const isEmp = !rawVal;

                if (isDup) {
                    input.classList.add('input-error');
                    input.classList.remove('valid');
                } else if (isEmp) {
                    input.classList.remove('valid');
                    if (showErrors) {
                        input.classList.add('input-error');
                    } else {
                        input.classList.remove('input-error');
                    }
                } else {
                    input.classList.remove('input-error');
                    input.classList.add('valid');
                }
            });

            if (aWarning) {
                const warnText = aWarning.querySelector('.warning-text');
                if (hasDuplicateA) {
                    if (warnText) warnText.textContent = `Las opciones de respuesta no pueden ser iguales en la Pregunta ${i}`;
                    aWarning.style.display = 'flex';
                } else if (showErrors && hasEmptyA) {
                    if (warnText) warnText.textContent = `No se permiten opciones de respuesta vacías en la Pregunta ${i}`;
                    aWarning.style.display = 'flex';
                } else if (showErrors && hasFewA) {
                    if (warnText) warnText.textContent = `La Pregunta ${i} debe tener al menos 2 opciones de respuesta`;
                    aWarning.style.display = 'flex';
                } else {
                    aWarning.style.display = 'none';
                }
            }

            if (hasFewA) {
                isValid = false;
                if (!errorMessage) {
                    errorMessage = `La Pregunta ${i} debe tener al menos 2 opciones de respuesta`;
                    firstInvalidTarget = `#answers-container-${i}`;
                }
            }
            if (hasDuplicateA) {
                isValid = false;
                if (!errorMessage) {
                    errorMessage = `Las opciones de respuesta no pueden ser iguales en la Pregunta ${i}`;
                    firstInvalidTarget = `#answers-container-${i}`;
                }
            }
            if (hasEmptyA) {
                isValid = false;
                if (!errorMessage && showErrors) {
                    errorMessage = `No se permiten respuestas vacías en la Pregunta ${i}`;
                    firstInvalidTarget = `#answers-container-${i}`;
                }
            }

            questionDetails[i] = {
                text: rawQText,
                hasDuplicateQ,
                hasEmptyQ,
                hasDuplicateA,
                hasEmptyA,
                hasFewA,
                answersCount: answerInputs.length,
                validAnswersCount: answerInputs.filter(inp => inp.value.trim() && !duplicateAnswers.has(inp.value.trim().toLowerCase())).length,
                isValid: !hasDuplicateQ && !hasEmptyQ && !hasDuplicateA && !hasEmptyA && !hasFewA
            };
        }

        // Enable / disable Color del brandlift selector
        const themeContainer = $('#theme-selector-container');
        const themeHint = $('#theme-disabled-hint');
        if (themeContainer) {
            if (isValid) {
                themeContainer.classList.remove('disabled');
                themeContainer.style.opacity = '1';
                themeContainer.style.pointerEvents = 'auto';
                if (themeHint) themeHint.style.display = 'none';
            } else {
                themeContainer.classList.add('disabled');
                themeContainer.style.opacity = '0.45';
                themeContainer.style.pointerEvents = 'none';
                if (themeHint) themeHint.style.display = 'block';
            }
        }

        return { isValid, errorMessage, firstInvalidTarget, questionDetails };
    }

    // ===== DYNAMIC ANSWERS MANAGEMENT =====
    function addAnswerInput(qNum, initialValue = '') {
        const container = $(`#answers-container-${qNum}`);
        if (!container) return;

        const currentAnswers = container.querySelectorAll('.answer-item');
        if (currentAnswers.length >= 6) {
            showToast('Máximo 6 opciones de respuesta por pregunta', true);
            return;
        }

        const newIndex = currentAnswers.length + 1;
        const item = document.createElement('div');
        item.className = 'answer-item';
        item.dataset.index = newIndex;

        const removeBtnHtml = newIndex > 2 ? `
            <button type="button" class="btn-remove-answer" title="Eliminar opción" onclick="removeAnswerInput(${qNum}, this)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        ` : '';

        item.innerHTML = `
            <span class="answer-number">${newIndex}</span>
            <input type="text" class="form-input answer-input"
                placeholder="Respuesta ${newIndex}"
                data-question="${qNum}" data-index="${newIndex}"
                value="${escapeSummaryHtml(initialValue)}" required />
            ${removeBtnHtml}
        `;

        container.appendChild(item);

        const input = item.querySelector('.answer-input');
        input.addEventListener('input', () => {
            validateQuestionsAndAnswers(false);
            checkStepCompletion(qNum);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const allInputs = [...container.querySelectorAll('.answer-input')];
                const currentIndex = allInputs.indexOf(input);
                if (currentIndex < allInputs.length - 1) {
                    allInputs[currentIndex + 1].focus();
                } else if (allInputs.length < 6 && input.value.trim()) {
                    addAnswerInput(qNum);
                    const updatedInputs = [...container.querySelectorAll('.answer-input')];
                    if (updatedInputs[currentIndex + 1]) {
                        updatedInputs[currentIndex + 1].focus();
                    }
                }
            }
        });

        updateAddAnswerButtonState(qNum);
        validateQuestionsAndAnswers(false);
        checkStepCompletion(qNum);
        updateViewportHeight();
    }

    window.removeAnswerInput = function(qNum, btnEl) {
        const item = btnEl.closest('.answer-item');
        if (item) {
            const container = $(`#answers-container-${qNum}`);
            item.remove();
            const items = container.querySelectorAll('.answer-item');
            items.forEach((it, idx) => {
                const num = idx + 1;
                it.dataset.index = num;
                it.querySelector('.answer-number').textContent = num;
                const inp = it.querySelector('.answer-input');
                inp.placeholder = `Respuesta ${num}`;
                inp.dataset.index = num;
                const rmBtn = it.querySelector('.btn-remove-answer');
                if (num <= 2 && rmBtn) {
                    rmBtn.remove();
                }
            });
            updateAddAnswerButtonState(qNum);
            validateQuestionsAndAnswers(false);
            checkStepCompletion(qNum);
            updateViewportHeight();
        }
    };

    function updateAddAnswerButtonState(qNum) {
        const container = $(`#answers-container-${qNum}`);
        const btn = $(`#btn-add-answer-${qNum}`);
        if (container && btn) {
            const count = container.querySelectorAll('.answer-item').length;
            if (count >= 6) {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'inline-flex';
            }
        }
    }

    function initQuestionAnswers(qNum) {
        const container = $(`#answers-container-${qNum}`);
        if (!container) return;
        container.innerHTML = '';
        addAnswerInput(qNum, '');
        addAnswerInput(qNum, '');
    }

    // Initialize all 4 questions with 2 answers each
    for (let i = 1; i <= 4; i++) {
        initQuestionAnswers(i);
        const qInput = $(`#bl-question-${i}`);
        if (qInput) {
            qInput.addEventListener('input', () => {
                validateQuestionsAndAnswers(false);
                checkStepCompletion(i);
            });
        }
        const btnAdd = $(`#btn-add-answer-${i}`);
        if (btnAdd) {
            btnAdd.addEventListener('click', () => {
                addAnswerInput(i);
                const container = $(`#answers-container-${i}`);
                const inputs = container.querySelectorAll('.answer-input');
                const lastInput = inputs[inputs.length - 1];
                if (lastInput) lastInput.focus();
            });
        }
    }

    // ===== CHECK STEP COMPLETION =====
    function isStepComplete(stepNum) {
        if (stepNum === 1) {
            const market = $('#bl-market-step1').value;
            const client = $('#bl-client-step1').value.trim();
            const endDate = $('#bl-end-date-step1').value;
            const campaign = $('#bl-campaign-name-step1').value.trim();
            const investment = $('#bl-investment-step1').value.trim();
            const profileId = $('#cm360-profile-id').value.trim();
            const advertiserId = $('#cm360-advertiser-id').value.trim();
            const today = new Date().toLocaleDateString('en-CA');
            const isDateValid = endDate && (endDate >= today || !!state.studyId);
            return market && state.dpsSelections.length > 0 && client && isDateValid && campaign && investment && profileId && advertiserId && !hasGroupErrors();
        }
        if (stepNum === 2) {
            return validateQuestionsAndAnswers(false).isValid;
        }
        return true;
    }

    function checkStepCompletion(questionNum) {
        if (state.creationCompleted) return;
        const complete = isStepComplete(2);
        const btnNext = $(`#btn-create`);

        if (btnNext) {
            btnNext.disabled = !complete;
        }

        // Update summaries live
        populateSummary();
        validateCurrentStep();
    }

    function validateCurrentStep() {
        if (state.creationCompleted && state.currentStep === 2) return;
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
    $('#bl-end-date-step1').addEventListener('input', populateSummary);

    // ===== INVESTMENT FORMATTING =====
    const investmentInput = $('#bl-investment-step1');
    investmentInput.addEventListener('input', function(e) {
        // Remove non-numeric characters except dots
        let value = this.value.replace(/[^0-9.]/g, '');

        // Handle multiple dots (keep only the first one)
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Check 1 million limit
        let numValue = parseFloat(value) || 0;
        if (numValue > 1000000) {
            value = '1000000';
        }

        // Format to US currency (with commas)
        if (value) {
            const splitValue = value.split('.');
            splitValue[0] = splitValue[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            // Allow up to 2 decimal places if dot is present
            if (splitValue.length > 1) {
                splitValue[1] = splitValue[1].substring(0, 2);
                this.value = splitValue.join('.');
            } else {
                this.value = splitValue[0];
            }
        } else {
            this.value = '';
        }

        populateSummary();
        checkStepCompletion(1);
    });

    // Handle blur to add .00 if needed
    investmentInput.addEventListener('blur', function(e) {
        let value = this.value.replace(/,/g, '');
        let numValue = parseFloat(value);
        if (!isNaN(numValue)) {
            this.value = numValue.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        populateSummary();
    });

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
    function validateGroups(showEmptyErrors = false) {
        const groupInputs = $$('.group-name-input');
        const counts = {};
        const duplicateNames = new Set();
        let hasEmpty = false;

        groupInputs.forEach(input => {
            const val = input.value.trim().toLowerCase();
            if (!val) {
                hasEmpty = true;
            } else {
                counts[val] = (counts[val] || 0) + 1;
                if (counts[val] > 1) {
                    duplicateNames.add(val);
                }
            }
        });

        let hasDuplicates = false;
        groupInputs.forEach(input => {
            const rawVal = input.value.trim();
            const val = rawVal.toLowerCase();
            const groupItem = input.closest('.group-item');

            const isDup = val && duplicateNames.has(val);
            const isEmp = !rawVal;

            if (groupItem) {
                if (isDup) {
                    hasDuplicates = true;
                    groupItem.classList.add('duplicate-error');
                    groupItem.classList.remove('empty-error');
                } else if (isEmp && showEmptyErrors) {
                    groupItem.classList.add('empty-error');
                    groupItem.classList.remove('duplicate-error');
                } else {
                    groupItem.classList.remove('duplicate-error', 'empty-error');
                }
            }
        });

        const warningEl = $('#groups-duplicate-warning');
        if (warningEl) {
            const span = warningEl.querySelector('span');
            if (hasDuplicates && (hasEmpty && showEmptyErrors)) {
                warningEl.style.display = 'flex';
                if (span) span.textContent = 'No se permiten grupos de audiencia vacíos ni con nombres repetidos.';
            } else if (hasDuplicates) {
                warningEl.style.display = 'flex';
                if (span) span.textContent = 'No se pueden repetir los nombres de los grupos de audiencia.';
            } else if (hasEmpty && showEmptyErrors) {
                warningEl.style.display = 'flex';
                if (span) span.textContent = 'No se permiten grupos de audiencia con nombres vacíos.';
            } else {
                warningEl.style.display = 'none';
            }
        }

        return { hasDuplicates, hasEmpty, isValid: !hasDuplicates && !hasEmpty };
    }

    function hasGroupErrors() {
        const res = validateGroups(false);
        return !res.isValid;
    }

    function updateVariantCount(showEmptyErrors = false) {
        const tagTypes = ['Ad_Exposed', 'Control'];
        state.tagTypes = tagTypes;

        const dpsSelections = [];
        $$('.dps-checkbox').forEach(cb => {
            if (cb.checked) dpsSelections.push(cb.value);
        });
        state.dpsSelections = dpsSelections;

        const groupInputs = $$('.group-name-input');
        state.groups = [...groupInputs].map(input => ({ name: input.value.trim() }));

        validateGroups(showEmptyErrors);

        state.totalVariants = dpsSelections.length > 0 ? Math.max(1, state.groups.length) * tagTypes.length * dpsSelections.length : 0;
        populateSummary();
        updateViewportHeight();
    }

    function setupGroupItem(groupItem) {
        const input = groupItem.querySelector('.group-name-input');
        const removeBtn = groupItem.querySelector('.btn-remove-group');

        const updateWidth = () => {
            const len = Math.max(10, (input.value || input.placeholder || '').length + 2);
            input.size = len;
        };

        input.addEventListener('input', () => {
            updateWidth();
            updateVariantCount(false);
            validateCurrentStep();
        });
        updateWidth();

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                groupItem.remove();
                $$('.group-item').forEach((item, i) => {
                    item.dataset.groupIndex = i;
                });
                updateVariantCount(false);
                validateCurrentStep();
            });
        }
    }

    // Add group button
    $('#btn-add-group').addEventListener('click', () => {
        const emptyInput = [...$$('.group-name-input')].find(i => !i.value.trim());
        if (emptyInput) {
            emptyInput.focus();
            validateGroups(true);
            return;
        }

        const container = $('#groups-container');
        const index = container.querySelectorAll('.group-item').length;
        const groupItem = document.createElement('div');
        groupItem.className = 'group-item';
        groupItem.dataset.groupIndex = index;
        groupItem.innerHTML = `
            <input type="text" class="group-name-input" placeholder="Nombre de audiencia..." value="" size="14">
            <button type="button" class="btn-remove-group" title="Eliminar audiencia"><span class="material-symbols-outlined" style="font-size:16px;">close</span></button>
        `;
        container.appendChild(groupItem);
        setupGroupItem(groupItem);

        updateVariantCount(false);
        groupItem.querySelector('.group-name-input').focus();
    });

    // Initialize existing group items
    $$('.group-item').forEach(setupGroupItem);

    // Allow Enter key to automatically add a new group input or trigger warning if empty
    $('#groups-container').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const emptyInput = [...$$('.group-name-input')].find(i => !i.value.trim());
            if (emptyInput) {
                emptyInput.focus();
                validateGroups(true);
            } else {
                $('#btn-add-group').click();
            }
        }
    });

    // ===== POPULATE SUMMARY (Modern Tracking Stepper) =====
    function escapeSummaryHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    function populateSummary() {
        const widgetContainer = $('#summary-widget-content');
        const summaryWidget = $('#summary-widget');
        const stepperBadge = $('#summary-stepper-badge');

        if (!widgetContainer || !summaryWidget) return;

        summaryWidget.style.display = 'block';

        if (stepperBadge) {
            if (state.creativesGenerated) {
                stepperBadge.textContent = 'Completado';
                stepperBadge.style.background = 'rgba(176, 244, 103, 0.25)';
                stepperBadge.style.color = 'var(--wpp-navy)';
            } else {
                stepperBadge.textContent = `Paso ${state.currentStep} de ${state.totalSteps}`;
                stepperBadge.style.background = 'rgba(0, 0, 80, 0.05)';
                stepperBadge.style.color = 'var(--wpp-navy)';
            }
        }

        // --- Step 1 Data ---
        const market = $('#bl-market-step1') ? $('#bl-market-step1').value : '';
        const dpsList = state.dpsSelections || [];
        const dps = dpsList.join(', ');
        const client = $('#bl-client-step1') ? $('#bl-client-step1').value.trim() : '';
        const endDate = $('#bl-end-date-step1') ? $('#bl-end-date-step1').value : '';
        const campaign = $('#bl-campaign-name-step1') ? $('#bl-campaign-name-step1').value.trim() : '';
        const investment = $('#bl-investment-step1') ? $('#bl-investment-step1').value.trim() : '';

        let clickEventSummary = 'Desactivado';
        const hasClickDPS = dpsList.some(d => ['TTD', 'Sonata', 'Amazon'].includes(d));
        if (hasClickDPS) {
            clickEventSummary = 'Activado';
        }

        // Formatted investment
        let formattedInvestment = '';
        if (investment) {
            const rawNum = parseFloat(investment.replace(/[^0-9.]/g, ''));
            if (!isNaN(rawNum) && rawNum > 0) {
                formattedInvestment = '$' + rawNum.toLocaleString('es-CO');
            } else {
                formattedInvestment = '$' + investment;
            }
        }

        // Formatted date (e.g. 2026-10-15 -> 15 Oct)
        let formattedDate = '';
        if (endDate) {
            try {
                const parts = endDate.split('-');
                if (parts.length === 3) {
                    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    const monthIdx = parseInt(parts[1], 10) - 1;
                    if (monthIdx >= 0 && monthIdx < 12) {
                        formattedDate = `${parseInt(parts[2], 10)} ${months[monthIdx]}`;
                    }
                }
            } catch (e) {
                formattedDate = endDate;
            }
        }

        // --- Questions Data & Validation ---
        const qVal = validateQuestionsAndAnswers(false);
        const qDetails = qVal.questionDetails || {};
        const hasQDuplicates = Object.values(qDetails).some(qd => qd.hasDuplicateA || qd.hasDuplicateQ);
        const hasQIncomplete = Object.values(qDetails).some(qd => qd.hasEmptyQ || qd.hasEmptyA || qd.hasFewA);
        const allQValid = Object.keys(qDetails).length === state.questionCount && Object.values(qDetails).every(qd => qd.isValid);

        // === NODE 1: Campaña y Anunciante ===
        const isStep1BasicDone = Boolean(campaign && client && market && endDate);
        let node1State = 'is-active';
        if (state.currentStep > 1) {
            node1State = 'is-completed';
        } else if (isStep1BasicDone) {
            node1State = 'is-completed';
        }

        const node1Title = campaign ? escapeSummaryHtml(campaign) : 'Campaña y Anunciante';
        const node1SubParts = [];
        node1SubParts.push(client ? escapeSummaryHtml(client) : 'Sin anunciante');
        if (market) node1SubParts.push(escapeSummaryHtml(market));
        node1SubParts.push(formattedInvestment ? formattedInvestment : 'Sin inversión');
        const node1Desc = node1SubParts.join(' · ');
        const node1Meta = formattedDate || 'Paso 1';

        // === NODE 2: DPS y Segmentación ===
        const hasDPS = dpsList.length > 0;
        const validGroups = (state.groups || []).filter(g => g.name && g.name.trim());
        let node2State = 'is-pending';
        if (state.currentStep > 1) {
            node2State = 'is-completed';
        } else if (hasDPS) {
            node2State = 'is-completed';
        } else if (isStep1BasicDone) {
            node2State = 'is-active';
        }

        const node2Title = 'DSP y Segmentación';
        let node2Desc = '';
        if (hasDPS) {
            const dpsText = escapeSummaryHtml(dps);
            const audText = validGroups.length > 0
                ? `${validGroups.length} audiencia(s): ${validGroups.map(g => escapeSummaryHtml(g.name)).join(', ')}`
                : 'Audiencia general';
            node2Desc = `${dpsText} · ${audText}`;
        } else {
            node2Desc = 'Plataformas (TTD, DV360, etc.) y grupos de audiencia';
        }
        const node2Meta = validGroups.length > 0 ? `${validGroups.length} aud.` : (hasDPS ? dpsList[0] : 'Audiencias');

        // === NODE 3: Preguntas del Brandlift ===
        let node3State = 'is-pending';
        let node3Meta = `${state.questionCount} preg.`;

        if (state.creativesGenerated) {
            node3State = 'is-completed';
            node3Meta = `${state.questionCount} preg.`;
        } else if (hasQDuplicates) {
            node3State = 'is-error';
            node3Meta = `<span style="color:var(--danger-red, #ef4444); font-weight:700; display:inline-flex; align-items:center; gap:2px;"><span class="material-symbols-outlined" style="font-size:14px;">warning</span> Respuestas repetidas</span>`;
        } else if (allQValid) {
            node3State = 'is-completed';
            node3Meta = `${state.questionCount} preg. completas`;
        } else if (state.currentStep === 2) {
            node3State = 'is-active';
            node3Meta = `Paso 2 (${state.questionCount} preg.)`;
        } else {
            node3State = 'is-pending';
            node3Meta = `${state.questionCount} preg.`;
        }

        const node3Title = 'Preguntas del Brandlift';
        let node3Desc = '';
        const qItems = [];

        for (let i = 1; i <= state.questionCount; i++) {
            const qd = qDetails[i];
            const text = qd && qd.text ? qd.text : '';
            const shortText = text.length > 35 ? text.substring(0, 35) + '…' : text;

            if (qd && qd.hasDuplicateA) {
                qItems.push(`
                    <div class="timeline-subitem is-error" style="display:flex; justify-content:space-between; align-items:center; gap:6px;">
                        <span><strong>P${i}:</strong> ${escapeSummaryHtml(shortText || 'Sin texto')}</span>
                        <span style="font-weight:700; font-size:10px; color:var(--danger-red, #ef4444); white-space:nowrap; display:inline-flex; align-items:center; gap:2px;"><span class="material-symbols-outlined" style="font-size:12px;">warning</span> Opciones repetidas</span>
                    </div>
                `);
            } else if (qd && qd.hasDuplicateQ) {
                qItems.push(`
                    <div class="timeline-subitem is-error" style="display:flex; justify-content:space-between; align-items:center; gap:6px;">
                        <span><strong>P${i}:</strong> ${escapeSummaryHtml(shortText)}</span>
                        <span style="font-weight:700; font-size:10px; color:var(--danger-red, #ef4444); white-space:nowrap; display:inline-flex; align-items:center; gap:2px;"><span class="material-symbols-outlined" style="font-size:12px;">warning</span> Pregunta repetida</span>
                    </div>
                `);
            } else if (qd && (qd.hasEmptyQ || qd.hasEmptyA || qd.hasFewA)) {
                qItems.push(`
                    <div class="timeline-subitem" style="color:var(--text-muted); border-style:dashed;">
                        <strong>P${i}:</strong> ${shortText ? escapeSummaryHtml(shortText) : '<em>Pendiente de completar</em>'} <span style="font-size:10.5px;">(${qd.validAnswersCount}/${qd.answersCount} opciones)</span>
                    </div>
                `);
            } else if (qd) {
                qItems.push(`
                    <div class="timeline-subitem">
                        <strong>P${i}:</strong> ${escapeSummaryHtml(shortText)} <span style="color:var(--text-muted); font-size:11px;">(${qd.validAnswersCount} opciones)</span>
                    </div>
                `);
            }
        }

        if (qItems.length > 0) {
            node3Desc = `<div class="timeline-subitems">${qItems.join('')}</div>`;
        } else {
            node3Desc = `Configura hasta ${state.questionCount} pregunta(s) con sus opciones`;
        }

        // === NODE 4: Total Creativos y Tags a generar ===
        let node4State = 'is-pending';
        if (state.creativesGenerated) {
            node4State = 'is-completed';
        } else if (state.currentStep === 2 && allQValid) {
            node4State = 'is-active';
        }

        const node4Title = 'Total creativos y tags';
        let node4Desc = '';
        if (state.creativesGenerated) {
            node4Desc = `${state.totalVariants} creativos (${state.selectedSize.width}x${state.selectedSize.height} px) y ${state.totalVariants} tags listos para descarga`;
        } else if (state.totalVariants > 0) {
            node4Desc = `${state.totalVariants} creativos (${state.selectedSize.width}x${state.selectedSize.height} px) y ${state.totalVariants} tags CM360`;
        } else {
            node4Desc = 'Pendiente de configurar DSP y audiencias';
        }
        const node4Meta = state.totalVariants > 0 ? `${state.totalVariants} creativos · ${state.totalVariants} tags` : '0 items';

        // Render Widget HTML
        widgetContainer.innerHTML = `
            <div class="summary-timeline">
                <!-- Node 1: Campaña -->
                <div class="timeline-step ${node1State}" onclick="goToField(1, 'campaign')">
                    <div class="timeline-marker"></div>
                    <div class="timeline-body">
                        <div class="timeline-header">
                            <h4 class="timeline-title">${node1Title}</h4>
                            <span class="timeline-meta">
                                <span>${node1Meta}</span>
                                <span class="meta-edit-hint">· Editar</span>
                            </span>
                        </div>
                        <div class="timeline-desc">${node1Desc}</div>
                    </div>
                </div>

                <!-- Node 2: DPS y Audiencias -->
                <div class="timeline-step ${node2State}" onclick="goToField(1, 'audiences')">
                    <div class="timeline-marker"></div>
                    <div class="timeline-body">
                        <div class="timeline-header">
                            <h4 class="timeline-title">${node2Title}</h4>
                            <span class="timeline-meta">
                                <span>${node2Meta}</span>
                                <span class="meta-edit-hint">· Editar</span>
                            </span>
                        </div>
                        <div class="timeline-desc">${node2Desc}</div>
                    </div>
                </div>

                <!-- Node 3: Preguntas -->
                <div class="timeline-step ${node3State}" onclick="goToField(2, 'questions')">
                    <div class="timeline-marker"></div>
                    <div class="timeline-body">
                        <div class="timeline-header">
                            <h4 class="timeline-title">${node3Title}</h4>
                            <span class="timeline-meta">
                                <span>${node3Meta}</span>
                                <span class="meta-edit-hint">· Editar</span>
                            </span>
                        </div>
                        <div class="timeline-desc">${node3Desc}</div>
                    </div>
                </div>

                <!-- Node 4: Creativos -->
                <div class="timeline-step ${node4State}" onclick="goToField(2, 'creatives')">
                    <div class="timeline-marker"></div>
                    <div class="timeline-body">
                        <div class="timeline-header">
                            <h4 class="timeline-title">${node4Title}</h4>
                            <span class="timeline-meta">
                                <span>${node4Meta}</span>
                            </span>
                        </div>
                        <div class="timeline-desc">${node4Desc}</div>
                    </div>
                </div>
            </div>
        `;
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
 .screen { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 16px 14px 34px 14px; box-sizing: border-box; z-index: 1; transition: opacity 0.4s ease, transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
 .screen.slow-transition { transition: opacity 1.4s ease, transform 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
 .screen.hidden { opacity: 0; transform: scale(0.85); pointer-events: none; }
 .screen.active { opacity: 1; transform: scale(1); pointer-events: auto; }
 .question-box { width: 100%; max-width: 264px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; box-sizing: border-box; padding: 0 4px; overflow: hidden; flex-shrink: 0; text-align: center; }
 .question-text { font-weight: 800; text-align: center; width: 100%; word-break: break-word; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
 .answers-box { display: flex; flex-direction: column; align-items: center; width: 100%; flex-shrink: 0; }
 .btn-anim { transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.2s ease; }
 .btn-anim:hover { transform: scale(1.04); opacity: 0.92 !important; }
 .btn-anim:active { transform: scale(0.96); }
</style>
<script>
 ${clickUrl ? `var clickTag = "${clickUrl}";` : ''}
 var webhookUrl = "{{ url('/api/brandlift/submit') }}?sheetId=${sheetId}";
 var surveyData = {};

 function fitQuestionTexts() {
   var qTexts = document.querySelectorAll('.question-text');
   for (var i = 0; i < qTexts.length; i++) {
     var el = qTexts[i];
     var box = el.parentElement;
     if (!box) continue;
     var maxH = box.clientHeight || 64;
     var maxW = box.clientWidth || 264;
     var size = parseFloat(window.getComputedStyle(el).fontSize) || 16;
     while ((el.scrollHeight > maxH || el.scrollWidth > maxW) && size > 8.5) {
       size -= 0.5;
       el.style.fontSize = size + 'px';
       el.style.lineHeight = Math.max(11, Math.round(size * 1.22)) + 'px';
     }
   }
 }

 window.onload = function() {
   fitQuestionTexts();
   var q1 = document.getElementById('screen-q1');
   if (q1) q1.classList.add('slow-transition');
   setTimeout(function() { showScreen('screen-q1'); fitQuestionTexts(); }, 150);
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
     fitQuestionTexts();
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
<\/head>
<body style="margin:0;padding:0;overflow:hidden;">
<div style="width:${w}px;height:${h}px;${bgStyle}position:relative;overflow:hidden;font-family:Arial,Helvetica,sans-serif;box-sizing:border-box;">
 <div style="position:absolute;width:200%;height:200%;top:-80%;left:-50%;background:radial-gradient(ellipse at center,${glow} 0%,transparent 60%);pointer-events:none;"></div>`;

        questionsData.forEach((q, idx) => {
            const qNum = idx + 1;
            const nextScreen = qNum < questionsData.length ? `screen-q${qNum+1}` : `screen-thanks`;
            const display = qNum === 1 ? 'active' : 'hidden';

            // Dynamic scaling based on answer count and question text length
            const ansCount = q.answers.length;
            const qLen = (q.question || '').length;
            let qFontSize, btnFontSize, btnPadding, btnGap, btnMaxWidth, qBoxHeight, screenPaddingTop;

            if (ansCount <= 2) {
                qBoxHeight = 68;
                screenPaddingTop = 18;
                btnFontSize = 14;
                btnPadding = '10px 24px';
                btnGap = 8;
                btnMaxWidth = 250;
                if (qLen <= 30) qFontSize = 18;
                else if (qLen <= 60) qFontSize = 15;
                else if (qLen <= 100) qFontSize = 13;
                else if (qLen <= 150) qFontSize = 11;
                else qFontSize = 9.5;
            } else if (ansCount === 3) {
                qBoxHeight = 62;
                screenPaddingTop = 14;
                btnFontSize = 13;
                btnPadding = '8px 22px';
                btnGap = 6;
                btnMaxWidth = 250;
                if (qLen <= 30) qFontSize = 16;
                else if (qLen <= 60) qFontSize = 14;
                else if (qLen <= 100) qFontSize = 12;
                else if (qLen <= 150) qFontSize = 10.5;
                else qFontSize = 9;
            } else if (ansCount === 4) {
                qBoxHeight = 56;
                screenPaddingTop = 12;
                btnFontSize = 12;
                btnPadding = '6px 18px';
                btnGap = 5;
                btnMaxWidth = 240;
                if (qLen <= 30) qFontSize = 15;
                else if (qLen <= 60) qFontSize = 13;
                else if (qLen <= 100) qFontSize = 11.5;
                else if (qLen <= 150) qFontSize = 10;
                else qFontSize = 8.5;
            } else if (ansCount === 5) {
                qBoxHeight = 48;
                screenPaddingTop = 10;
                btnFontSize = 11;
                btnPadding = '5px 16px';
                btnGap = 4;
                btnMaxWidth = 230;
                if (qLen <= 30) qFontSize = 13.5;
                else if (qLen <= 60) qFontSize = 12;
                else if (qLen <= 100) qFontSize = 10.5;
                else qFontSize = 8.5;
            } else {
                qBoxHeight = 44;
                screenPaddingTop = 8;
                btnFontSize = 10.5;
                btnPadding = '4px 14px';
                btnGap = 3;
                btnMaxWidth = 230;
                if (qLen <= 30) qFontSize = 12.5;
                else if (qLen <= 60) qFontSize = 11;
                else qFontSize = 8.5;
            }

            html += `
 <div id="screen-q${qNum}" class="screen ${display}" style="padding-top:${screenPaddingTop}px;">
  <div class="question-box" style="height:${qBoxHeight}px;max-height:${qBoxHeight}px;">
   <div class="question-text" style="${textStyle}font-size:${qFontSize}px;line-height:${Math.round(qFontSize*1.22)}px;">${q.question}</div>
  </div>
  <div class="answers-box" style="gap:${btnGap}px;">`;

            q.answers.forEach(a => {
                html += `
   <div onclick="storeAnswer('${qNum}', '${q.question.replace(/'/g,"\\'").replace(/"/g,"&quot;")}', '${a.replace(/'/g,"\\'").replace(/"/g,"&quot;")}'); setTimeout(function(){ showScreen('${nextScreen}'); ${qNum === questionsData.length ? 'submitAnswers();' : ''} }, 300);" class="btn-anim" style="background:${btnBg};border-radius:100px;padding:${btnPadding};text-align:center;cursor:pointer;font-family:Arial,Helvetica,sans-serif;font-size:${btnFontSize}px;font-weight:700;color:${btnText};letter-spacing:0.3px;width:80%;max-width:${btnMaxWidth}px;box-sizing:border-box;box-shadow:0 4px 10px rgba(0,0,0,0.15);">${a}</div>`;
            });

            html += `
  </div>
 </div>`;
        });

        html += `
 <div id="screen-thanks" class="screen hidden" style="justify-content:center;padding-top:20px;">
  <div style="${textStyle}font-size:22px;font-weight:800;text-align:center;line-height:1.4;margin-bottom:15px;text-shadow:0 2px 4px rgba(0,0,0,0.3);">¡Muchas gracias<br>por su opinión!</div>
 </div>
 <div style="position:absolute;bottom:10px;right:14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:${footerColor};letter-spacing:0.5px;z-index:2;"><span style="font-weight:800;">WPP</span><span style="font-weight:400;"> Media</span></div>
</div>
<\/body>
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
        const groups = state.groups.length > 0 ? state.groups : [{name: ''}];

        state.generatedCreatives = [];

        for (const dps of dpsSelections) {
            const clickEnabled = (dps === 'TTD' || dps === 'Sonata' || dps === 'Amazon');
            const clickUrl = clickEnabled ? 'https://www.wppmedia.com' : '';

            for (const group of groups) {
                for (const tagType of tagTypes) {
                    const key = group.name ? `${dps}_${group.name}_${tagType}` : `${dps}_${tagType}`;
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
        populateSummary();
    }

    // ===== CREATE BRANDLIFT =====
    $('#btn-create').addEventListener('click', async (e) => {
        if ($('#btn-create').disabled) {
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
        const endDateStep1 = $('#bl-end-date-step1').value;
        const investmentStep1 = $('#bl-investment-step1').value.trim();
        const profileId = $('#cm360-profile-id').value.trim();
        const advertiserId = $('#cm360-advertiser-id').value.trim();
        const siteId = $('#cm360-site-id').value.trim();
        const clientName = $('#bl-client-step1').value.trim();

        if (!market) { showToast('Selecciona un mercado', true); goToField(1, '#bl-market-step1'); return; }
        if (state.dpsSelections.length === 0) { showToast('Selecciona al menos un DPS', true); goToField(1, '.tag-type-options'); return; }
        if (!campaignNameStep1) { showToast('Ingresa el nombre de la campaña', true); goToField(1, '#bl-campaign-name-step1'); return; }
        if (!endDateStep1) { showToast('Ingresa la fecha fin de campaña', true); goToField(1, '#bl-end-date-step1'); return; }
        const today = new Date().toLocaleDateString('en-CA');
        if (endDateStep1 < today) { showToast('La fecha de finalización no puede ser una fecha pasada', true); goToField(1, '#bl-end-date-step1'); return; }
        if (!investmentStep1) { showToast('Ingresa la inversión / bonificado', true); goToField(1, '#bl-investment-step1'); return; }
        if (!clientName) { showToast('Ingresa el nombre del cliente', true); goToField(1, '#bl-client-step1'); return; }
        if (!profileId || !advertiserId || !siteId) { showToast('Selecciona Profile, Advertiser y Site en la Configuración inicial', true); goToField(1, '#bl-market-step1'); return; }
        const groupValidation = validateGroups();
        if (!groupValidation.isValid) {
            showToast(groupValidation.hasEmpty ? 'No se permiten grupos de audiencia con nombres vacíos' : 'No se pueden repetir los nombres de los grupos de audiencia', true);
            goToField(1, 'audiences');
            return;
        }

        const qValidation = validateQuestionsAndAnswers(true);
        if (!qValidation.isValid) {
            showToast(qValidation.errorMessage || 'Completa y valida las preguntas y respuestas', true);
            if (qValidation.firstInvalidTarget) {
                goToField(2, qValidation.firstInvalidTarget);
            } else {
                goToStep(2);
            }
            return;
        }

        const btn = $('#btn-create');
        btn.disabled = true;
        btn.innerHTML = `<div class="spinner"></div> Creando & Cargando...`;

        // 0. Iniciar Stepper Interactivo con IA en el Overlay
        initAIStepper();

        let isSuccess = false;
        let sheetId = null;

        try {
            // ==========================================
            // PASO 1: Creación en la Plataforma
            // ==========================================
            setAIStep(1, 'active', 'Configurando Workspace en Google Drive...');
            updateAIProgress(12);

            try {
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
            } catch (sheetErr) {
                setAIStep(1, 'error', 'Error en Google Drive: ' + sheetErr.message);
                throw sheetErr;
            }

            setAIStep(1, 'active', 'Registrando estudio y preguntas en la base de datos...');
            updateAIProgress(22);

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
                    audiences: state.groups.map(g => g.name.trim()).filter(n => n.length > 0),
                    dps_tags: state.dpsSelections,
                    sheet_id: sheetId,
                    end_date: endDateStep1,
                    investment: investmentStep1,
                    cm360_site_id: siteId,
                    cm360_profile_id: profileId,
                    cm360_advertiser_id: advertiserId,
                    theme_colors: state.theme,
                    questions: questionsPayload
                })
            });
            const storeData = await storeRes.json();
            if (!storeRes.ok || !storeData.success) {
                setAIStep(1, 'error', storeData.message || 'Error al registrar en base de datos');
                throw new Error(storeData.message || 'Error al registrar en base de datos');
            }
            state.studyId = storeData.study_id;

            setAIStep(1, 'completed', 'Workspace y plataforma registrados correctamente.');
            updateAIProgress(35);

            // ==========================================
            // PASO 2: Generación de Creativos & Backup
            // ==========================================
            setAIStep(2, 'active', 'Compilando creativos HTML5 interactivos...');
            updateAIProgress(42);

            updateVariantCount();
            if (state.tagTypes.length === 0) {
                throw new Error('Selecciona al menos un tipo de tag (Ad_Exposed o Control)');
            }

            generateAndShowPreviews(sheetId);

            setAIStep(2, 'active', 'Generando captura de pantalla de seguridad (Backup)...');
            updateAIProgress(52);

            let backupImageBase64 = null;
            if (state.generatedCreatives.length > 0) {
                try {
                    const captureContainer = document.createElement('div');
                    captureContainer.style.cssText = `
                        position: fixed; left: -9999px; top: 0; z-index: -1;
                        width: ${state.selectedSize.width}px;
                        height: ${state.selectedSize.height}px;
                        overflow: hidden;
                    `;

                    const shadowHost = document.createElement('div');
                    captureContainer.appendChild(shadowHost);
                    document.body.appendChild(captureContainer);

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(state.generatedCreatives[0].html, 'text/html');

                    const renderDiv = document.createElement('div');
                    renderDiv.style.cssText = `
                        width: ${state.selectedSize.width}px;
                        height: ${state.selectedSize.height}px;
                        position: relative; overflow: hidden;
                    `;

                    doc.querySelectorAll('style').forEach(s => {
                        const style = document.createElement('style');
                        style.textContent = s.textContent;
                        renderDiv.appendChild(style);
                    });

                    const bodyEl = doc.querySelector('body');
                    const bodyWrapper = document.createElement('div');
                    bodyWrapper.style.cssText = (bodyEl?.getAttribute('style') || '') + `; width: ${state.selectedSize.width}px; height: ${state.selectedSize.height}px; position: relative; overflow: hidden; margin: 0; padding: 0;`;
                    bodyWrapper.innerHTML = bodyEl?.innerHTML || '';

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

                    await new Promise(r => setTimeout(r, 450));

                    const canvas = await html2canvas(renderDiv, {
                        useCORS: true,
                        width: state.selectedSize.width,
                        height: state.selectedSize.height,
                        scale: 1,
                        backgroundColor: null,
                        logging: false
                    });
                    backupImageBase64 = canvas.toDataURL('image/jpeg', 0.9);
                    document.body.removeChild(captureContainer);
                } catch (e) {
                    console.error('Error capturing backup image:', e);
                }
            }

            setAIStep(2, 'completed', 'Creativos HTML5 y Backup compilados.');
            updateAIProgress(60);

            // ==========================================
            // PASO 3: Despliegue en Orden en CM360
            // ==========================================
            setAIStep(3, 'active', 'Creando Campaña, Placements, Creativos y Anuncios en CM360...');
            updateAIProgress(72);

            const statusBar = $('#cm360-status');
            statusBar.className = 'status-bar visible loading';
            statusBar.innerHTML = `<div style="display:flex; align-items:center; gap:10px;"><div class="spinner"></div> Sincronizando con Google Campaign Manager 360...</div>`;

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
                    end_date: $('#bl-end-date-step1').value,
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

            if (!response.ok || !data.success) {
                setAIStep(3, 'error', data.message || 'Error en CM360');
                statusBar.className = 'status-bar visible error';
                let errorHtml = `<div style="display:flex; align-items:center; gap:8px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> <strong>${data.message || 'Error al subir a CM360'}</strong></div>`;
                if (data.results && data.results.length > 0) {
                    const failedResults = data.results.filter(r => r.status === 'error');
                    if (failedResults.length > 0) {
                        errorHtml += '<div style="margin-top:8px; font-size:12px; opacity:0.85;">';
                        failedResults.forEach(r => {
                            errorHtml += `<div style="margin-top:4px; display:flex; align-items:center; gap:4px;"><span class="material-symbols-outlined" style="font-size:14px; color:#ef4444;">error</span> <strong>Q${r.question_number}</strong> (${r.creative_name}): ${r.error}</div>`;
                        });
                        errorHtml += '</div>';
                    }
                }
                statusBar.innerHTML = errorHtml;
                throw new Error(data.message || 'Error al sincronizar con CM360');
            }

            setAIStep(3, 'completed', 'Elementos creados y vinculados en CM360 exitosamente.');
            updateAIProgress(85);

            // ==========================================
            // PASO 4: Generación & Exportación de Tags
            // ==========================================
            setAIStep(4, 'active', 'Almacenando tags de medición y generando Excel...');
            updateAIProgress(92);

            window.cm360TagsData = data.results;

            try {
                await fetch('/api/brandlift/store-tags', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        study_id: state.studyId,
                        tags: data.results
                    })
                });
            } catch (tagErr) {
                console.error('Error guardando tags generados en DB:', tagErr);
            }

            setAIStep(4, 'completed', 'Tags generados y listos para descargar.');
            updateAIProgress(100);

            // Link de Campaña en CM360
            const campaignId = (data.results && data.results.find(r => r.campaign_id)?.campaign_id) || null;
            let cm360Url = '';
            if (campaignId) {
                const profileObj = (window.cm360Profiles || []).find(p => p.id == profileId);
                const accountId = profileObj?.accountId || '732535';
                cm360Url = `https://campaignmanager.google.com/trafficking/#/accounts/${accountId}/campaigns/${campaignId}/explorer?statuses=0;2`;
            }

            let successHtml = `
                <div style="display: flex; align-items: center; gap: 10px; width: 100%;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
                    <span style="font-weight: 600; color: #166534; font-size: 14px;">Creativos subidos y tags generados exitosamente</span>
                </div>
            `;

            if (cm360Url) {
                successHtml += `
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(22, 101, 52, 0.15); width: 100%; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #166534;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span>Campaña en Campaign Manager:</span>
                        </div>
                        <a href="${cm360Url}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #000050; background: #b0f467; padding: 7px 16px; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,80,0.1);">
                            <span>Abrir campaña en CM360</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>
                `;
            }

            statusBar.className = 'status-bar visible success';
            statusBar.innerHTML = successHtml;

            showToast('¡Proceso completado exitosamente!');
            isSuccess = true;
            state.creationCompleted = true;

            // Pausa breve para visualizar el 100% completado en el stepper
            await new Promise(r => setTimeout(r, 1200));

        } catch (error) {
            console.error(error);
            showToast('' + (error.message || 'Error en el proceso de creación'), true);
            await new Promise(r => setTimeout(r, 1800));
        } finally {
            if (isSuccess) {
                btn.style.display = 'none';

                // El modal permanece abierto al terminar el proceso con el botón para finalizar
                const footerActions = $('#ai-modal-footer-actions');
                if (footerActions) {
                    footerActions.style.display = 'flex';
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = `<span class="ripple"></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> <span id="btn-create-text">Crear & Cargar</span>`;
                $('#full-loading-overlay').classList.remove('active');
            }
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

        // Reset and show nav controls
        window.currentPreviewStep = 0;
        $('#preview-nav-text').innerText = 'Pág 1';
        $('#preview-nav-controls').style.display = 'flex';
        $('.btn-restart-preview').style.display = 'inline-flex';
    }

    window.navigatePreview = function(dir) {
        const f = document.querySelector('#preview-content-1 iframe');
        if (!f || !f.contentWindow) return;

        const totalQ = parseInt($('#bl-question-count').value) || 1;
        const maxScreens = totalQ + 1;

        window.currentPreviewStep = (window.currentPreviewStep || 0) + dir;
        if (window.currentPreviewStep < 0) window.currentPreviewStep = 0;
        if (window.currentPreviewStep >= maxScreens) window.currentPreviewStep = maxScreens - 1;

        let targetId = 'screen-q' + (window.currentPreviewStep + 1);
        if (window.currentPreviewStep === maxScreens - 1) {
            targetId = 'screen-thanks';
        }

        $('#preview-nav-text').innerText = (window.currentPreviewStep === maxScreens - 1) ? 'Final' : 'Pág ' + (window.currentPreviewStep + 1);

        if (typeof f.contentWindow.showScreen === 'function') {
            f.contentWindow.showScreen(targetId);
        }
    };
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

    function parseClientName(advertiserName) {
        if (!advertiserName) return '';
        if (advertiserName.includes('|')) {
            const parts = advertiserName.split('|');
            return parts[parts.length - 1].trim();
        }
        const parts = advertiserName.split(/[_]/);
        if (parts.length > 1) {
            return parts[parts.length - 1].replace(/^\[TEST\]\s*/i, '').trim();
        }
        return advertiserName.trim();
    }

    function filterAdvertisers() {
        const advSelect = $('#cm360-advertiser-id');
        const market = $('#bl-market-step1').value;

        if (!market) {
            advSelect.innerHTML = '<option value="">Selecciona un mercado primero...</option>';
            advSelect.disabled = true;
            return;
        }

        if (!window.cm360Advertisers || window.cm360Advertisers.length === 0) {
            return;
        }

        advSelect.innerHTML = '<option value="">Selecciona un anunciante...</option>';

        const marketAliases = {
            'PE': ['PE', 'PER', 'XAXPE', 'PERU', 'PERÚ'],
            'PRI': ['PRI', 'PR', 'XAXPR', 'PUERTO RICO'],
            'ARG': ['ARG', 'AR', 'XAXAR', 'ARGENTINA'],
            'MIA': ['MIA', 'MSMIA', 'MIAMI'],
            'MEX': ['MEX', 'MX', 'XAXMEX', 'MEXICO', 'MÉXICO'],
            'CHL': ['CHL', 'CL', 'XAXCL', 'CHILE'],
            'COL': ['COL', 'CO', 'XAXCO', 'COLOMBIA'],
            'ECU': ['ECU', 'EC', 'XAXEC', 'ECUADOR']
        };

        const aliases = marketAliases[market.toUpperCase()] || [market.toUpperCase()];

        let filteredCount = 0;
        window.cm360Advertisers.forEach(a => {
            const nameUpper = (a.name || '').toUpperCase();
            const matches = aliases.some(alias => {
                if (nameUpper.startsWith(alias)) return true;
                const regex = new RegExp(`(^|[\\s_\\|\\-\\[\\(])${alias}([\\s_\\|\\-\\]\\)]|$)`, 'i');
                return regex.test(nameUpper);
            });

            if (matches) {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = `${a.name} (${a.id})`;
                advSelect.appendChild(opt);
                filteredCount++;
            }
        });

        // In edit mode or existing study data, ensure the pre-saved advertiser is available and selected
        if (window.EDIT_STUDY_DATA) {
            const editAdvId = window.EDIT_STUDY_DATA.cm360_advertiser_id ? String(window.EDIT_STUDY_DATA.cm360_advertiser_id) : null;
            const editClient = (window.EDIT_STUDY_DATA.client_name || '').trim().toLowerCase();

            let matchedOpt = null;

            if (editAdvId) {
                matchedOpt = [...advSelect.options].find(o => o.value === editAdvId);
                // If not in the filtered list but exists in all advertisers, add it
                if (!matchedOpt) {
                    const fullAdv = window.cm360Advertisers.find(a => String(a.id) === editAdvId);
                    if (fullAdv) {
                        matchedOpt = document.createElement('option');
                        matchedOpt.value = fullAdv.id;
                        matchedOpt.textContent = `${fullAdv.name} (${fullAdv.id})`;
                        advSelect.appendChild(matchedOpt);
                        filteredCount++;
                    }
                }
            }

            // Fallback: match by client_name
            if (!matchedOpt && editClient) {
                matchedOpt = [...advSelect.options].find(o => o.textContent.toLowerCase().includes(editClient));
                if (!matchedOpt) {
                    const fullAdv = window.cm360Advertisers.find(a => (a.name || '').toLowerCase().includes(editClient));
                    if (fullAdv) {
                        matchedOpt = document.createElement('option');
                        matchedOpt.value = fullAdv.id;
                        matchedOpt.textContent = `${fullAdv.name} (${fullAdv.id})`;
                        advSelect.appendChild(matchedOpt);
                        filteredCount++;
                    }
                }
            }

            if (matchedOpt) {
                advSelect.value = matchedOpt.value;
                const selectedAdv = window.cm360Advertisers.find(a => String(a.id) === matchedOpt.value);
                if (selectedAdv) {
                    const parsedName = parseClientName(selectedAdv.name);
                    if (parsedName) $('#bl-client-step1').value = parsedName;
                }
            }
        }

        if (filteredCount === 0) {
            advSelect.innerHTML = `<option value="">No se encontraron anunciantes para ${market}</option>`;
        }
        advSelect.disabled = false;
        if (typeof validateCurrentStep === 'function') validateCurrentStep();
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
        const advSelect = $('#cm360-advertiser-id');
        const advId = advSelect ? advSelect.value : null;

        if (!marketVal) {
            siteSelect.innerHTML = '<option value="">Selecciona un mercado primero...</option>';
            siteSelect.disabled = true;
            return;
        }

        if (!window.cm360Sites || window.cm360Sites.length === 0) return;

        const selectedOption = marketSelect.options[marketSelect.selectedIndex];
        const countryName = selectedOption ? selectedOption.getAttribute('data-country') : '';

        let targetSiteName = `Xaxis ${countryName}`.toLowerCase();
        if (marketVal === 'PE') {
            targetSiteName = 'adwords';
        } else if (marketVal === 'ARG') {
            targetSiteName = 'xaxis_argentina';
        }

        const selectedAdv = advId && window.cm360Advertisers ? window.cm360Advertisers.find(a => String(a.id) === String(advId)) : null;
        const hasSubaccount = selectedAdv && selectedAdv.subaccountId;

        siteSelect.innerHTML = '<option value="">Selecciona un site...</option>';

        let foundMatchId = null;

        // If advertiser has a subaccount, filter to only sites in that subaccount
        let eligibleSites = window.cm360Sites;
        if (hasSubaccount) {
            const subaccountSites = window.cm360Sites.filter(s => String(s.subaccountId) === String(selectedAdv.subaccountId));
            if (subaccountSites.length > 0) {
                eligibleSites = subaccountSites;
            }
        }

        eligibleSites.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.name} (${s.id})`;
            siteSelect.appendChild(opt);

            // Matching logic:
            if (!foundMatchId) {
                if (s.name.toLowerCase().includes(targetSiteName)) {
                    foundMatchId = s.id;
                } else if (hasSubaccount && s.name.toLowerCase().includes('xaxis')) {
                    foundMatchId = s.id;
                }
            }
        });

        // If no match found yet, select first eligible site if subaccount was used
        if (!foundMatchId && hasSubaccount && eligibleSites.length > 0) {
            foundMatchId = eligibleSites[0].id;
        }

        if (window.EDIT_STUDY_DATA && window.EDIT_STUDY_DATA.cm360_site_id) {
            const editSiteId = String(window.EDIT_STUDY_DATA.cm360_site_id);
            if ([...siteSelect.options].some(o => o.value === editSiteId)) {
                foundMatchId = editSiteId;
            }
        }

        if (foundMatchId) {
            siteSelect.value = foundMatchId;
        }

        siteSelect.disabled = false;
        if (typeof validateCurrentStep === 'function') validateCurrentStep();
    }

    $('#cm360-advertiser-id').addEventListener('change', (e) => {
        const advId = e.target.value;
        if (!advId) {
            $('#bl-client-step1').value = '';
            filterSites();
            populateSummary();
            validateCurrentStep();
            return;
        }

        const adv = window.cm360Advertisers.find(a => String(a.id) === String(advId));
        if (adv) {
            const clientName = parseClientName(adv.name);
            if (clientName) {
                $('#bl-client-step1').value = clientName;
            }
            filterSites();
            populateSummary();
            validateCurrentStep();
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
        const fileName = `${year}_${month}_WMSCSLATAM_${market}_${client.replace(/\s+/g,'_')}_${campaignName}_Tags_brandlift_b`;

        XLSX.writeFile(wb, `${fileName}.xlsx`);
    }

    $('#btn-download-excel-tags')?.addEventListener('click', () => {
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
        initEditModeIfPresent();
    });

    window.addEventListener('message', function(event) {
        if (event.data === 'brandlift_finished') {
            document.querySelectorAll('.btn-restart-preview').forEach(btn => btn.style.display = 'inline-flex');
        }
    });

    window.EDIT_STUDY_DATA = @json($editStudy ?? null);

    function initEditModeIfPresent() {
        if (!window.EDIT_STUDY_DATA) return;
        const data = window.EDIT_STUDY_DATA;

        state.studyId = data.id;

        // Step 1 fields
        if (data.market) {
            const marketSel = $('#bl-market-step1');
            if (marketSel) {
                marketSel.value = data.market;
                marketSel.dispatchEvent(new Event('change'));
            }
        }

        if (data.campaign_name) {
            const campEl = $('#bl-campaign-name-step1');
            if (campEl) campEl.value = data.campaign_name;
        }

        if (data.client_name) {
            const clientEl = $('#bl-client-step1');
            if (clientEl) clientEl.value = data.client_name;
        }

        if (data.investment) {
            const invEl = $('#bl-investment-step1');
            if (invEl) invEl.value = data.investment;
        }

        if (data.end_date) {
            const endEl = $('#bl-end-date-step1');
            const visualEl = $('#bl-end-date-visual');
            if (endEl) endEl.value = data.end_date;
            if (visualEl) {
                const [y, m, d] = data.end_date.split('-');
                visualEl.value = `${d}/${m}/${y}`;
            }
        }

        // DSP checkboxes
        if (Array.isArray(data.dps_tags) && data.dps_tags.length > 0) {
            const tagsLower = data.dps_tags.map(t => String(t).toLowerCase());
            ['dv360', 'ttd', 'sonata', 'amazon'].forEach(dsp => {
                const cb = $(`#dps-${dsp}`);
                if (cb) {
                    cb.checked = tagsLower.includes(dsp);
                }
            });
        }

        // Audiences
        if (Array.isArray(data.audiences) && data.audiences.length > 0) {
            const container = $('#groups-container');
            if (container) {
                container.innerHTML = '';
                data.audiences.forEach((aud, idx) => {
                    const groupItem = document.createElement('div');
                    groupItem.className = 'group-item';
                    groupItem.dataset.groupIndex = idx;
                    groupItem.innerHTML = `
                        <input type="text" class="group-name-input" placeholder="Nombre de audiencia..." value="${escapeSummaryHtml(aud)}" size="14">
                        <button type="button" class="btn-remove-group" title="Eliminar audiencia"><span class="material-symbols-outlined" style="font-size:16px;">close</span></button>
                    `;
                    container.appendChild(groupItem);
                    setupGroupItem(groupItem);
                });
            }
        }

        // Question count
        if (data.question_count) {
            const qCount = parseInt(data.question_count, 10) || 1;
            state.questionCount = qCount;
            const qCountEl = $('#bl-question-count');
            if (qCountEl) {
                qCountEl.value = qCount;
                qCountEl.dispatchEvent(new Event('change'));
            }
        }

        // Questions & Answers
        if (Array.isArray(data.questions) && data.questions.length > 0) {
            data.questions.forEach((q, idx) => {
                const qNum = q.question_number || (idx + 1);
                const qInput = $(`#bl-question-${qNum}`);
                if (qInput) {
                    qInput.value = q.question_text || q.question || '';
                }

                if (Array.isArray(q.answers) && q.answers.length > 0) {
                    const ansContainer = $(`#answers-container-${qNum}`);
                    if (ansContainer) {
                        ansContainer.innerHTML = '';
                        q.answers.forEach(ans => {
                            addAnswerInput(qNum, ans);
                        });
                    }
                }
            });
        }

        // Theme
        if (data.theme_colors) {
            const themeVal = typeof data.theme_colors === 'string' ? data.theme_colors : (data.theme_colors.theme || 'dark');
            state.theme = themeVal;
            if (themeVal === 'light') {
                $('#theme-light')?.click();
            } else {
                $('#theme-dark')?.click();
            }
        }

        if (window.cm360Advertisers && window.cm360Advertisers.length > 0) {
            filterAdvertisers();
            filterSites();
        }

        updateVariantCount(false);
        validateQuestionsAndAnswers(false);
        validateCurrentStep();
        populateSummary();
        updateViewportHeight();

        setTimeout(() => {
            if (window.cm360Advertisers && window.cm360Advertisers.length > 0) {
                filterAdvertisers();
                filterSites();
            }
            updateVariantCount(false);
            validateQuestionsAndAnswers(false);
            validateCurrentStep();
            populateSummary();
            updateViewportHeight();
        }, 300);
    }
    </script>
            </div><!-- .content-area -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
</body>
</html>
