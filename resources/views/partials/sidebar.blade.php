<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">WPP MEDIA SOLUTIONS<br><span style="color: var(--wpp-lime); font-size: 14px; font-weight: normal; margin-top: 4px; display: inline-block;">| Creative Services LATAM</span></div>

    <div class="sidebar-section">
        <span class="sidebar-section-title">Menú</span>
        <a href="/dashboard" class="sidebar-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="/brandlift" class="sidebar-link sidebar-link-cta {{ ($active ?? '') === 'brandlift' ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Crear brandlift
        </a>
        <a href="/reports" class="sidebar-link {{ ($active ?? '') === 'reports' ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            Reportes
        </a>
    </div>

    <div class="sidebar-section" style="margin-top: 20px;">
        <span class="sidebar-section-title">Recursos</span>
        <a href="https://drive.google.com/drive/folders/1OIeg01OAC5SsBW4i5XQqtGUCrs6eAj_p" target="_blank" class="sidebar-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
            Ver data en Google Sheets
        </a>
    </div>

    @if(auth()->check() && auth()->user()->role === 'admin')
    <div class="sidebar-section" style="margin-top: 20px;">
        <span class="sidebar-section-title">Administración</span>
        <a href="/users" class="sidebar-link {{ ($active ?? '') === 'users' ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuarios
        </a>
    </div>
    @endif

    <div class="sidebar-spacer"></div>

    <div class="sidebar-footer">
        <div class="sidebar-footer-brand">
            WPP Media Solutions LATAM {{ date('Y') }}
        </div>
    </div>
</aside>
