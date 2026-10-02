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
        }
    </style>
</head>
<body>
    <div class="app-layout">
        @include('partials.sidebar', ['active' => 'users'])

        <!-- Main Content -->
        <main class="main-content">
            @include('partials.top-header', ['title' => 'Gestión de usuarios'])

            <div class="content-area">
                <div class="content-wrapper">
            <div class="header">
                <h1>Gestión de usuarios</h1>
                <a href="{{ route('users.create') }}" class="btn-nav">+ Nuevo usuario</a>
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
                            <th>Mercados asociados</th>
                            <th>Estado</th>
                            <th>Última conexión</th>
                            <th>Acceso directo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td style="color: var(--text-secondary)">{{ $user->email }}</td>
                            <td>
                                <span class="role-badge" style="{{ $user->role === 'admin' ? 'background: #e0f2fe; color: #0369a1; border-color: #bae6fd;' : ($user->role === 'local' ? 'background: #f0fdf4; color: #15803d; border-color: #bbf7d0;' : 'background: #faf5ff; color: #7e22ce; border-color: #f3e8ff;') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Todos (Global)</span>
                                @elseif(!empty($user->assigned_markets))
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        @foreach($user->assigned_markets as $mkt)
                                            <span style="background: var(--bg-secondary); border: 1px solid var(--border-input); padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                                {{ $mkt }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px;">Ninguno</span>
                                @endif
                            </td>
                            <td>
                                @if(($user->status ?? 'active') === 'active')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                        Activo
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td style="color: var(--text-secondary); font-size: 12px;">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Sin registro' }}
                            </td>
                            <td>
                                @if($user->login_token)
                                    <button type="button" class="btn-submit" onclick="copyLoginLink('{{ route('login.token', $user->login_token) }}', this)" style="padding: 4px 8px; font-size: 11px; background: var(--bg-secondary); color: var(--wpp-navy); border: 1px solid var(--border-input);">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        Copiar link
                                    </button>
                                @else
                                    <span style="color: var(--text-muted); font-size: 11px;">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-submit" style="padding: 4px 8px; font-size: 11px; height: auto;">Editar</a>
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding: 4px 8px; font-size: 11px;">Eliminar</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($users->isEmpty())
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">No hay usuarios registrados.</td>
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
    <script>
        function copyLoginLink(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '✓ ¡Copiado!';
                btn.style.background = '#dcfce7';
                btn.style.color = '#15803d';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.style.background = 'var(--bg-secondary)';
                    btn.style.color = 'var(--wpp-navy)';
                }, 2000);
            });
        }
    </script>
</body>
</html>
