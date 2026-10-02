<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrandLift Builder by Creative Services LATAM: {{ $study->campaign_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wpp-navy: #000050;
            --wpp-lime: #b0f467;
            --wpp-cyan: #93dfe3;
            --preview-bar-bg: #000035;
            --site-bg: #f8fafc;
            --site-card-bg: #ffffff;
            --site-text-main: #0f172a;
            --site-text-muted: #64748b;
            --site-border: #e2e8f0;
            --ad-slot-border: rgba(176, 244, 103, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0f172a;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ===== TOP TOOLBAR (Brandlift Controls) ===== */
        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--preview-bar-bg);
            border-bottom: 1px solid rgba(176, 244, 103, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            color: #ffffff;
            flex-wrap: wrap;
        }

        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .toolbar-brand-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
            white-space: nowrap;
        }

        .toolbar-brand-title .brand-main {
            font-size: 13.5px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .toolbar-brand-title .brand-sub {
            color: #94a3b8;
            font-weight: 500;
            font-size: 11px;
            line-height: 1.2;
        }

        .toolbar-divider {
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.16);
            flex-shrink: 0;
        }

        .toolbar-title-group {
            display: flex;
            flex-direction: column;
        }

        .toolbar-campaign {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-meta {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .device-selector {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }

        .device-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .device-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .device-btn.active {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            font-weight: 700;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-tb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-tb-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-tb-secondary:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-tb-primary {
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            border: none;
            font-weight: 700;
        }

        .btn-tb-primary:hover {
            background: #c3f789;
            transform: translateY(-1px);
        }

        .variant-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            outline: none;
            cursor: pointer;
        }

        .variant-select option {
            background: #000050;
            color: #ffffff;
        }

        /* ===== VIEWPORT SIMULATION CONTAINER ===== */
        .preview-stage {
            flex: 1;
            padding: 30px 16px 60px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: #090e17;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(176, 244, 103, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(147, 223, 227, 0.05) 0%, transparent 40%);
        }

        .simulated-viewport {
            width: 100%;
            max-width: 1240px;
            background: var(--site-bg);
            border-radius: 12px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08);
            overflow: hidden;
            transition: max-width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ===== MOCK WEBSITE DESIGN ===== */
        .mock-site-header {
            background: #ffffff;
            border-bottom: 1px solid var(--site-border);
        }

        .mock-top-strip {
            background: #0f172a;
            color: #94a3b8;
            padding: 6px 32px;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mock-top-strip .strip-links {
            display: flex;
            gap: 16px;
        }

        .mock-nav-main {
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--site-border);
        }

        .mock-logo {
            font-family: 'Newsreader', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #0f172a;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mock-logo span {
            color: #2563eb;
            font-style: italic;
        }

        .mock-nav-links {
            display: flex;
            gap: 24px;
            list-style: none;
        }

        .mock-nav-links li a {
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .mock-nav-links li a:hover, .mock-nav-links li.active a {
            color: #0f172a;
        }

        .mock-sub-nav {
            padding: 10px 32px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 12px;
            color: #64748b;
            overflow-x: auto;
            white-space: nowrap;
        }

        .mock-sub-nav .trending-tag {
            background: #0f172a;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* ===== ARTICLE & AD LAYOUT ===== */
        .mock-content-grid {
            padding: 36px 32px 60px;
            max-width: 1140px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 40px;
        }

        /* When viewport is smaller or mobile */
        .simulated-viewport.is-mobile .mock-content-grid,
        .simulated-viewport.is-tablet .mock-content-grid {
            grid-template-columns: 1fr;
            padding: 20px 16px 40px;
        }

        .simulated-viewport.is-mobile .mock-nav-links,
        .simulated-viewport.is-mobile .mock-top-strip {
            display: none;
        }

        .article-body {
            min-width: 0;
        }

        .article-category {
            display: inline-block;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .article-title {
            font-family: 'Newsreader', serif;
            font-size: 38px;
            line-height: 1.18;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .simulated-viewport.is-mobile .article-title {
            font-size: 26px;
        }

        .article-lead {
            font-size: 18px;
            line-height: 1.55;
            color: #475569;
            margin-bottom: 24px;
            font-weight: 400;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--site-border);
            margin-bottom: 28px;
        }

        .author-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #000050);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
        }

        .author-info {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .article-date {
            font-size: 12px;
            color: #64748b;
        }

        .article-paragraph {
            font-size: 16px;
            line-height: 1.75;
            color: #334155;
            margin-bottom: 20px;
        }

        .article-heading {
            font-family: 'Newsreader', serif;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 32px 0 16px;
        }

        .article-quote {
            border-left: 3px solid #2563eb;
            padding: 12px 20px;
            margin: 24px 0;
            background: #f1f5f9;
            border-radius: 0 8px 8px 0;
            font-family: 'Newsreader', serif;
            font-size: 19px;
            font-style: italic;
            color: #1e293b;
            line-height: 1.5;
        }

        /* ===== AD SLOT CONTAINER ===== */
        .ad-sidebar-slot {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .ad-card-wrapper {
            background: #ffffff;
            border: 1px solid var(--site-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .ad-tag-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            justify-content: center;
        }

        .ad-tag-label::before, .ad-tag-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .ad-iframe-frame {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            background: #0a1628;
            position: relative;
        }

        .ad-iframe-frame iframe {
            display: block;
            border: none;
        }

        .ad-slot-footer {
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            font-size: 11px;
            color: #94a3b8;
        }

        .btn-ad-reload {
            background: transparent;
            border: none;
            color: #2563eb;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-ad-reload:hover {
            text-decoration: none;
            opacity: 0.8;
        }

        /* In-Article mobile ad slot */
        .mobile-ad-placeholder {
            display: none;
            margin: 28px 0;
            width: 100%;
            justify-content: center;
        }

        .simulated-viewport.is-mobile .mobile-ad-placeholder,
        .simulated-viewport.is-tablet .mobile-ad-placeholder {
            display: flex;
        }

        .simulated-viewport.is-mobile .ad-sidebar-slot,
        .simulated-viewport.is-tablet .ad-sidebar-slot {
            display: none;
        }

        /* ===== AD SPACE PLACEHOLDERS ===== */
        .mock-ad-top-wrapper {
            padding: 24px 32px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border-bottom: 1px solid var(--site-border);
        }

        .mock-ad-bottom-wrapper {
            padding: 20px 32px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .in-article-ad-slot {
            margin: 28px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .ad-placeholder {
            width: 100%;
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            color: #64748b;
            text-align: center;
            transition: all 0.2s ease;
        }

        .ad-placeholder::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(203, 213, 225, 0.18),
                rgba(203, 213, 225, 0.18) 12px,
                transparent 12px,
                transparent 24px
            );
            pointer-events: none;
        }

        .ad-placeholder:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }

        .ad-placeholder-tag {
            position: absolute;
            top: 6px;
            right: 8px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            z-index: 2;
        }

        .ad-placeholder-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px;
            z-index: 1;
        }

        .ad-placeholder-icon {
            color: #94a3b8;
        }

        .ad-placeholder-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #475569;
        }

        .ad-placeholder-specs {
            font-size: 11px;
            font-weight: 600;
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(37, 99, 235, 0.18);
        }

        .ad-placeholder-sub {
            font-size: 10px;
            color: #94a3b8;
        }

        /* Dimension variations */
        .ad-top-slot {
            max-width: 970px;
            height: 120px;
        }

        .ad-in-article-slot {
            max-width: 728px;
            height: 100px;
        }

        .ad-sidebar-secondary-slot {
            max-width: 100%;
            height: 250px;
            margin-top: 24px;
        }

        .ad-bottom-slot {
            max-width: 970px;
            height: 100px;
        }

        .mobile-only-text {
            display: none;
        }

        /* Tablet responsiveness */
        .simulated-viewport.is-tablet .ad-top-slot {
            max-width: 728px;
            height: 90px;
        }

        .simulated-viewport.is-tablet .ad-bottom-slot {
            max-width: 728px;
            height: 90px;
        }

        .simulated-viewport.is-tablet .mock-ad-top-wrapper {
            padding: 16px 20px 10px;
        }

        /* Mobile responsiveness */
        .simulated-viewport.is-mobile .mock-ad-top-wrapper {
            padding: 12px 14px 8px;
        }

        .simulated-viewport.is-mobile .mock-ad-bottom-wrapper {
            padding: 16px 14px 24px;
        }

        .simulated-viewport.is-mobile .ad-top-slot {
            max-width: 320px;
            height: 70px;
        }

        .simulated-viewport.is-mobile .ad-in-article-slot {
            max-width: 100%;
            height: 80px;
        }

        .simulated-viewport.is-mobile .ad-bottom-slot {
            max-width: 320px;
            height: 60px;
        }

        .simulated-viewport.is-mobile .ad-placeholder-icon {
            display: none;
        }

        .simulated-viewport.is-mobile .ad-placeholder-title {
            font-size: 10px;
        }

        .simulated-viewport.is-mobile .ad-placeholder-specs {
            font-size: 10px;
            padding: 1px 6px;
        }

        .simulated-viewport.is-mobile .ad-placeholder-sub {
            display: none;
        }

        .simulated-viewport.is-mobile .desktop-only-text {
            display: none;
        }

        .simulated-viewport.is-mobile .mobile-only-text {
            display: inline-block;
        }

        /* Mock site footer */
        .mock-site-footer {
            background: #ffffff;
            border-top: 1px solid var(--site-border);
            padding: 24px 32px;
            color: #64748b;
            font-size: 12px;
        }

        .mock-footer-inner {
            max-width: 1140px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .mock-footer-brand {
            font-family: 'Newsreader', serif;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .mock-footer-brand span {
            color: #2563eb;
            font-style: italic;
        }

        .mock-footer-links {
            display: flex;
            gap: 16px;
            font-size: 11px;
            color: #94a3b8;
        }

        /* Toast notification */
        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #000050;
            color: #ffffff;
            border: 1px solid var(--wpp-lime);
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 9999;
        }

        .toast-notification.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- ===== TOOLBAR ===== -->
    <header class="preview-toolbar">
        <div class="toolbar-brand">
            <div class="toolbar-brand-title">
                <span class="brand-main">BrandLift Builder</span>
                <span class="brand-sub">by Creative Services LATAM</span>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-title-group">
                <div class="toolbar-campaign">{{ $study->campaign_name }}</div>
                <div class="toolbar-meta">
                    <span>{{ $study->client_name ?? 'Anunciante' }}</span>
                    <span>•</span>
                    <span>{{ $study->market_name }} ({{ $study->market }})</span>
                    <span>•</span>
                    <span>{{ $study->creative_width ?? 300 }}x{{ $study->creative_height ?? 250 }}</span>
                </div>
            </div>
        </div>

        <!-- Device switcher -->
        <div class="device-selector">
            <button type="button" class="device-btn active" data-width="1240" data-mode="desktop" onclick="setDeviceMode('desktop', 1240)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <span>Desktop</span>
            </button>
            <button type="button" class="device-btn" data-width="768" data-mode="tablet" onclick="setDeviceMode('tablet', 768)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Tablet</span>
            </button>
            <button type="button" class="device-btn" data-width="375" data-mode="mobile" onclick="setDeviceMode('mobile', 375)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Mobile</span>
            </button>
            <button type="button" class="device-btn" data-width="100%" data-mode="responsive" onclick="setDeviceMode('responsive', '100%')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                <span>Full</span>
            </button>
        </div>

        <!-- Actions -->
        <div class="toolbar-actions">
            @if($study->creatives->count() > 1)
                <select id="variant-switcher" class="variant-select" onchange="switchVariant(this.value)">
                    @foreach($study->creatives as $cr)
                        <option value="{{ $cr->id }}">Variante: {{ $cr->variant_key }}</option>
                    @endforeach
                </select>
            @endif

            <button type="button" class="btn-tb btn-tb-secondary" onclick="reloadAdFrame()" title="Reiniciar creativo interactivo">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                Reiniciar
            </button>

            <button type="button" class="btn-tb btn-tb-primary" onclick="copyShareLink()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                Compartir Preview
            </button>
        </div>
    </header>

    <!-- ===== STAGE ===== -->
    <main class="preview-stage">
        <div id="simulated-viewport" class="simulated-viewport">
            
            <!-- Mock Site Navigation -->
            <header class="mock-site-header">
                <div class="mock-top-strip">
                    <div>Lorem Ipsum • {{ now()->translatedFormat('d F Y') }}</div>
                    <div class="strip-links">
                        <span>Lorem</span>
                        <span>Ipsum</span>
                        <span>Dolor</span>
                    </div>
                </div>
                <div class="mock-nav-main">
                    <div class="mock-logo">
                        Creative <span>Services</span>
                    </div>
                    <ul class="mock-nav-links">
                        <li class="active"><a href="#lorem">Lorem</a></li>
                        <li><a href="#ipsum">Ipsum</a></li>
                        <li><a href="#dolor">Dolor</a></li>
                        <li><a href="#sit">Sit Amet</a></li>
                        <li><a href="#consectetur">Consectetur</a></li>
                        <li><a href="#adipiscing">Adipiscing</a></li>
                    </ul>
                </div>
                <div class="mock-sub-nav">
                    <span class="trending-tag">Lorem</span>
                    <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit</span>
                    <span>•</span>
                    <span>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</span>
                    <span>•</span>
                    <span>Ut enim ad minim veniam quis nostrud</span>
                </div>
            </header>

            <!-- Top Leaderboard / Billboard Ad Placeholder -->
            <div class="mock-ad-top-wrapper">
                <div class="ad-placeholder ad-top-slot">
                    <div class="ad-placeholder-body">
                        <span class="ad-placeholder-title">Creative Services LATAM</span>
                        <span class="ad-placeholder-specs desktop-only-text">970×250 / 970×90 / 728×90</span>
                        <span class="ad-placeholder-specs mobile-only-text">320×100 / 320×50</span>
                    </div>
                </div>
            </div>

            <!-- Mock Article & Ad Container -->
            <div class="mock-content-grid">
                
                <!-- Article Column -->
                <article class="article-body">
                    <span class="article-category">Lorem Ipsum Dolor</span>
                    <h1 class="article-title">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt</h1>
                    <p class="article-lead">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    
                    <div class="article-meta">
                        <div class="author-avatar">LI</div>
                        <div class="author-info">
                            <span class="author-name">Lorem Ipsum</span>
                            <span class="article-date">Lorem ipsum dolor • 4 min</span>
                        </div>
                    </div>

                    <p class="article-paragraph">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                    </p>

                    <!-- Mobile / Tablet In-Article Ad Slot -->
                    <div class="mobile-ad-placeholder">
                        <div class="ad-card-wrapper" style="max-width: 330px;">
                            <div class="ad-iframe-frame" style="width: {{ $study->creative_width ?? 300 }}px; height: {{ $study->creative_height ?? 250 }}px;">
                                <iframe id="mobile-ad-iframe" src="{{ route('brandlift.public-creative-html', ['id' => $study->liquid_id]) }}" width="{{ $study->creative_width ?? 300 }}" height="{{ $study->creative_height ?? 250 }}" scrolling="no" loading="eager" allow="autoplay; fullscreen"></iframe>
                            </div>
                        </div>
                    </div>

                    <p class="article-paragraph">
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
                    </p>

                    <!-- In-Article Ad Space Placeholder -->
                    <div class="in-article-ad-slot">
                        <div class="ad-placeholder ad-in-article-slot">
                            <div class="ad-placeholder-body">
                                <span class="ad-placeholder-title">Creative Services LATAM</span>
                                <span class="ad-placeholder-specs">728×90 / 300×250</span>
                            </div>
                        </div>
                    </div>

                    <div class="article-quote">
                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam."
                    </div>

                    <h2 class="article-heading">Lorem ipsum dolor sit amet</h2>
                    <p class="article-paragraph">
                        Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.
                    </p>
                    <p class="article-paragraph">
                        Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur.
                    </p>
                </article>

                <!-- Desktop Sidebar Ad Slot -->
                <aside class="ad-sidebar-slot">
                    <div class="ad-card-wrapper">
                        <div class="ad-iframe-frame" style="width: {{ $study->creative_width ?? 300 }}px; height: {{ $study->creative_height ?? 250 }}px;">
                            <iframe id="desktop-ad-iframe" src="{{ route('brandlift.public-creative-html', ['id' => $study->liquid_id]) }}" width="{{ $study->creative_width ?? 300 }}" height="{{ $study->creative_height ?? 250 }}" scrolling="no" loading="eager" allow="autoplay; fullscreen"></iframe>
                        </div>
                    </div>

                    <!-- Secondary Sidebar Ad Space Placeholder -->
                    <div class="ad-placeholder ad-sidebar-secondary-slot">
                        <div class="ad-placeholder-body">
                            <span class="ad-placeholder-title">Creative Services LATAM</span>
                            <span class="ad-placeholder-specs">300×250 / 300×600</span>
                        </div>
                    </div>
                </aside>

            </div>

            <!-- Bottom Ad Banner Placeholder -->
            <div class="mock-ad-bottom-wrapper">
                <div class="ad-placeholder ad-bottom-slot">
                    <div class="ad-placeholder-body">
                        <span class="ad-placeholder-title">Creative Services LATAM</span>
                        <span class="ad-placeholder-specs desktop-only-text">970×90 / 728×90</span>
                        <span class="ad-placeholder-specs mobile-only-text">320×50</span>
                    </div>
                </div>
            </div>

            <!-- Mock Site Footer -->
            <footer class="mock-site-footer">
                <div class="mock-footer-inner">
                    <div class="mock-footer-brand">Creative <span>Services</span></div>
                    <div style="font-size:11px;color:#94a3b8;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
                    <div class="mock-footer-links">
                        <span>Lorem</span>
                        <span>•</span>
                        <span>Ipsum</span>
                        <span>•</span>
                        <span>Dolor</span>
                    </div>
                </div>
            </footer>

        </div>
    </main>

    <!-- Toast -->
    <div id="toast" class="toast-notification">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--wpp-lime)" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span id="toast-message">Enlace de preview copiado al portapapeles</span>
    </div>

    <script>
        let currentCreativeId = null;

        function setDeviceMode(mode, width) {
            document.querySelectorAll('.device-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });

            const viewport = document.getElementById('simulated-viewport');
            viewport.classList.remove('is-mobile', 'is-tablet', 'is-desktop');
            
            if (mode === 'mobile') {
                viewport.classList.add('is-mobile');
                viewport.style.maxWidth = '375px';
            } else if (mode === 'tablet') {
                viewport.classList.add('is-tablet');
                viewport.style.maxWidth = '768px';
            } else if (mode === 'desktop') {
                viewport.classList.add('is-desktop');
                viewport.style.maxWidth = '1240px';
            } else {
                viewport.style.maxWidth = '100%';
            }
        }

        function reloadAdFrame() {
            const baseSrc = currentCreativeId 
                ? `/preview/{{ $study->liquid_id }}/creative/${currentCreativeId}`
                : `/preview/{{ $study->liquid_id }}/creative`;

            const cacheBust = '?t=' + new Date().getTime();

            const desktopIframe = document.getElementById('desktop-ad-iframe');
            if (desktopIframe) desktopIframe.src = baseSrc + cacheBust;

            const mobileIframe = document.getElementById('mobile-ad-iframe');
            if (mobileIframe) mobileIframe.src = baseSrc + cacheBust;
        }

        function switchVariant(creativeId) {
            currentCreativeId = creativeId;
            reloadAdFrame();
        }

        function copyShareLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast("¡Enlace de preview copiado al portapapeles!");
            }).catch(() => {
                showToast("Copia este enlace: " + window.location.href);
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }
    </script>
</body>
</html>
