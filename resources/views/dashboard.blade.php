<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — Brandlift History</title>
    <meta name="description" content="Dashboard de historial de Brandlifts creados para Campaign Manager 360">
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
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
            opacity: 0.12;
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
            width: 400px; height: 400px;
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



        /* ===== KPI CARDS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--bg-card);
            border: none;
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow-card);
            transition: all var(--transition-med);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .kpi-card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-4px);
        }

        /* Primera Card (Solid Navy) */
        .kpi-card.card-primary {
            background: var(--wpp-navy);
            color: var(--wpp-white);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .kpi-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.3px;
        }

        .kpi-card.card-primary .kpi-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .kpi-icon {
            width: 48px; height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: var(--bg-secondary);
            color: var(--wpp-navy);
        }

        .kpi-card.card-primary .kpi-icon {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
        }

        .kpi-value {
            font-size: 48px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
            color: var(--wpp-navy);
        }

        .kpi-card.card-primary .kpi-value {
            color: var(--wpp-white);
        }

        .kpi-sub {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .kpi-card.card-primary .kpi-sub {
            color: rgba(255, 255, 255, 0.6);
        }

        /* ===== CARD (Table Container) ===== */
        .card {
            background: var(--bg-card);
            border: none;
            border-radius: var(--radius-xl);
            padding: 28px;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-card);
        }

        .card-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-header-row h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .card-header-row h2 .icon-wrapper {
            width: 38px; height: 38px;
            border-radius: var(--radius-sm);
            background: rgba(176, 244, 103, 0.2);
            color: var(--wpp-navy);
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ===== FILTERS ===== */
        .filters-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .filter-input, .filter-select {
            padding: 12px 18px;
            background: var(--bg-secondary);
            border: 1px solid transparent;
            border-radius: var(--radius-full);
            color: var(--text-primary);
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            transition: all var(--transition-fast);
            outline: none;
        }

        .filter-input:focus, .filter-select:focus {
            background: var(--bg-card);
            border-color: var(--border-input-focus);
            box-shadow: 0 0 0 3px rgba(147, 223, 227, 0.15);
        }

        .filter-input { flex: 1; min-width: 200px; }

        .filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12' fill='none'%3E%3Cpath d='M3 4.5L6 7.5L9 4.5' stroke='%2364748b' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
            min-width: 160px;
        }

        .filter-input::placeholder { color: var(--text-muted); }

        .btn-filter-clear {
            padding: 10px 16px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-sm);
            color: #fca5a5;
            font-family: 'WPP', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: none;
        }

        .btn-filter-clear:hover {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.35);
        }

        .btn-filter-clear.visible { display: flex; align-items: center; gap: 6px; }

        /* ===== TABLE ===== */
        .table-wrapper {
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(176, 244, 103, 0.2) transparent;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8; /* Gris muy claro para encabezados */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0, 0, 80, 0.05);
            white-space: nowrap;
            position: sticky;
            top: 0;
            background: var(--bg-card);
        }

        tbody tr {
            transition: all var(--transition-fast);
            cursor: pointer;
        }

        tbody tr:hover {
            background: rgba(176, 244, 103, 0.04);
        }

        tbody td {
            padding: 16px 16px;
            font-size: 13px;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(0, 0, 80, 0.03);
            white-space: nowrap;
        }

        .td-id {
            color: var(--text-muted);
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }

        .td-campaign {
            color: var(--text-primary);
            font-weight: 600;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .badge-market {
            background: var(--bg-secondary);
            color: var(--text-secondary);
        }

        .badge-created {
            background: rgba(147, 223, 227, 0.2);
            color: var(--wpp-navy);
        }

        .badge-pushed {
            background: rgba(176, 244, 103, 0.25);
            color: var(--wpp-navy);
        }

        .badge-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
        }

        .badge-created .badge-dot { background: var(--wpp-cyan); }
        .badge-pushed .badge-dot { background: var(--wpp-lime); }
        .badge-error .badge-dot { background: #ef4444; }

        /* ===== ACTION BUTTONS ===== */
        .actions-cell {
            display: flex;
            gap: 6px;
        }

        .btn-action {
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(176, 244, 103, 0.15);
            background: rgba(176, 244, 103, 0.06);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-action:hover {
            background: rgba(176, 244, 103, 0.15);
            color: var(--accent-blue-light);
            border-color: rgba(176, 244, 103, 0.3);
        }

        .btn-action.danger:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* ===== PAGINATION ===== */
        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid rgba(176, 244, 103, 0.08);
            margin-top: 8px;
        }

        .pagination-info {
            font-size: 12px;
            color: var(--text-muted);
        }

        .pagination-buttons {
            display: flex;
            gap: 4px;
        }

        .btn-page {
            padding: 8px 12px;
            border-radius: 6px;
            background: rgba(176, 244, 103, 0.06);
            border: 1px solid rgba(176, 244, 103, 0.12);
            color: var(--text-muted);
            font-family: 'WPP', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-page:hover:not(:disabled) {
            background: rgba(176, 244, 103, 0.15);
            color: var(--accent-blue-light);
        }

        .btn-page.active {
            background: var(--wpp-navy);
            color: white;
            border-color: transparent;
        }

        .btn-page:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state .icon { font-size: 56px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: var(--text-secondary); }
        .empty-state p { font-size: 14px; color: var(--text-muted); line-height: 1.6; }

        .empty-state .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            background: var(--wpp-navy);
            color: white;
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(176, 244, 103, 0.3);
            transition: all var(--transition-fast);
        }

        .empty-state .btn-cta:hover {
            box-shadow: 0 6px 24px rgba(176, 244, 103, 0.45);
            transform: translateY(-1px);
        }

        /* ===== LOADING SKELETON ===== */
        .skeleton-row td {
            position: relative;
            overflow: hidden;
        }

        .skeleton-bar {
            height: 14px;
            border-radius: 4px;
            background: rgba(176, 244, 103, 0.06);
            position: relative;
            overflow: hidden;
        }

        .skeleton-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(176, 244, 103, 0.06), transparent);
            animation: skeletonShimmer 1.5s infinite;
        }

        @keyframes skeletonShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            padding: 24px;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal {
            background: var(--bg-card);
            border: 1px solid var(--bg-card-border);
            border-radius: var(--radius-xl);
            padding: 0;
            max-width: 680px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(176, 244, 103, 0.1);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            scrollbar-width: thin;
            scrollbar-color: rgba(176, 244, 103, 0.2) transparent;
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px;
            border-bottom: 1px solid rgba(176, 244, 103, 0.08);
            position: sticky;
            top: 0;
            background: var(--bg-card);
            z-index: 2;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(176, 244, 103, 0.15);
            background: rgba(176, 244, 103, 0.06);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .modal-body {
            padding: 28px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .detail-item {
            background: var(--bg-secondary);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-md);
            padding: 16px;
            transition: all 0.2s ease;
        }
        .detail-item:hover {
            border-color: rgba(176, 244, 103, 0.5);
            background: rgba(176, 244, 103, 0.02);
        }

        .detail-item .detail-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .detail-item .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-all;
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .question-detail-block {
            background: var(--bg-secondary);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-md);
            padding: 20px;
            height: 100%;
            transition: all 0.2s ease;
        }
        .question-detail-block:hover {
            border-color: rgba(176, 244, 103, 0.5);
        }

        .question-detail-block .q-num {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            background: var(--wpp-neon);
            border: 1px solid var(--wpp-neon);
            color: var(--wpp-navy);
            margin-bottom: 10px;
            box-shadow: 0 0 10px rgba(176, 244, 103, 0.2);
        }

        .question-detail-block .q-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            line-height: 1.5;
        }


        .preview-mini-frame {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px dashed var(--border-input);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .preview-mini-frame iframe {
            border: none;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed; bottom: 32px; right: 32px;
            padding: 14px 24px; border-radius: var(--radius-md);
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            color: #6ee7b7; font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
            z-index: 2000; transform: translateY(20px); opacity: 0;
            transition: all 0.3s ease-out; backdrop-filter: blur(12px);
        }
        .toast.show { transform: translateY(0); opacity: 1; }

        /* ===== CONFIRM DIALOG ===== */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .confirm-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .confirm-box {
            background: var(--bg-card);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-lg);
            padding: 28px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
        }

        .confirm-box .icon { font-size: 40px; margin-bottom: 12px; }
        .confirm-box h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .confirm-box p { font-size: 14px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5; }

        .confirm-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirm-actions button {
            padding: 10px 24px;
            border-radius: var(--radius-sm);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
        }

        .btn-confirm-cancel {
            background: rgba(176, 244, 103, 0.08);
            border: 1px solid rgba(176, 244, 103, 0.15) !important;
            color: var(--text-secondary);
        }

        .btn-confirm-cancel:hover {
            background: rgba(176, 244, 103, 0.15);
        }

        .btn-confirm-delete {
            background: rgba(239, 68, 68, 0.8);
            color: white;
        }

        .btn-confirm-delete:hover {
            background: rgba(239, 68, 68, 1);
        }

        /* ===== MARKET CHIPS (KPI) ===== */
        .market-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .market-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            color: #c4b5fd;
        }

        .market-chip .chip-count {
            background: rgba(139, 92, 246, 0.2);
            padding: 1px 5px;
            border-radius: 100px;
            font-size: 9px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .app-container { padding: 20px 16px; }
            .top-nav { flex-direction: column; gap: 16px; }
            .filters-bar { flex-direction: column; }
            .filter-input { min-width: 100%; }
            .detail-grid { grid-template-columns: 1fr; }
            .card { padding: 20px; }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .animate-in:nth-child(1) { animation-delay: 0ms; }
        .animate-in:nth-child(2) { animation-delay: 60ms; }
        .animate-in:nth-child(3) { animation-delay: 120ms; }
        .animate-in:nth-child(4) { animation-delay: 180ms; }

        /* Count-up animation */
        .count-up {
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
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
                <a href="/dashboard" class="sidebar-link active">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="/brandlift" class="sidebar-link sidebar-link-cta">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Crear Brandlift
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
                <h1 class="top-header-title">Dashboard</h1>
                <div class="top-header-user">
                    <div class="top-header-user-info">
                        <div class="top-header-user-name">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="top-header-user-email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="top-header-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                </div>
            </header>

            <div class="content-area">
        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Total Brandlifts</span>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
                </div>
                <div class="kpi-value" id="kpi-total">—</div>
                <div class="kpi-sub">Estudios creados</div>
            </div>
            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Subidos a CM360</span>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                </div>
                <div class="kpi-value" id="kpi-pushed">—</div>
                <div class="kpi-sub">Publicados exitosamente</div>
            </div>
            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Este Mes</span>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                </div>
                <div class="kpi-value" id="kpi-month">—</div>
                <div class="kpi-sub" id="kpi-month-label">—</div>
            </div>

        </div>

        <!-- History Table Card -->
        <div class="card animate-in">
            <div class="card-header-row">
                <h2>
                    Historial de Brandlifts
                </h2>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input type="text" id="filter-search" class="filter-input" placeholder="🔍 Buscar por nombre de campaña...">
                <select id="filter-market" class="filter-select">
                    <option value="">Todos los mercados</option>
                    <option value="PE">Perú (PE)</option>
                    <option value="PRI">Puerto Rico (PRI)</option>
                    <option value="ARG">Argentina (ARG)</option>
                    <option value="MIA">Miami (MIA)</option>
                    <option value="MEX">México (MEX)</option>
                    <option value="CHL">Chile (CHL)</option>
                    <option value="COL">Colombia (COL)</option>
                    <option value="ECU">Ecuador (ECU)</option>
                </select>
                <select id="filter-status" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="created">Creado</option>
                    <option value="pushed">Subido a CM360</option>
                    <option value="error">Error</option>
                </select>
                <input type="date" id="filter-date-from" class="filter-input" style="min-width:140px;flex:0;" title="Fecha desde">
                <input type="date" id="filter-date-to" class="filter-input" style="min-width:140px;flex:0;" title="Fecha hasta">
                <button type="button" id="btn-clear-filters" class="btn-filter-clear">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Limpiar
                </button>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Campaña</th>
                            <th>Anunciante</th>
                            <th>Grupos de Audiencia</th>
                            <th>Tag Dps</th>
                            <th>Preguntas</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div id="empty-state" class="empty-state" style="display:none;">
                <div class="icon">📭</div>
                <h3>Sin brandlifts aún</h3>
                <p>Todavía no has creado ningún brandlift.<br>Crea tu primer estudio y aparecerá aquí.</p>
                <a href="/brandlift" class="btn-cta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Crear Brandlift
                </a>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar" id="pagination-bar" style="display:none;">
                <span class="pagination-info" id="pagination-info"></span>
                <div class="pagination-buttons" id="pagination-buttons"></div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detail-modal">
        <div class="modal">
            <div class="modal-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    Detalle del Brandlift
                </h3>
                <button class="modal-close" id="modal-close-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    <!-- Confirm Delete Dialog -->
    <div class="confirm-overlay" id="confirm-dialog">
        <div class="confirm-box">
            <div class="icon">🗑️</div>
            <h3>¿Eliminar Brandlift?</h3>
            <p id="confirm-message">Esta acción no se puede deshacer. El registro será eliminado permanentemente.</p>
            <div class="confirm-actions">
                <button class="btn-confirm-cancel" id="btn-confirm-cancel">Cancelar</button>
                <button class="btn-confirm-delete" id="btn-confirm-delete">Sí, eliminar</button>
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
    const STATUS_LABELS = { 'created': 'Creado', 'pushed': 'Subido', 'error': 'Error' };

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
        animateCounter('kpi-pushed', stats.pushed);
        animateCounter('kpi-month', stats.this_month);

        const now = new Date();
        $('#kpi-month-label').textContent = `Brandlifts en ${MONTHS_ES[now.getMonth()]}`;
    }

    function animateCounter(id, target) {
        const el = $(`#${id}`);
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

            return `
                <tr data-id="${study.id}" style="animation: fadeInUp 0.4s ease-out ${idx * 40}ms both">
                    <td class="td-id">${study.id}</td>
                    <td class="td-campaign" title="${escapeHtml(study.campaign_name)}">${escapeHtml(study.campaign_name)}</td>
                    <td title="${escapeHtml(study.client_name || '-')}">${escapeHtml(study.client_name || '-')}</td>
                    <td title="${escapeHtml(audiencesText || '-')}">${escapeHtml(audiencesText || '-')}</td>
                    <td title="${escapeHtml(dpsTagsText || '-')}">${escapeHtml(dpsTagsText || '-')}</td>
                    <td>${study.question_count}</td>
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
            if (s.cm360_pushed) {
                const pushedDate = s.cm360_pushed_at ? new Date(s.cm360_pushed_at).toLocaleString('es-ES') : '—';
                
                let downloadTagsHtml = '';
                if (s.cm360_tags) {
                    const tagsDataStr = typeof s.cm360_tags === 'string' ? escapeAttr(s.cm360_tags) : escapeAttr(JSON.stringify(s.cm360_tags));
                    downloadTagsHtml = `
                        <div style="margin-top: 15px;">
                            <button class="btn btn-secondary" onclick="downloadTagsFromDashboard(this)" data-tags="${tagsDataStr}" data-market="${escapeAttr(s.market)}" data-client="${escapeAttr(s.client_name || '')}" data-campaign="${escapeAttr(s.campaign_name)}" style="font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px; background: var(--wpp-teal); color: #fff; border: none; font-weight: 600;">
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
                                <div class="detail-value">${s.cm360_campaign_id || '—'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Subido</div>
                                <div class="detail-value">${pushedDate}</div>
                            </div>
                        </div>
                        ${downloadTagsHtml}
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

                ${previewHtml}
            `;
        } catch (e) {
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
</body>
</html>
