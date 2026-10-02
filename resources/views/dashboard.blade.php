<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — historial de brandlifts</title>
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

        a, a:hover, a:focus {
            text-decoration: none !important;
        }

        /* ===== KPI CARDS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--bg-card);
            border: none;
            border-radius: var(--radius-md);
            padding: 18px 22px;
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
            transform: translateY(-3px);
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
            margin-bottom: 12px;
        }

        .kpi-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.2px;
        }

        .kpi-card.card-primary .kpi-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .kpi-icon {
            width: 34px; height: 34px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            color: var(--wpp-navy);
            flex-shrink: 0;
        }

        .kpi-icon svg {
            width: 18px;
            height: 18px;
        }

        .kpi-card.card-primary .kpi-icon {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
            color: var(--wpp-navy);
        }

        .kpi-card.card-primary .kpi-value {
            color: var(--wpp-white);
        }

        .kpi-sub {
            font-size: 12px;
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
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(0, 0, 80, 0.06);
            white-space: nowrap;
            position: sticky;
            top: 0;
            background: var(--bg-card);
            z-index: 2;
        }

        tbody tr {
            transition: all var(--transition-fast);
            cursor: pointer;
        }

        tbody tr:hover {
            background: rgba(176, 244, 103, 0.04);
        }

        tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(0, 0, 80, 0.04);
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
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== STATUS INDICATORS (Normalized, non-pill) ===== */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .status-indicator.active { color: #15803d; }
        .status-indicator.active .status-dot { background: #22c55e; box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2); }

        .status-indicator.finished { color: #64748b; }
        .status-indicator.finished .status-dot { background: #94a3b8; }

        .status-indicator.inactive { color: #94a3b8; }
        .status-indicator.inactive .status-dot { background: #cbd5e1; }

        .status-indicator.error { color: #dc2626; font-weight: 600; cursor: help; }
        .status-indicator.error .status-dot { background: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }

        /* ===== TABLE LINKS (Normalized, non-pill) ===== */
        .table-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
            transition: color var(--transition-fast), opacity var(--transition-fast);
        }

        .table-link-sheet {
            color: #15803d;
        }
        .table-link-sheet:hover {
            color: #166534;
            text-decoration: none;
            opacity: 0.85;
        }

        .table-link-cm360 {
            color: #0369a1;
        }
        .table-link-cm360:hover {
            color: #0c4a6e;
            text-decoration: none;
            opacity: 0.85;
        }

        .table-link .link-icon {
            font-size: 15px;
            opacity: 0.85;
        }

        .table-link .ext-icon {
            font-size: 13px;
            opacity: 0.6;
        }

        /* ===== FLOATING TOOLTIP (Never clipped by tables or headers) ===== */
        #floating-tooltip {
            position: fixed;
            z-index: 999999;
            background: #0f172a;
            color: #f8fafc;
            padding: 9px 13px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 400;
            line-height: 1.45;
            max-width: 320px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            white-space: normal;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        #floating-tooltip.visible {
            opacity: 1;
        }

        .floating-tooltip-title {
            font-weight: 600;
            color: #fca5a5;
            margin-bottom: 3px;
            font-size: 11.5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .row-error-hint {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #dc2626;
            margin-top: 4px;
            cursor: help;
        }

        .row-error-hint .error-icon {
            font-size: 13px;
            color: #ef4444;
            flex-shrink: 0;
        }

        /* ===== ACTION BUTTONS ===== */
        .actions-cell {
            display: flex;
            gap: 4px;
        }

        .btn-action {
            width: 30px; height: 30px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0, 0, 80, 0.08);
            background: #ffffff;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-action:hover {
            background: rgba(0, 0, 80, 0.05);
            color: var(--wpp-navy);
            border-color: rgba(0, 0, 80, 0.15);
        }

        .btn-action.warning:hover {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.25);
        }

        .btn-action.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.25);
        }
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
            border: 1px solid var(--border-card);
            border-radius: var(--radius-xl);
            padding: 0;
            max-width: 780px;
            width: 100%;
            max-height: 88vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0, 0, 80, 0.2), 0 0 0 1px rgba(0, 0, 80, 0.05);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 80, 0.15) transparent;
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-card);
            position: sticky;
            top: 0;
            background: var(--bg-card);
            z-index: 2;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .modal-header h3 {
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
        }

        .modal-close {
            width: 34px; height: 34px;
            border-radius: 50%;
            border: 1px solid var(--border-input);
            background: var(--bg-secondary);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .modal-body {
            padding: 24px;
        }

        /* ===== DETAIL MODAL SPECIFIC (ENLARGED & 2-COLUMN) ===== */
        #detail-modal .modal {
            max-width: 1240px;
            width: 95vw;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(0, 0, 80, 0.22), 0 0 0 1px rgba(0, 0, 80, 0.08);
        }

        #detail-modal .modal-header {
            padding: 16px 24px;
            flex-shrink: 0;
        }

        #detail-modal .modal-body {
            padding: 18px 24px 22px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 80, 0.15) transparent;
        }

        .detail-modal-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 370px;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .detail-modal-layout {
                grid-template-columns: 1fr;
            }
        }

        .detail-modal-left {
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-width: 0;
        }

        .detail-modal-right {
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: sticky;
            top: 0;
        }

        /* Summary Card */
        .detail-summary-card {
            background: var(--bg-secondary);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-lg);
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .detail-summary-card:hover {
            border-color: rgba(0, 0, 80, 0.28);
            background: #ffffff;
        }

        .detail-summary-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .detail-summary-title-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
            flex: 1;
        }

        .detail-summary-label {
            font-size: 10px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .detail-summary-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            word-break: break-all;
            line-height: 1.35;
        }

        .detail-summary-badges {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .detail-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px 14px;
            padding-top: 12px;
            border-top: 1px dashed rgba(0, 0, 80, 0.08);
        }

        .detail-meta-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .detail-meta-cell .meta-lbl {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .detail-meta-cell .meta-val {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* CM360 Bar */
        .detail-cm360-bar {
            background: var(--bg-secondary);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-md);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
        }

        .detail-cm360-title {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: var(--text-primary);
            font-size: 12px;
        }

        .detail-cm360-items {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .detail-cm360-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .detail-cm360-item .cm-lbl {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .detail-cm360-item .cm-val {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Audit Alert */
        .detail-audit-alert {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: var(--radius-md);
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .detail-audit-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #c53030;
            font-size: 12px;
        }

        /* Questions Section */
        .detail-questions-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-section-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .questions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 10px;
        }

        .question-card {
            background: var(--bg-secondary);
            border: 1px dashed var(--border-input);
            border-radius: var(--radius-md);
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .question-card:hover {
            border-color: rgba(0, 0, 80, 0.28);
            background: #ffffff;
        }

        .question-header {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .q-badge {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 100px;
            white-space: nowrap;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(176, 244, 103, 0.3);
            letter-spacing: 0.2px;
        }

        .q-title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.35;
        }

        .q-answers-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(115px, 1fr));
            gap: 5px;
        }

        .q-answer-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 80, 0.08);
            border-radius: var(--radius-sm);
            padding: 4px 7px;
            font-size: 11.5px;
            color: var(--text-secondary);
            min-width: 0;
        }

        .q-answer-pill .opt-letter {
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: rgba(0, 0, 80, 0.06);
            color: var(--wpp-navy);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .q-answer-pill span:last-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Pill Lists for metadata */
        .pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 2px;
        }

        .pill-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(0, 0, 80, 0.05);
            color: var(--wpp-navy);
            border: 1px solid rgba(0, 0, 80, 0.12);
            padding: 2px 7px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
        }

        .pill-tag-dps {
            background: rgba(84, 101, 255, 0.1);
            border-color: rgba(84, 101, 255, 0.25);
            color: var(--wpp-cornflower);
        }

        /* Actions Bar */
        .detail-actions-bar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            padding-top: 2px;
        }

        .btn-detail-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        /* Preview Panel */
        .detail-preview-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 18px rgba(0, 0, 80, 0.06);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .detail-preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid rgba(0, 0, 80, 0.06);
            background: var(--bg-secondary);
        }

        .detail-preview-header h4 {
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .detail-preview-header .preview-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .preview-stage {
            background: #090d16;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 260px;
            position: relative;
        }

        .preview-stage iframe {
            border: none;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            background: #ffffff;
            display: block;
        }

        .preview-footer-note {
            padding: 7px 12px;
            background: var(--bg-secondary);
            border-top: 1px solid rgba(0, 0, 80, 0.05);
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        @include('partials.sidebar', ['active' => 'dashboard'])

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            @include('partials.top-header', ['title' => 'Dashboard'])

            <div class="content-area">
        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Total de brandlifts</span>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div>
                </div>
                <div class="kpi-value" id="kpi-total">—</div>
                <div class="kpi-sub">Estudios creados</div>
            </div>


            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Este mes</span>
                    <div class="kpi-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                </div>
                <div class="kpi-value" id="kpi-month">—</div>
                <div class="kpi-sub" id="kpi-month-label">—</div>
            </div>

            <div class="kpi-card animate-in">
                <div class="kpi-header">
                    <span class="kpi-label">Brandlifts activos</span>
                    <div class="kpi-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="kpi-value" id="kpi-active">—</div>
                <div class="kpi-sub">En vigencia</div>
            </div>

        </div>

        <!-- History Table Card -->
        <div class="card animate-in">
            <div class="card-header-row">
                <h2>
                    Historial de brandlifts
                </h2>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input type="text" id="filter-search" class="filter-input" placeholder="Buscar por nombre de campaña o cliente...">
                @php
                    $dashUser = auth()->user();
                    $assignedMarkets = $dashUser?->assigned_markets ?? [];
                    $allMarketDefs = [
                        'PE' => 'Perú (PE)',
                        'PRI' => 'Puerto Rico (PRI)',
                        'ARG' => 'Argentina (ARG)',
                        'MIA' => 'Miami (MIA)',
                        'MEX' => 'México (MEX)',
                        'CHL' => 'Chile (CHL)',
                        'COL' => 'Colombia (COL)',
                        'ECU' => 'Ecuador (ECU)',
                    ];
                    $showMarketFilter = !$dashUser || $dashUser->isAdmin() || count($assignedMarkets) > 1;
                    if ($dashUser && !$dashUser->isAdmin() && !empty($assignedMarkets)) {
                        $filterMarketOptions = array_intersect_key($allMarketDefs, array_flip($assignedMarkets));
                    } else {
                        $filterMarketOptions = $allMarketDefs;
                    }
                @endphp
                @if($showMarketFilter)
                <select id="filter-market" class="filter-select">
                    <option value="">{{ count($assignedMarkets) > 1 ? 'Todos mis mercados' : 'Todos los mercados' }}</option>
                    @foreach($filterMarketOptions as $code => $name)
                        <option value="{{ $code }}" {{ session('active_market') === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @endif
                <select id="filter-status" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="created">Creado</option>
                    <option value="cm360_pushed">Subido</option>
                    <option value="error">Error</option>
                </select>
                <div class="filter-date-group" style="position: relative; min-width: 160px;">
                    <input type="text" id="filter-date-visual" class="filter-input" placeholder="Hasta (dd/mm/aaaa)" readonly style="cursor: pointer; width: 100%; padding-right: 36px;">
                    <input type="date" id="filter-date-to" class="filter-input" style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="
                        const visual = document.getElementById('filter-date-visual');
                        if(this.value) {
                            const [y, m, d] = this.value.split('-');
                            visual.value = `${d}/${m}/${y}`;
                        } else {
                            visual.value = '';
                        }
                    ">
                    <span class="material-symbols-outlined" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted); pointer-events: none;">calendar_today</span>
                </div>
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
                            <th>Campaña</th>
                            <th>Finaliza</th>
                            <th>Estado</th>
                            <th>Respuestas</th>
                            <th>Campaña CM360</th>
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
                <div class="icon"><span class="material-symbols-outlined" style="font-size: 48px; color: var(--text-muted);">inbox</span></div>
                <h3>No hay brandlift creados.</h3>
                <a href="/brandlift" class="btn-cta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Crear brandlift
                </a>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar" id="pagination-bar" style="display:none;">
                <span class="pagination-info" id="pagination-info"></span>
                <div class="pagination-buttons" id="pagination-buttons"></div>
            </div>
        </div>
    </div>

    <!-- Send Tags Email Modal -->
    <div id="send-tags-modal" class="modal-overlay">
        <div class="modal" style="max-width: 560px;">
            <div class="modal-header">
                <h3>
                    <span class="material-symbols-outlined" style="font-size: 20px; color: var(--wpp-navy); font-style: normal;">send</span>
                    Enviar tags por correo
                </h3>
                <button class="modal-close" onclick="closeSendTagsModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <input type="hidden" id="send-tags-study-id">
                
                <!-- View 1: Form View -->
                <div id="send-tags-form-view">
                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 11px; font-weight: 700; color: var(--text-muted);">Campaña</label>
                        <div id="send-tags-campaign-title" style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-top: 4px; word-break: break-all;"></div>
                    </div>

                    <!-- Google Sheet info block -->
                    <div id="send-tags-sheet-box" style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #16a34a; flex-shrink: 0;">description</span>
                            <div style="font-size: 12.5px;">
                                <strong style="color: #166534; display: block; font-size: 13px;">Hoja de respuestas (Google Sheets)</strong>
                                <span style="color: #15803d; line-height: 1.4; display: block;" id="send-tags-sheet-desc">El enlace directo a la hoja se enviará automáticamente en el correo.</span>
                            </div>
                        </div>
                        <a id="send-tags-sheet-link" href="#" target="_blank" style="color: #16a34a; font-weight: 700; font-size: 12px; white-space: nowrap; text-decoration: none; padding: 4px 10px; background: rgba(34, 197, 94, 0.14); border-radius: 4px;">Abrir hoja &rarr;</a>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="send-tags-emails" style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Destinatarios (uno o varios correos separados por comas o enter) <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea id="send-tags-emails" class="filter-input" rows="3" placeholder="ejemplo1@wppmedia.com, traffic@cliente.com" style="width: 100%; border-radius: var(--radius-sm); font-size: 13px; padding: 10px; resize: vertical; box-sizing: border-box;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="send-tags-message" style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Mensaje personalizado de indicaciones y recomendaciones
                        </label>
                        <textarea id="send-tags-message" class="filter-input" rows="4" style="width: 100%; border-radius: var(--radius-sm); font-size: 13px; padding: 10px; resize: vertical; box-sizing: border-box;">Estimado equipo,

Adjuntamos el archivo Excel con los tags de tráfico y especificaciones técnicas para la implementación de la campaña BrandLift, junto con el enlace a la hoja de Google Sheets donde se recibirán las respuestas.

Por favor verificar la correcta implementación antes del inicio de la pauta.</textarea>
                    </div>

                    <div style="background: var(--bg-secondary); border: 1px dashed var(--border-input); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span class="material-symbols-outlined" style="font-size: 24px; color: #16a34a;">table_chart</span>
                        <div style="font-size: 12px;">
                            <strong style="color: var(--text-primary);">Archivo adjunto automático:</strong>
                            <div style="color: var(--text-muted);" id="send-tags-attachment-name">Tags_BrandLift.xls (Formato Excel)</div>
                        </div>
                    </div>

                    <!-- Inline Error Alert in Modal -->
                    <div id="send-tags-error-alert" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 16px; color: #b91c1c; font-size: 12.5px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">error</span>
                        <span id="send-tags-error-text"></span>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="btn-cancel-send-tags" class="btn-cancel" onclick="closeSendTagsModal()" style="padding: 10px 18px; border-radius: var(--radius-sm); border: 1px solid var(--border-input); background: transparent; cursor: pointer; font-size: 13px; font-weight: 600;">Cancelar</button>
                        <button type="button" id="btn-submit-send-tags" onclick="submitSendTags()" style="padding: 10px 20px; border-radius: var(--radius-sm); border: none; background: var(--wpp-navy); color: var(--wpp-lime); cursor: pointer; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                            <span class="material-symbols-outlined" style="font-size: 16px; font-style: normal;">send</span>
                            <span>Enviar tags</span>
                        </button>
                    </div>
                </div>

                <!-- View 2: Success Notification View inside same modal -->
                <div id="send-tags-success-view" style="display: none; text-align: center; padding: 16px 8px 8px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">check_circle</span>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 8px 0;">¡Tags y enlace enviados exitosamente!</h3>
                    <p style="font-size: 13.5px; color: var(--text-secondary); margin: 0 0 16px 0; line-height: 1.5;">
                        Se ha enviado el correo con los tags en Excel y el enlace a la hoja de Google Sheets a los siguientes destinatarios:
                    </p>
                    <div id="send-tags-success-recipients" style="background: var(--bg-secondary); border: 1px solid var(--border-input); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 24px; font-size: 12.5px; color: var(--text-primary); text-align: left; max-height: 120px; overflow-y: auto;">
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <button type="button" onclick="closeSendTagsModal()" style="padding: 10px 28px; border-radius: var(--radius-sm); border: none; background: var(--wpp-navy); color: #ffffff; cursor: pointer; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                            <span>Cerrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detail-modal">
        <div class="modal">
            <div class="modal-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    Detalle del brandlift
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

    <!-- Resolution / Problem Correction Modal -->
    <div class="modal-overlay" id="resolution-modal">
        <div class="modal" style="max-width: 640px;">
            <div class="modal-header">
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 22px; color: #d97706;">build_circle</span>
                    <span>Diagnóstico y resolución</span>
                </h3>
                <button class="modal-close" onclick="closeResolutionModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" id="resolution-modal-body" style="padding: 24px;">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="toast-message"></span>
    </div>

    <!-- Global Floating Tooltip -->
    <div id="floating-tooltip" style="display:none;"></div>

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
    const STATUS_LABELS = { 'created': 'Creado', 'pushed': 'Subido', 'cm360_pushed': 'Subido', 'active': 'Activo', 'inactive': 'Desactivado', 'error': 'Error' };

    let currentPage = 1;
    let debounceTimer = null;

    // ===== FLOATING TOOLTIP (Never clipped by table headers/overflow) =====
    const floatingTooltip = $('#floating-tooltip');

    document.addEventListener('mouseover', (e) => {
        const target = e.target.closest('[data-tooltip]');
        if (!target || !floatingTooltip) return;

        const text = target.getAttribute('data-tooltip');
        const title = target.getAttribute('data-tooltip-title');
        if (!text) return;

        floatingTooltip.innerHTML = title
            ? `<div class="floating-tooltip-title"><span class="material-symbols-outlined" style="font-size:14px;color:#ef4444;">error</span><span>${escapeHtml(title)}</span></div><div>${escapeHtml(text)}</div>`
            : `<div>${escapeHtml(text)}</div>`;

        floatingTooltip.style.display = 'block';
        floatingTooltip.style.visibility = 'hidden';

        const rect = target.getBoundingClientRect();
        const tipRect = floatingTooltip.getBoundingClientRect();

        let left = rect.left;
        if (left + tipRect.width > window.innerWidth - 16) {
            left = window.innerWidth - tipRect.width - 16;
        }
        if (left < 16) left = 16;

        // Prefer placing below target (avoids collision with sticky thead)
        let top = rect.bottom + 8;
        if (top + tipRect.height > window.innerHeight - 16) {
            top = Math.max(16, rect.top - tipRect.height - 8);
        }

        floatingTooltip.style.left = `${left}px`;
        floatingTooltip.style.top = `${top}px`;
        floatingTooltip.style.visibility = 'visible';
        floatingTooltip.classList.add('visible');
    });

    document.addEventListener('mouseout', (e) => {
        const target = e.target.closest('[data-tooltip]');
        if (!target || !floatingTooltip) return;
        if (e.relatedTarget && target.contains(e.relatedTarget)) return;
        floatingTooltip.classList.remove('visible');
        floatingTooltip.style.display = 'none';
    });

    window.addEventListener('scroll', () => {
        if (floatingTooltip && floatingTooltip.style.display === 'block') {
            floatingTooltip.classList.remove('visible');
            floatingTooltip.style.display = 'none';
        }
    }, true);

    // ===== FETCH HISTORY =====
    async function fetchHistory(page = 1) {
        currentPage = page;
        const search = $('#filter-search') ? $('#filter-search').value.trim() : '';
        const market = $('#filter-market') ? $('#filter-market').value : '';
        const status = $('#filter-status') ? $('#filter-status').value : '';
        const dateTo = $('#filter-date-to') ? $('#filter-date-to').value : '';

        const params = new URLSearchParams();
        params.set('page', page);
        params.set('per_page', 15);
        if (search) params.set('search', search);
        if (market) params.set('market', market);
        if (status) params.set('status', status);
        if (dateTo) params.set('date_to', dateTo);

        // Show/hide clear button
        const hasFilters = search || market || status || dateTo;
        if ($('#btn-clear-filters')) {
            $('#btn-clear-filters').classList.toggle('visible', !!hasFilters);
        }

        // Show skeleton while loading
        showSkeleton();

        try {
            const res = await fetch(`/api/brandlift/history?${params.toString()}`);
            const data = await res.json();

            updateKPIs(data.stats);
            renderTable(data.studies);
        } catch (e) {
            console.error('Error fetching history:', e);
            showToast('Error al cargar el historial', true);
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

    // ===== ERROR HELPER =====
    function getStudyErrorInfo(study) {
        let full = study.error_message || '';
        let type = 'cm360';
        let short = 'Sincronización pendiente';

        if (!full) {
            if (!study.sheet_id) {
                type = 'sheet';
                short = 'Hoja de respuestas pendiente';
                full = 'No se ha creado o vinculado la hoja de cálculo de Google Sheets para almacenar respuestas.';
            } else if (!study.cm360_pushed || !study.cm360_campaign_id) {
                type = 'cm360';
                short = 'Sincronización pendiente';
                full = 'Los creativos interactivos están generados, pero aún no han sido sincronizados con Google Campaign Manager 360.';
            } else {
                type = 'general';
                short = 'Revisión requerida';
                full = 'El estudio requiere revisión de configuración para activarse completamente.';
            }
        } else {
            const lower = full.toLowerCase();
            if (lower.includes('sheet') || lower.includes('drive')) {
                type = 'sheet';
                short = 'Hoja de respuestas pendiente';
            } else if (lower.includes('cm360') || lower.includes('campaign') || lower.includes('advertiser') || lower.includes('perfil') || lower.includes('creative')) {
                type = 'cm360';
                short = 'Sincronización pendiente';
            } else if (lower.includes('tag')) {
                type = 'cm360';
                short = 'Tags pendientes';
            } else {
                type = 'general';
                short = 'Revisión requerida';
            }
        }

        return { short, full, type };
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
            const endDateStr = study.end_date ? new Date(study.end_date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const hasError = study.status === 'error';
            const errorInfo = hasError ? getStudyErrorInfo(study) : null;

            let vigenciaHtml = '-';
            if (study.status === 'inactive') {
                vigenciaHtml = `<span class="status-indicator inactive"><span class="status-dot"></span>Desactivado</span>`;
            } else if (hasError) {
                vigenciaHtml = `<span class="status-indicator error" onclick="event.stopPropagation(); openResolutionModal(${study.id})" style="cursor: pointer;" data-tooltip="Haz clic para diagnosticar y resolver" data-tooltip-title="${escapeAttr(errorInfo.short)}"><span class="status-dot"></span>${escapeHtml(errorInfo.short)}</span>`;
            } else if (study.end_date) {
                const now = new Date();
                now.setHours(0, 0, 0, 0);
                const [y, m, d] = study.end_date.split('-');
                const endDate = new Date(y, m - 1, d);

                if (endDate >= now) {
                    vigenciaHtml = `<span class="status-indicator active"><span class="status-dot"></span>Activo</span>`;
                } else {
                    vigenciaHtml = `<span class="status-indicator finished"><span class="status-dot"></span>Finalizado</span>`;
                }
            }

            const sheetUrl = study.sheet_id ? `https://docs.google.com/spreadsheets/d/${study.sheet_id}` : null;
            const sheetCell = sheetUrl
                ? `<a href="${sheetUrl}" target="_blank" rel="noopener noreferrer" class="table-link table-link-sheet" title="Abrir Google Sheet de respuestas" onclick="event.stopPropagation()">
                    <span class="material-symbols-outlined link-icon">table_chart</span>
                    <span>Google Sheet</span>
                    <span class="material-symbols-outlined ext-icon">open_in_new</span>
                   </a>`
                : (hasError
                    ? `<span style="color:#ef4444;font-size:12px;font-weight:500;cursor:pointer;" onclick="event.stopPropagation(); openResolutionModal(${study.id})" data-tooltip="Haz clic para vincular hoja de cálculo" data-tooltip-title="Hoja pendiente">No disponible</span>`
                    : `<span style="color:var(--text-muted);font-size:12px;">—</span>`);

            const accountId = study.cm360_account_id || '732535';
            const cm360Url = study.cm360_url || (study.cm360_campaign_id
                ? `https://campaignmanager.google.com/trafficking/#/accounts/${accountId}/campaigns/${study.cm360_campaign_id}/explorer?statuses=0;2`
                : null);

            const cm360Cell = cm360Url
                ? `<a href="${cm360Url}" target="_blank" rel="noopener noreferrer" class="table-link table-link-cm360" title="Abrir campaña ${study.cm360_campaign_id} en Google Campaign Manager 360" data-cm360-url="${cm360Url.replace(/"/g, '&quot;')}" onclick="event.stopPropagation(); openCm360Campaign(this); return false;">
                    <span>Ver campaña</span>
                    <span class="material-symbols-outlined ext-icon">open_in_new</span>
                   </a>`
                : `<span style="color:var(--text-muted);font-size:12px;">—</span>`;

            const marketName = MARKET_NAMES[study.market] || study.market;

            const advertiserCountryHtml = (study.client_name || study.market)
                ? `<div class="td-campaign-sub" style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px; display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                    <span style="font-weight: 500; color: var(--text-secondary);">${escapeHtml(study.client_name || 'Sin anunciante')}</span>
                    ${study.market ? `<span style="opacity: 0.4;">•</span><span>${escapeHtml(marketName || '')} <span style="opacity: 0.65;">· ${escapeHtml(study.market)}</span></span>` : ''}
                   </div>`
                : '';

            const errorAlertHtml = hasError
                ? `<div class="row-error-hint" onclick="event.stopPropagation(); openResolutionModal(${study.id})" data-tooltip="Haz clic para diagnosticar y resolver" data-tooltip-title="${escapeAttr(errorInfo.short)}" style="cursor: pointer;">
                    <span class="material-symbols-outlined error-icon">build</span>
                    <span>${escapeHtml(errorInfo.short)}</span>
                    <span style="font-size: 10px; opacity: 0.75; margin-left: 2px;">• Resolver</span>
                   </div>`
                : '';

            return `
                <tr data-id="${study.id}" style="animation: fadeInUp 0.4s ease-out ${idx * 40}ms both">
                    <td>
                        <div class="td-campaign" title="${escapeHtml(study.campaign_name)}">${escapeHtml(study.campaign_name)}</div>
                        ${advertiserCountryHtml}
                        ${errorAlertHtml}
                    </td>
                    <td>${endDateStr}</td>
                    <td>${vigenciaHtml}</td>
                    <td>${sheetCell}</td>
                    <td>${cm360Cell}</td>
                    <td>
                        <div class="actions-cell" onclick="event.stopPropagation()">
                            ${hasError ? `
                            <button type="button" class="btn-action warning" title="Resolver problema del brandlift" onclick="openResolutionModal(${study.id})" style="color: #c2410c; border-color: rgba(234, 88, 12, 0.3); background: rgba(254, 243, 199, 0.6);">
                                <span class="material-symbols-outlined" style="font-size: 16px;">build</span>
                            </button>
                            ` : ''}
                            <button type="button" class="btn-action" title="Enviar tags por correo" onclick="openSendTagsModal(${study.id})">
                                <span class="material-symbols-outlined" style="font-size: 16px;">mail</span>
                            </button>
                            <a href="/preview/${study.liquid_id || study.id}" target="_blank" class="btn-action" title="Vista previa pública (sitio simulado)">
                                <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                            </a>
                            <a href="/brandlift?edit_id=${study.liquid_id || study.id}" class="btn-action" title="Editar brandlift">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                            </a>
                            <button type="button" class="btn-action danger" title="Eliminar brandlift y su Google Sheet" onclick="deleteStudy(${study.id})">
                                <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
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
                <td>
                    <div class="skeleton-bar" style="width:200px; margin-bottom: 6px;"></div>
                    <div class="skeleton-bar" style="width:120px; height: 10px;"></div>
                </td>
                <td><div class="skeleton-bar" style="width:80px"></div></td>
                <td><div class="skeleton-bar" style="width:70px"></div></td>
                <td><div class="skeleton-bar" style="width:90px"></div></td>
                <td><div class="skeleton-bar" style="width:90px"></div></td>
                <td><div class="skeleton-bar" style="width:80px"></div></td>
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

        body.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;"><span class="material-symbols-outlined" style="font-size:32px; animation: spin 1.5s linear infinite; color: var(--wpp-navy);">progress_activity</span></div><p style="color:var(--text-muted)">Cargando historial...</p></div>';
        modal.classList.add('active');

        try {
            const res = await fetch(`/api/brandlift/history/${studyId}`);
            const data = await res.json();
            const logs = data.study.edit_logs || [];

            if (logs.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted)">No se han ejecutado cambios para este creativo.</div>';
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
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444;display:flex;align-items:center;justify-content:center;gap:6px;"><span class="material-symbols-outlined">error</span> Error al cargar el historial</div>';
        }
    }

    window.addEventListener('click', (e) => {
        if (e.target.id === 'history-modal') {
            closeHistoryModal();
        }
    });

    function openCm360Campaign(el) {
        const url = el.dataset.cm360Url || el.getAttribute('href');
        if (url) window.open(url, '_blank', 'noopener,noreferrer');
    }

    // ===== DETAIL MODAL =====
    async function openDetail(id) {
        const modal = $('#detail-modal');
        const body = $('#modal-body');

        body.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;"><span class="material-symbols-outlined" style="font-size:32px; animation: spin 1.5s linear infinite; color: var(--wpp-navy);">progress_activity</span></div><p style="color:var(--text-muted)">Cargando información guardada...</p></div>';
        modal.classList.add('active');

        try {
            const res = await fetch(`/api/brandlift/history/${id}`);
            const data = await res.json();
            const s = data.study;
            window.currentDetailStudy = s;

            // Error info
            const errorInfo = (s.status === 'error' || s.error_message) ? getStudyErrorInfo(s) : null;
            let errorAlertHtmlTop = '';
            if (errorInfo) {
                errorAlertHtmlTop = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: flex-start; gap: 10px;">
                        <span class="material-symbols-outlined" style="color: #ef4444; font-size: 20px; flex-shrink: 0; margin-top: 1px;">error</span>
                        <div style="font-size: 12.5px;">
                            <strong style="color: #991b1b; display: block; font-size: 13px;">${escapeHtml(errorInfo.short)}:</strong>
                            <span style="color: #b91c1c; line-height: 1.4; display: block; margin-top: 2px;">${escapeHtml(errorInfo.full)}</span>
                        </div>
                    </div>
                `;
            }

            // Fechas
            const createdDate = new Date(s.created_at);
            const createdDateStr = createdDate.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            const createdTimeStr = createdDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

            const monthNamesFull = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            let endDateStr = 'Sin fecha fin';
            let vigenciaBadge = '';
            if (s.status === 'inactive') {
                vigenciaBadge = `<span class="status-indicator inactive"><span class="status-dot"></span>Desactivado</span>`;
            } else if (s.status === 'error' || errorInfo) {
                vigenciaBadge = `<span class="status-indicator error" data-tooltip="${escapeAttr(errorInfo.full)}" data-tooltip-title="${escapeAttr(errorInfo.short)}"><span class="status-dot"></span>${escapeHtml(errorInfo.short)}</span>`;
            } else if (s.end_date) {
                const parts = s.end_date.split('-');
                const y = parseInt(parts[0]);
                const m = parseInt(parts[1]);
                const d = parseInt(parts[2]);
                endDateStr = `${d} de ${monthNamesFull[m - 1]} de ${y}`;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const endD = new Date(y, m - 1, d);
                if (endD >= today) {
                    vigenciaBadge = `<span class="status-indicator active"><span class="status-dot"></span>Activo</span>`;
                } else {
                    vigenciaBadge = `<span class="status-indicator finished"><span class="status-dot"></span>Finalizado</span>`;
                }
            } else {
                vigenciaBadge = `<span class="status-indicator active"><span class="status-dot"></span>Activo</span>`;
            }

            // Audiencias
            let audiencesHtml = '<span style="color:var(--text-muted);font-size:12px;">General</span>';
            if (Array.isArray(s.audiences) && s.audiences.length > 0) {
                audiencesHtml = `<div class="pill-list">${s.audiences.map(a => `<span class="pill-tag">${escapeHtml(a)}</span>`).join('')}</div>`;
            }

            // DSPs
            let dpsHtml = '<span style="color:var(--text-muted);font-size:12px;">—</span>';
            if (Array.isArray(s.dps_tags) && s.dps_tags.length > 0) {
                dpsHtml = `<div class="pill-list">${s.dps_tags.map(d => `<span class="pill-tag pill-tag-dps">${escapeHtml(d)}</span>`).join('')}</div>`;
            }

            // Google Sheets
            let sheetHtml = '<span style="color:var(--text-muted);font-size:12px;">No configurada</span>';
            if (s.sheet_id) {
                const shortSheetId = s.sheet_id.length > 18 ? s.sheet_id.substring(0, 14) + '...' : s.sheet_id;
                sheetHtml = `
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                        <a href="https://docs.google.com/spreadsheets/d/${escapeAttr(s.sheet_id)}" target="_blank" rel="noopener noreferrer" style="color:#15803d;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:600;font-size:12.5px;">
                            <span class="material-symbols-outlined" style="font-size:16px;color:#16a34a;">table_chart</span>
                            <span>Google Sheet (Respuestas)</span>
                            <span class="material-symbols-outlined" style="font-size:13px;opacity:0.6;">open_in_new</span>
                        </a>
                        <span style="font-size:11px;color:var(--text-muted);font-family:monospace;" title="${escapeAttr(s.sheet_id)}">${escapeHtml(shortSheetId)}</span>
                    </div>
                `;
            }

            // CM360
            let cm360Html = '';
            const detailAccountId = s.cm360_account_id || '732535';
            const detailCm360Url = s.cm360_url || (s.cm360_campaign_id 
                ? `https://campaignmanager.google.com/trafficking/#/accounts/${detailAccountId}/campaigns/${s.cm360_campaign_id}/explorer?statuses=0;2`
                : null);

            const hasCm360 = s.cm360_pushed || s.cm360_campaign_id || s.cm360_advertiser_id || s.cm360_profile_id;
            if (hasCm360) {
                const pushedDate = s.cm360_pushed_at ? new Date(s.cm360_pushed_at).toLocaleString('es-ES') : (s.cm360_pushed ? 'Sincronizado' : 'Pendiente');
                cm360Html = `
                    <div class="detail-cm360-bar">
                        <div class="detail-cm360-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            <span>Campaign Manager 360</span>
                        </div>
                        <div class="detail-cm360-items">
                            <div class="detail-cm360-item">
                                <span class="cm-lbl">Campaign:</span>
                                <span class="cm-val">${detailCm360Url ? `<a href="${detailCm360Url}" target="_blank" rel="noopener noreferrer" style="color:var(--wpp-navy);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">${escapeHtml(String(s.cm360_campaign_id))} <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>` : (s.cm360_campaign_id ? escapeHtml(String(s.cm360_campaign_id)) : '—')}</span>
                            </div>
                            <div class="detail-cm360-item">
                                <span class="cm-lbl">Advertiser:</span>
                                <span class="cm-val">${s.cm360_advertiser_id ? escapeHtml(String(s.cm360_advertiser_id)) : '—'}</span>
                            </div>
                            <div class="detail-cm360-item">
                                <span class="cm-lbl">Site:</span>
                                <span class="cm-val">${s.cm360_site_id ? escapeHtml(String(s.cm360_site_id)) : '—'}</span>
                            </div>
                            <div class="detail-cm360-item">
                                <span class="cm-lbl">Estado:</span>
                                <span class="cm-val">${pushedDate}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Preguntas y respuestas
            let questionsHtml = '';
            const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            if (s.questions && s.questions.length > 0) {
                questionsHtml = s.questions.map((q, idx) => {
                    const answersHtml = (q.answers || []).map((ans, aIdx) => `
                        <div class="q-answer-pill" title="${escapeAttr(ans)}">
                            <span class="opt-letter">${letters[aIdx] || (aIdx + 1)}</span>
                            <span>${escapeHtml(ans)}</span>
                        </div>
                    `).join('');

                    return `
                        <div class="question-card">
                            <div class="question-header">
                                <span class="q-badge">P${q.question_number || (idx + 1)}</span>
                                <div class="q-title">${escapeHtml(q.question_text)}</div>
                            </div>
                            <div class="q-answers-list">
                                ${answersHtml || '<span style="color:var(--text-muted);font-size:11px;">Sin opciones</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Click redirection removal (Audit alert)
            let clickActionHtmlTop = '';
            const firstQ = s.questions && s.questions.length > 0 ? s.questions[0] : null;
            if (firstQ && firstQ.creative_html && firstQ.creative_html.includes('clickTag')) {
                clickActionHtmlTop = `
                    <div class="detail-audit-alert">
                        <div class="detail-audit-msg">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span><strong>Audit:</strong> Recuerda retirar el evento de click después de pasar el proceso de auditoría</span>
                        </div>
                        <button style="background-color: var(--wpp-navy); color: white; border: none; padding: 6px 12px; font-size: 11.5px; font-weight: 600; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all 0.2s ease; white-space: nowrap;" onclick="removeClickEvent(${s.id})">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/></svg>
                            Retirar redirección de click
                        </button>
                    </div>
                `;
            }

            // Vista previa
            const previewCreativeHtml = (firstQ && firstQ.creative_html) 
                || (s.creatives && s.creatives.length > 0 && s.creatives[0].creative_html) 
                || null;
            const previewUrl = `/preview/${s.liquid_id || s.id}`;

            let previewHtml = '';
            if (previewCreativeHtml) {
                previewHtml = `
                    <div class="detail-preview-panel">
                        <div class="detail-preview-header">
                            <h4>
                                <span class="material-symbols-outlined" style="font-size: 18px; color: var(--wpp-navy);">visibility</span>
                                Vista previa
                            </h4>
                            <div class="preview-actions">
                                <button type="button" class="btn-detail-action btn-restart-preview" onclick="const f=document.querySelector('#dash-preview-frame iframe'); if(f){const src=f.srcdoc; f.srcdoc=''; setTimeout(()=>f.srcdoc=src,10);} this.style.display='none';" style="display: none; font-size: 11px; padding: 4px 9px; background: transparent; border: 1px solid var(--border-input); color: var(--text-secondary);">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    Reiniciar
                                </button>
                                <a href="${previewUrl}" target="_blank" title="Ver en simulador de sitio web" class="btn-detail-action" style="font-size: 11px; padding: 4px 9px; background: transparent; border: 1px solid var(--border-input); color: var(--text-secondary);">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Simulador
                                </a>
                            </div>
                        </div>
                        <div class="preview-stage" id="dash-preview-frame">
                            <iframe srcdoc="${escapeAttr(previewCreativeHtml)}" width="${s.creative_width}" height="${s.creative_height}"></iframe>
                        </div>
                    </div>
                `;
            } else {
                previewHtml = `
                    <div class="detail-preview-panel">
                        <div class="detail-preview-header">
                            <h4>
                                <span class="material-symbols-outlined" style="font-size: 18px; color: var(--wpp-navy);">visibility</span>
                                Vista previa
                            </h4>
                            <a href="${previewUrl}" target="_blank" class="btn-detail-action" style="font-size: 11px; padding: 4px 9px; background: transparent; border: 1px solid var(--border-input); color: var(--text-secondary);">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Simulador
                            </a>
                        </div>
                        <div class="preview-stage" id="dash-preview-frame" style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px; min-height: 200px;">
                            <span>El banner interactivo se genera al guardar el estudio. Puedes abrir el simulador para visualizar la experiencia.</span>
                        </div>
                    </div>
                `;
            }

            const editBtnHtml = `
                <a href="/brandlift?edit_id=${s.liquid_id || s.id}" class="btn-detail-action" style="background: var(--wpp-navy); color: #ffffff; border: none; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border-radius: var(--radius-md);">
                    <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                    Editar
                </a>
            `;

            // Audit Logs History Timeline
            let auditLogHtml = '';
            if (s.edit_logs && s.edit_logs.length > 0) {
                const logsList = s.edit_logs.map(log => {
                    const logDate = new Date(log.created_at);
                    const logDateStr = logDate.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    const userName = (log.user && log.user.name) || (log.changes_made && log.changes_made.user_name) || 'Usuario';
                    const action = log.changes_made ? (log.changes_made.action || 'Modificación') : 'Modificación';
                    const desc = log.changes_made ? (log.changes_made.description || 'Cambios guardados') : 'Modificación registrada';

                    let actionBadge = `<span style="background:rgba(3,105,161,0.12);color:#0369a1;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">Modificación</span>`;
                    if (action === 'created') actionBadge = `<span style="background:rgba(22,163,74,0.12);color:#15803d;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">Creación</span>`;
                    if (action === 'send_tags') actionBadge = `<span style="background:rgba(217,119,6,0.12);color:#b45309;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">Tags enviados</span>`;

                    return `
                        <div style="padding: 8px 12px; background: var(--bg-secondary); border-radius: var(--radius-sm); border: 1px solid var(--border-input); font-size: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="color: var(--text-primary); font-size: 12px;">${escapeHtml(userName)}</strong>
                                    ${actionBadge}
                                </div>
                                <span style="color: var(--text-muted); font-size: 11px;">${logDateStr}</span>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 12px;">${escapeHtml(desc)}</div>
                        </div>
                    `;
                }).join('');

                auditLogHtml = `
                    <div class="detail-questions-section" style="margin-top: 16px;">
                        <h4 class="detail-section-title">
                            <span class="material-symbols-outlined" style="font-size: 16px;">history</span>
                            Historial y log de auditoría (${s.edit_logs.length})
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            ${logsList}
                        </div>
                    </div>
                `;
            }

            body.innerHTML = `
                <div class="detail-modal-layout">
                    <!-- Left Column: Summary, Questions, Actions -->
                    <div class="detail-modal-left">
                        ${errorAlertHtmlTop}
                        ${clickActionHtmlTop}
                        
                        <div class="detail-summary-card">
                            <div class="detail-summary-top">
                                <div class="detail-summary-title-group">
                                    <span class="detail-summary-label">Campaña</span>
                                    <span class="detail-summary-name">${escapeHtml(s.campaign_name)}</span>
                                </div>
                                <div class="detail-summary-badges">
                                    <span style="font-size:12px;color:var(--text-secondary);font-weight:600;">${escapeHtml(MARKET_NAMES[s.market] || s.market)}</span>
                                    ${vigenciaBadge}
                                </div>
                            </div>

                            <div class="detail-meta-grid">
                                <div class="detail-meta-cell">
                                    <span class="meta-lbl">Anunciante / Cliente</span>
                                    <span class="meta-val">${escapeHtml(s.client_name || '—')}</span>
                                </div>
                                <div class="detail-meta-cell">
                                    <span class="meta-lbl">Finaliza</span>
                                    <span class="meta-val">${endDateStr}</span>
                                </div>
                                <div class="detail-meta-cell">
                                    <span class="meta-lbl">Creado el</span>
                                    <span class="meta-val">${createdDateStr} <span style="color:var(--text-muted);font-size:11px;">(${createdTimeStr})</span></span>
                                </div>
                                <div class="detail-meta-cell">
                                    <span class="meta-lbl">DSPs / Plataformas</span>
                                    <div class="meta-val">${dpsHtml}</div>
                                </div>
                            </div>
                        </div>

                        ${cm360Html}

                        <div class="detail-questions-section">
                            <h4 class="detail-section-title">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Preguntas y Respuestas (${s.questions ? s.questions.length : 0})
                            </h4>
                            <div class="questions-grid">
                                ${questionsHtml || '<p style="color:var(--text-muted);font-size:12px;margin:0;">Sin preguntas registradas</p>'}
                            </div>
                        </div>

                        ${auditLogHtml}

                        <div class="detail-actions-bar">
                            ${editBtnHtml}
                        </div>
                    </div>

                    <!-- Right Column: Creative Preview -->
                    <div class="detail-modal-right">
                        ${previewHtml}
                    </div>
                </div>
            `;
        } catch (e) {
            console.error('Error in openDetail:', e);
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444;display:flex;align-items:center;justify-content:center;gap:6px;"><span class="material-symbols-outlined">error</span> Error al cargar los detalles</div>';
        }
    }

    function closeModal() {
        $('#detail-modal').classList.remove('active');
    }

    $('#modal-close-btn').addEventListener('click', closeModal);
    $('#detail-modal').addEventListener('click', (e) => {
        if (e.target === $('#detail-modal')) closeModal();
    });

    // ===== RESOLUTION MODAL =====
    function closeResolutionModal() {
        $('#resolution-modal').classList.remove('active');
    }

    $('#resolution-modal').addEventListener('click', (e) => {
        if (e.target === $('#resolution-modal')) closeResolutionModal();
    });

    async function openResolutionModal(studyId) {
        const modal = $('#resolution-modal');
        const body = $('#resolution-modal-body');

        body.innerHTML = `
            <div style="text-align:center;padding:40px;">
                <div style="font-size:24px;margin-bottom:12px;">
                    <span class="material-symbols-outlined" style="font-size:32px; animation: spin 1.5s linear infinite; color: var(--wpp-navy);">progress_activity</span>
                </div>
                <p style="color:var(--text-muted);font-size:13px;">Analizando estado del brandlift...</p>
            </div>
        `;
        modal.classList.add('active');

        try {
            const res = await fetch(`/api/brandlift/history/${studyId}`);
            const data = await res.json();
            const s = data.study;
            window.currentResolvingStudy = s;

            const errorInfo = getStudyErrorInfo(s);
            const marketName = MARKET_NAMES[s.market] || s.market;

            let resolutionActionHtml = '';

            if (errorInfo.type === 'sheet') {
                // Sheet resolution
                resolutionActionHtml = `
                    <div style="background: var(--bg-card); border: 1px solid var(--border-input); border-radius: var(--radius-md); padding: 18px; margin-top: 14px;">
                        <h4 style="font-size: 13.5px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="color: #16a34a; font-size: 18px;">table_chart</span>
                            Opción 1: Generar hoja de cálculo automática
                        </h4>
                        <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.45; margin-bottom: 12px;">
                            Crea una nueva hoja de cálculo en Google Drive en la carpeta de <strong>${escapeHtml(marketName)}</strong> basada en la plantilla estándar de respuestas.
                        </p>
                        <button type="button" id="btn-resolve-sheet" onclick="executeResolveSheet(${s.id})" style="background: #16a34a; color: white; border: none; padding: 8px 16px; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="font-size: 15px;">add</span>
                            <span>Generar Google Sheet ahora</span>
                        </button>

                        <div style="margin: 18px 0 14px; border-top: 1px dashed var(--border-input);"></div>

                        <h4 style="font-size: 13.5px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="color: #0284c7; font-size: 18px;">link</span>
                            Opción 2: Vincular hoja existente
                        </h4>
                        <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.45; margin-bottom: 10px;">
                            Si ya dispones de una hoja creada en Google Drive para esta campaña, ingresa su URL o ID:
                        </p>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="resolve-sheet-url" class="filter-input" placeholder="https://docs.google.com/spreadsheets/d/1.../edit" style="flex: 1; font-size: 12.5px; padding: 7px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-input);">
                            <button type="button" onclick="executeLinkManualSheet(${s.id})" style="background: var(--wpp-navy); color: white; border: none; padding: 7px 14px; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 600; cursor: pointer;">
                                Vincular
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // CM360 Sync resolution
                resolutionActionHtml = `
                    <div style="background: var(--bg-card); border: 1px solid var(--border-input); border-radius: var(--radius-md); padding: 18px; margin-top: 14px;">
                        <h4 style="font-size: 13.5px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="color: #0369a1; font-size: 18px;">sync</span>
                            Sincronización con Google Campaign Manager 360
                        </h4>
                        <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.45; margin-bottom: 12px;">
                            Los creativos HTML están generados. Puedes reintentar la subida o ajustar los parámetros de CM360:
                        </p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px;">ID de Perfil</label>
                                <input type="text" id="res-cm-profile" value="${escapeAttr(s.cm360_profile_id || '732535')}" class="filter-input" style="width: 100%; font-size: 12px; padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-input); box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px;">ID de Anunciante</label>
                                <input type="text" id="res-cm-advertiser" value="${escapeAttr(s.cm360_advertiser_id || '')}" placeholder="ID Anunciante" class="filter-input" style="width: 100%; font-size: 12px; padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-input); box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px;">ID de Sitio</label>
                                <input type="text" id="res-cm-site" value="${escapeAttr(s.cm360_site_id || '')}" placeholder="ID Sitio" class="filter-input" style="width: 100%; font-size: 12px; padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-input); box-sizing: border-box;">
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <button type="button" id="btn-resolve-sync" onclick="executeResolveSync(${s.id})" style="background: var(--wpp-navy); color: #ffffff; border: none; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">cloud_upload</span>
                                <span>Reintentar sincronización ahora</span>
                            </button>

                            <a href="/brandlift?edit_id=${s.liquid_id || s.id}" class="btn-detail-action" style="font-size: 12px; color: var(--text-secondary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-outlined" style="font-size: 15px;">tune</span>
                                <span>Abrir configurador completo</span>
                            </a>
                        </div>
                    </div>
                `;
            }

            body.innerHTML = `
                <!-- Study Info Header -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <div>
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Campaña</span>
                            <div style="font-size: 14px; font-weight: 700; color: var(--wpp-navy); margin-top: 1px;">${escapeHtml(s.campaign_name)}</div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                ${escapeHtml(s.client_name || 'Sin anunciante')} • ${escapeHtml(marketName)} (${escapeHtml(s.market)})
                            </div>
                        </div>
                        <span style="background: rgba(234, 88, 12, 0.12); color: #c2410c; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; white-space: nowrap;">
                            ${escapeHtml(errorInfo.short)}
                        </span>
                    </div>
                </div>

                <!-- Diagnostic Explanation -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: #d97706; font-size: 18px; flex-shrink: 0; margin-top: 1px;">info</span>
                        <div style="font-size: 12px; color: #92400e; line-height: 1.45;">
                            <strong style="display: block; font-size: 12.5px; margin-bottom: 2px; color: #78350f;">Diagnóstico:</strong>
                            ${escapeHtml(errorInfo.full)}
                        </div>
                    </div>
                    ${s.error_message ? `
                        <div style="margin-top: 8px; padding-top: 6px; border-top: 1px dashed rgba(217, 119, 6, 0.3); font-size: 11px; color: #b45309; font-family: monospace; word-break: break-all;">
                            <strong>Detalle técnico:</strong> ${escapeHtml(s.error_message)}
                        </div>
                    ` : ''}
                </div>

                <!-- Feedback alert slot -->
                <div id="res-feedback-slot"></div>

                <!-- Resolution Action Box -->
                ${resolutionActionHtml}
            `;
        } catch (e) {
            console.error('Error in openResolutionModal:', e);
            body.innerHTML = `
                <div style="text-align:center;padding:30px;color:#ef4444;">
                    <span class="material-symbols-outlined" style="font-size:32px;">error</span>
                    <p style="margin-top:8px;">No se pudo cargar la información para resolución.</p>
                </div>
            `;
        }
    }

    async function executeResolveSync(studyId) {
        const btn = document.getElementById('btn-resolve-sync');
        const feedback = document.getElementById('res-feedback-slot');
        const profileId = document.getElementById('res-cm-profile')?.value?.trim();
        const advertiserId = document.getElementById('res-cm-advertiser')?.value?.trim();
        const siteId = document.getElementById('res-cm-site')?.value?.trim();

        if (!profileId || !advertiserId || !siteId) {
            feedback.innerHTML = `
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 6px; font-size: 12px; margin-bottom: 12px;">
                    Por favor completa los 3 identificadores de CM360 (Perfil, Anunciante y Sitio).
                </div>
            `;
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px; animation: spin 1s linear infinite;">progress_activity</span> Sincronizando con CM360...`;
        feedback.innerHTML = '';

        try {
            const res = await fetch(`/api/brandlift/${studyId}/retry-sync`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    profile_id: profileId,
                    advertiser_id: advertiserId,
                    site_id: siteId
                })
            });

            const data = await res.json();

            if (data.success) {
                feedback.innerHTML = `
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 500; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: #16a34a;">check_circle</span>
                        <span>${escapeHtml(data.message)}</span>
                    </div>
                `;
                btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">check</span> Sincronizado`;
                showToast('¡Brandlift sincronizado exitosamente con CM360!');
                
                fetchHistory(currentPage);

                setTimeout(() => {
                    closeResolutionModal();
                }, 1600);
            } else {
                feedback.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px 14px; border-radius: 6px; font-size: 12px; margin-bottom: 12px;">
                        <strong>Fallo en la sincronización:</strong><br>
                        ${escapeHtml(data.message || 'Error desconocido al comunicar con CM360')}
                    </div>
                `;
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">cloud_upload</span> Reintentar de nuevo`;
            }
        } catch (e) {
            console.error(e);
            feedback.innerHTML = `
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 6px; font-size: 12px; margin-bottom: 12px;">
                    Error de conexión al reintentar la sincronización.
                </div>
            `;
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">cloud_upload</span> Reintentar sincronización`;
        }
    }

    async function executeResolveSheet(studyId) {
        const btn = document.getElementById('btn-resolve-sheet');
        const feedback = document.getElementById('res-feedback-slot');

        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px; animation: spin 1s linear infinite;">progress_activity</span> Creando Google Sheet...`;
        feedback.innerHTML = '';

        try {
            const res = await fetch(`/api/brandlift/${studyId}/link-sheet`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            const data = await res.json();
            if (data.success) {
                feedback.innerHTML = `
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 500; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: #16a34a;">check_circle</span>
                        <span>${escapeHtml(data.message)}</span>
                    </div>
                `;
                btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">check</span> Hoja creada`;
                showToast('¡Hoja de cálculo generada y vinculada!');
                fetchHistory(currentPage);
                setTimeout(() => {
                    closeResolutionModal();
                }, 1600);
            } else {
                feedback.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 6px; font-size: 12px; margin-bottom: 12px;">
                        ${escapeHtml(data.message || 'Error al crear la hoja')}
                    </div>
                `;
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">add</span> Generar Google Sheet ahora`;
            }
        } catch (e) {
            console.error(e);
            feedback.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px; border-radius: 6px; font-size: 12px;">Error de conexión.</div>`;
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;">add</span> Generar Google Sheet ahora`;
        }
    }

    async function executeLinkManualSheet(studyId) {
        const input = document.getElementById('resolve-sheet-url');
        const feedback = document.getElementById('res-feedback-slot');
        const sheetVal = input ? input.value.trim() : '';

        if (!sheetVal) {
            feedback.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 8px 12px; border-radius: 6px; font-size: 12px; margin-bottom: 10px;">Por favor ingresa la URL o ID de la hoja de Google Sheets.</div>`;
            return;
        }

        try {
            const res = await fetch(`/api/brandlift/${studyId}/link-sheet`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ sheet_id: sheetVal })
            });

            const data = await res.json();
            if (data.success) {
                feedback.innerHTML = `
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 500; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: #16a34a;">check_circle</span>
                        <span>${escapeHtml(data.message)}</span>
                    </div>
                `;
                showToast('¡Hoja vinculada exitosamente!');
                fetchHistory(currentPage);
                setTimeout(() => {
                    closeResolutionModal();
                }, 1600);
            } else {
                feedback.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 8px 12px; border-radius: 6px; font-size: 12px; margin-bottom: 10px;">${escapeHtml(data.message)}</div>`;
            }
        } catch (e) {
            console.error(e);
            feedback.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 8px 12px; border-radius: 6px; font-size: 12px;">Error al vincular.</div>`;
        }
    }

    // ===== SEND TAGS VIA EMAIL =====
    function openSendTagsModal(id, campaignName = '', sheetId = '', sheetUrl = '') {
        const study = window.allStudies?.find(s => s.id == id);
        if (study) {
            campaignName = campaignName || study.campaign_name || '';
            sheetId = sheetId || study.sheet_id || '';
            sheetUrl = sheetUrl || study.google_sheet_url || '';
        }
        campaignName = campaignName || 'BrandLift';
        $('#send-tags-study-id').value = id;
        $('#send-tags-campaign-title').textContent = campaignName;
        const cleanName = campaignName.replace(/[^A-Za-z0-9_\-]/g, '_');
        $('#send-tags-attachment-name').textContent = `Tags_BrandLift_${cleanName}.xls (Formato Excel)`;

        // Google Sheet box
        const sheetBox = $('#send-tags-sheet-box');
        const sheetLink = $('#send-tags-sheet-link');
        const sheetDesc = $('#send-tags-sheet-desc');
        const finalUrl = sheetUrl || (sheetId ? `https://docs.google.com/spreadsheets/d/${sheetId}/edit` : '');

        if (sheetBox) {
            sheetBox.style.display = 'flex';
            if (finalUrl) {
                sheetDesc.textContent = 'El enlace directo a la hoja de Google Sheets se incluirá automáticamente en el correo.';
                if (sheetLink) {
                    sheetLink.href = finalUrl;
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
        const formView = $('#send-tags-form-view');
        const successView = $('#send-tags-success-view');
        const errorAlert = $('#send-tags-error-alert');

        if (formView) formView.style.display = 'block';
        if (successView) successView.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';

        const btn = $('#btn-submit-send-tags');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px;">send</span><span>Enviar tags</span>';
        }

        const btnCancel = $('#btn-cancel-send-tags');
        if (btnCancel) {
            btnCancel.disabled = false;
            btnCancel.style.opacity = '1';
            btnCancel.style.cursor = 'pointer';
        }

        const emailsInput = $('#send-tags-emails');
        const msgInput = $('#send-tags-message');
        if (emailsInput) emailsInput.disabled = false;
        if (msgInput) msgInput.disabled = false;

        $('#send-tags-modal').classList.add('active');
    }

    function closeSendTagsModal() {
        $('#send-tags-modal').classList.remove('active');
    }

    async function submitSendTags() {
        const id = $('#send-tags-study-id').value;
        const emails = $('#send-tags-emails').value.trim();
        const message = $('#send-tags-message').value.trim();

        const errorAlert = $('#send-tags-error-alert');
        const errorText = $('#send-tags-error-text');
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

        const btn = $('#btn-submit-send-tags');
        const btnCancel = $('#btn-cancel-send-tags');
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ emails, message })
            });

            const data = await res.json();
            if (data.success) {
                // Notificación de envío en la misma modal
                const formView = $('#send-tags-form-view');
                const successView = $('#send-tags-success-view');

                if (formView) formView.style.display = 'none';
                if (successView) successView.style.display = 'block';

                const recipients = data.recipients || emails.split(/[\s,;]+/).filter(Boolean);
                const recipientsHtml = recipients.map(e => `
                    <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; margin: 3px 4px 3px 0;">
                        ${escapeHtml(e)}
                    </span>
                `).join('');

                const recipientsContainer = $('#send-tags-success-recipients');
                if (recipientsContainer) {
                    recipientsContainer.innerHTML = recipientsHtml;
                }

                if (emailsInput) emailsInput.value = '';
                showToast(data.message || 'Tags enviados exitosamente');

                // Refrescar tabla en segundo plano
                if (typeof fetchHistory === 'function') {
                    fetchHistory(currentPage);
                }
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

    // ===== REMOVE CLICK EVENT =====
    async function removeClickEvent(id, name = '') {
        if (!name) {
            name = window.allStudies?.find(s => s.id == id)?.campaign_name || 'este brandlift';
        }
        if (!confirm(`¿Estás seguro de que deseas retirar la redirección de click para "${name}"?\n\nSi está subida a CM360, esto tardará unos segundos mientras se actualizan los creativos.`)) {
            return;
        }

        const modalBody = $('#modal-body');
        modalBody.innerHTML = '<div style="text-align:center;padding:40px;"><div style="font-size:24px;margin-bottom:12px;"><span class="material-symbols-outlined" style="font-size:32px; animation: spin 1.5s linear infinite; color: var(--wpp-navy);">progress_activity</span></div><p style="color:var(--text-muted)">Actualizando creativos en Base de Datos y CM360...</p><p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Por favor espera, no cierres esta ventana.</p></div>';

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
                showToast(data.message);
                openDetail(id); // Reload modal details
            } else {
                showToast('Completado con advertencias: ' + (data.message || 'Error desconocido'), true);
                if (data.cm360_errors && data.cm360_errors.length > 0) {
                    console.error("CM360 Errors:", data.cm360_errors);
                }
                openDetail(id);
            }
        } catch (e) {
            showToast('Error al retirar el evento', true);
            openDetail(id);
        }
    }

    // ===== DELETE BRANDLIFT STUDY =====
    window.deleteStudy = async function(id, name = '') {
        if (!name) {
            name = window.allStudies?.find(s => s.id == id)?.campaign_name || 'este brandlift';
        }
        if (!confirm(`¿Estás seguro de que deseas eliminar el brandlift "${name}"?\n\nEsta acción también eliminará permanentemente la hoja de respuestas vinculada en Google Sheets.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/brandlift/history/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast(data.message || 'Brandlift eliminado correctamente');
                fetchHistory(currentPage);
            } else {
                showToast(data.message || 'Error al eliminar el brandlift', true);
            }
        } catch (e) {
            console.error('Error deleting study:', e);
            showToast('Error al eliminar el brandlift', true);
        }
    };

    // ===== FILTERS =====
    if ($('#filter-search')) {
        $('#filter-search').addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchHistory(1), 350);
        });
    }

    if ($('#filter-market')) {
        $('#filter-market').addEventListener('change', () => fetchHistory(1));
    }
    if ($('#filter-status')) {
        $('#filter-status').addEventListener('change', () => fetchHistory(1));
    }
    if ($('#filter-date-to')) {
        $('#filter-date-to').addEventListener('change', () => fetchHistory(1));
    }

    if ($('#btn-clear-filters')) {
        $('#btn-clear-filters').addEventListener('click', () => {
            if ($('#filter-search')) $('#filter-search').value = '';
            if ($('#filter-market')) $('#filter-market').value = '';
            if ($('#filter-status')) $('#filter-status').value = '';
            if ($('#filter-date-visual')) $('#filter-date-visual').value = '';
            if ($('#filter-date-to')) $('#filter-date-to').value = '';
            $('#btn-clear-filters').classList.remove('visible');
            fetchHistory(1);
        });
    }

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
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
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
            const fileName = `${year}_${month}_WMSCSLATAM_${market}_${client.replace(/\s+/g,'_')}_${campaignName}_Tags_brandlift_b`;
            
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
<!-- History Modal -->
<div class="modal-overlay" id="history-modal">
    <div class="modal" style="max-width: 600px; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                Historial de Edición
            </h3>
            <button class="modal-close" onclick="closeHistoryModal()">×</button>
        </div>
        <div class="modal-body" id="history-modal-body" style="max-height: 400px; overflow-y: auto;">
            <div style="text-align:center;padding:40px;"><p style="color:var(--text-muted)">Cargando...</p></div>
        </div>
    </div>
</div>

<div id="edit-questions-modal" class="modal-overlay hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,80,0.5); backdrop-filter: blur(8px); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
    <div style="background: var(--bg-card); width: 950px; max-width: 95%; max-height: 90vh; border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-modal); padding: 32px; position: relative; display: flex; gap: 30px;">
        <button onclick="closeEditQuestionsModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(0,0,80,0.05); border: none; border-radius: 50%; width: 32px; height: 32px; color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: all 0.2s;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        
        <!-- Left: Form -->
        <div style="flex: 1; overflow-y: auto; padding-right: 15px;">
            <h3 style="color: var(--wpp-navy); font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 20px;">Editar Preguntas</h3>
            
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

            
            <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; margin-bottom: 10px; align-self: flex-start;">Vista previa</label>
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

<script>
let editingStudy = null;

async function openEditQuestionsModal(studyId) {
    if (window.currentDetailStudy && Number(window.currentDetailStudy.id) === Number(studyId)) {
        editingStudy = window.currentDetailStudy;
    } else {
        try {
            const res = await fetch(`/api/brandlift/history/${studyId}`);
            const data = await res.json();
            editingStudy = data.study;
        } catch (e) {
            console.error('Error loading study for edit:', e);
            return;
        }
    }
    if (!editingStudy || !editingStudy.questions) return;

    // Set theme
    const theme = editingStudy.theme_colors || 'dark';
    document.querySelector(`input[name="edit-theme"][value="${theme}"]`).checked = true;

    // Render questions
    renderEditQuestions();

    const modal = document.getElementById('edit-questions-modal');
    modal.classList.remove('hidden');
    
    // Set initial preview
    updateModalPreview();

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
                    <input type="text" class="form-input edit-q-text" value="${escapeAttr(q.question_text || q.question || '')}" placeholder="Escribe la pregunta" style="width: 100%; margin-bottom: 15px;" oninput="updateModalPreview()" required>
                    
                    <label style="color: var(--text-secondary); font-size: 12px; margin-bottom: 8px; display: block;">Respuestas</label>
                    <div class="edit-answers-container" style="display: flex; flex-direction: column; gap: 8px;">
                        ${q.answers.map((ans, aIdx) => `
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="color: var(--text-secondary); font-size: 12px;">${aIdx + 1}.</span>
                                <input type="text" class="form-input edit-a-text" value="${escapeAttr(ans)}" style="width: 100%;" oninput="updateModalPreview()" required>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', qHtml);
        });
    }

    function updateModalPreview() {
        if (!editingStudy) return;

        const blocks = document.querySelectorAll('.question-block');
        const newQuestions = [];
        
        for (const block of blocks) {
            const qText = block.querySelector('.edit-q-text').value;
            const aTexts = Array.from(block.querySelectorAll('.edit-a-text')).map(i => i.value);
            newQuestions.push({ question: qText, answers: aTexts });
        }

        const theme = document.querySelector('input[name="edit-theme"]:checked').value;
        
        const html = generateCreativeHTML(
            newQuestions,
            editingStudy.creative_width || 300,
            editingStudy.creative_height || 250,
            editingStudy.campaign_name,
            editingStudy.market,
            '',
            'Ad_Exposed',
            theme
        );

        const iframe = document.getElementById('edit-modal-preview-iframe');
        if (iframe) {
            iframe.srcdoc = html;
            iframe.width = editingStudy.creative_width || 300;
            iframe.height = editingStudy.creative_height || 250;
        }
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
    updateModalPreview();
}

function removeEditQuestion(idx) {
    editingStudy.questions.splice(idx, 1);
    renderEditQuestions();
    updateModalPreview();
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
 var webhookUrl = ""; 
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
   <div onclick="storeAnswer('${qNum}', '${q.question}', '${a}'); setTimeout(function(){ showScreen('${nextScreen}'); ${nextScreen === 'screen-thanks' ? 'submitAnswers();' : ''} }, 300);" class="btn-anim" style="background:${btnBg};border-radius:100px;padding:${btnPadding};text-align:center;cursor:pointer;font-family:Arial,Helvetica,sans-serif;font-size:${btnFontSize}px;font-weight:700;color:${theme==='dark'?'#000050':'#ffffff'};letter-spacing:0.3px;width:80%;max-width:${btnMaxWidth}px;box-sizing:border-box;box-shadow:0 4px 10px rgba(0,0,0,0.15);">${a}</div>`;
        });

        html += `
  </div>
 </div>`;
    });

    html += `
 <div id="screen-thanks" class="screen hidden" style="justify-content:center;padding-top:20px;">
  <div style="${textStyle}font-size:22px;font-weight:800;text-align:center;line-height:1.4;margin-bottom:15px;text-shadow:0 2px 4px rgba(0,0,0,0.3);">¡Muchas gracias<br>por su opinión!</div>
 </div>
 <div style="position:absolute;bottom:10px;right:14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:${isDark?'rgba(255,255,255,0.6)':'rgba(0,0,80,0.6)'};letter-spacing:0.5px;z-index:2;"><span style="font-weight:800;">WPP</span><span style="font-weight:400;"> Media</span></div>
</div>
<\/body>
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
</body>
</html>
