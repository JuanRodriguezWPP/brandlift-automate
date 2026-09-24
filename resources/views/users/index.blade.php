<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios — Brandlift Automate</title>
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <style>
        /* ===== PAGE-SPECIFIC STYLES ===== */




        .content-wrapper {
            max-width: 1000px;
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

        .btn-nav {
            background: var(--wpp-navy);
            color: var(--wpp-lime);
            text-decoration: none;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 14px;
            display: inline-block;
            transition: all var(--transition-fast);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.15);
        }
        .btn-nav:hover {
            background: #000070;
            color: var(--wpp-lime);
            box-shadow: 0 6px 16px rgba(0, 0, 80, 0.2);
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .btn-submit {
            background-color: var(--wpp-cornflower);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-submit:hover {
            background-color: var(--wpp-navy);
            color: var(--wpp-lime);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .page-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-card);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        th {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 14px;
            font-weight: 400;
        }

        .role-badge {
            background: rgba(147, 223, 227, 0.15);
            color: #0e6e74;
            padding: 6px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 500;
            border: 1px solid rgba(147, 223, 227, 0.3);
            text-transform: capitalize;
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
                <h1 class="top-header-title">Usuarios</h1>
                <div class="top-header-user">
                    <div class="top-header-user-info">
                        <div class="top-header-user-name">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="top-header-user-email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="top-header-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                </div>
            </header>

            <div class="content-area">
                <div class="content-wrapper">
            <div class="header">
                <h1>Gestión de Usuarios</h1>
                <a href="{{ route('users.create') }}" class="btn-nav">+ Nuevo Usuario</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="page-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Mercado</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td style="color: var(--text-secondary)">{{ $user->email }}</td>
                            <td><span class="role-badge">{{ $user->role }}</span></td>
                            <td style="color: var(--text-secondary)">{{ $user->market ?? '-' }}</td>
                            <td style="color: var(--text-secondary);">{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-submit" style="padding: 4px 10px; font-size: 12px; height: auto;">Editar</a>
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($users->isEmpty())
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No hay usuarios registrados.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
            </div><!-- .content-area -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
</body>
</html>
