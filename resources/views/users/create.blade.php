<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario — Brandlift Automate</title>
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <style>
        /* ===== PAGE-SPECIFIC STYLES ===== */




        /* Form */
        .form-wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-card);
        }

        .header h1 {
            font-size: 28px;
            margin: 0;
        }

        .page-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            padding: 30px;
            box-shadow: var(--shadow-card);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        input, select {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: all var(--transition-fast);
        }

        input:focus, select:focus {
            border-color: var(--border-input-focus);
            box-shadow: 0 0 0 3px rgba(147, 223, 227, 0.15);
            background: var(--wpp-white);
        }

        button.btn-submit {
            width: 100%;
            background: var(--wpp-navy);
            color: var(--wpp-lime);
            border: none;
            padding: 14px;
            border-radius: var(--radius-sm);
            font-family: 'WPP', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
            transition: all var(--transition-fast);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.15);
        }

        button.btn-submit:hover {
            background: #000070;
            box-shadow: 0 6px 16px rgba(0, 0, 80, 0.2);
            transform: translateY(-1px);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            transition: color var(--transition-fast);
        }
        .back-link:hover { color: var(--wpp-navy); }
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
                <a href="/brandlift" class="sidebar-link sidebar-link-cta">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Crear Brandlift
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="/users" class="sidebar-link active">
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
                <h1 class="top-header-title">Crear Usuario</h1>
                <div class="top-header-user">
                    <div class="top-header-user-info">
                        <div class="top-header-user-name">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="top-header-user-email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="top-header-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                </div>
            </header>

            <div class="content-area">
        <div class="form-wrapper">
            <a href="{{ route('users.index') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver a Usuarios
            </a>

            <div class="header">
                <h1>Crear Nuevo Usuario</h1>
            </div>

            <div class="page-card">
                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nombre Completo</label>
                        <input type="text" id="name" name="name" placeholder="Ej: Juan Perez" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico (@wppmedia.com)</label>
                        <input type="email" id="email" name="email" placeholder="usuario@wppmedia.com" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Rol en la Plataforma</label>
                        <select id="role" name="role" required onchange="toggleMarketSelect()">
                            <option value="">Selecciona un rol...</option>
                            <option value="admin">Administrador (Acceso total)</option>
                            <option value="diseñador">Diseñador</option>
                            <option value="mercado">Mercado</option>
                        </select>
                    </div>

                    <div class="form-group" id="market-group" style="display: none;">
                        <label for="market">Mercado Asignado</label>
                        <select id="market" name="market">
                            <option value="">Selecciona un mercado...</option>
                            <option value="PE">Perú (PE)</option>
                            <option value="PRI">Puerto Rico (PRI)</option>
                            <option value="ARG">Argentina (ARG)</option>
                            <option value="MIA">Miami (MIA)</option>
                            <option value="MEX">México (MEX)</option>
                            <option value="CHL">Chile (CHL)</option>
                            <option value="COL">Colombia (COL)</option>
                            <option value="ECU">Ecuador (ECU)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Guardar Usuario</button>
                </form>
            </div>
        </div>
        </div>
            </div><!-- .content-area -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
    <script>
        function toggleMarketSelect() {
            const role = document.getElementById('role').value;
            const marketGroup = document.getElementById('market-group');
            const marketSelect = document.getElementById('market');
            if (role === 'mercado') {
                marketGroup.style.display = 'block';
                marketSelect.setAttribute('required', 'required');
            } else {
                marketGroup.style.display = 'none';
                marketSelect.removeAttribute('required');
                marketSelect.value = '';
            }
        }
    </script>
</body>
</html>
