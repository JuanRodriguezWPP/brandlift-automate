<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario — Brandlift Automate</title>
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

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
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
        @include('partials.sidebar', ['active' => 'users'])

        <!-- Main Content -->
        <main class="main-content">
            @include('partials.top-header', ['title' => 'Editar usuario'])

            <div class="content-area">
        <div class="form-wrapper">
            <a href="{{ route('users.index') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver a usuarios
            </a>

            <div class="header">
                <h1>Editar usuario</h1>
            </div>

            <div class="page-card">
                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Ej: Juan Perez" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email">Correo electrónico (@wppmedia.com)</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="usuario@wppmedia.com" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="role">Rol en la plataforma</label>
                        <select id="role" name="role" required onchange="toggleMarketSelect()">
                            <option value="">Selecciona un rol...</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador (acceso total)</option>
                            <option value="local" {{ in_array(old('role', $user->role), ['local', 'mercado']) ? 'selected' : '' }}>Local (acceso por mercado/s asignados)</option>
                            <option value="diseñador" {{ in_array(old('role', $user->role), ['diseñador', 'disenador']) ? 'selected' : '' }}>Diseñador</option>
                        </select>
                    </div>

                    <div class="form-group" id="market-group" style="{{ in_array(old('role', $user->role), ['local', 'mercado']) ? 'display: block;' : 'display: none;' }} margin-bottom: 20px;">
                        <label style="margin-bottom: 10px;">Mercados asociados <span style="font-weight: normal; color: var(--text-muted);">(puedes seleccionar uno o varios)</span></label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px;">
                            @php
                                $allMarkets = [
                                    'PE' => ['name' => 'Perú', 'flag' => '🇵🇪'],
                                    'PRI' => ['name' => 'Puerto Rico', 'flag' => '🇵🇷'],
                                    'ARG' => ['name' => 'Argentina', 'flag' => '🇦🇷'],
                                    'MIA' => ['name' => 'Miami', 'flag' => '🇺🇸'],
                                    'MEX' => ['name' => 'México', 'flag' => '🇲🇽'],
                                    'CHL' => ['name' => 'Chile', 'flag' => '🇨🇱'],
                                    'COL' => ['name' => 'Colombia', 'flag' => '🇨🇴'],
                                    'ECU' => ['name' => 'Ecuador', 'flag' => '🇪🇨'],
                                ];
                                $userMarkets = (array) old('markets', $user->assigned_markets);
                            @endphp
                            @foreach($allMarkets as $code => $info)
                            <label style="display: flex; align-items: center; gap: 8px; background: var(--bg-secondary); border: 1px solid var(--border-input); padding: 10px 12px; border-radius: var(--radius-sm); cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-primary); margin: 0;">
                                <input type="checkbox" name="markets[]" value="{{ $code }}" {{ in_array($code, $userMarkets) ? 'checked' : '' }} style="width: auto; margin: 0;">
                                <span>{{ $info['flag'] }} {{ $code }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="status">Estado de la cuenta</label>
                        <select id="status" name="status" required>
                            <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ old('status', $user->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    @if($user->login_token)
                    <div class="form-group" style="margin-bottom: 24px; background: var(--bg-secondary); border: 1px dashed var(--border-input); border-radius: var(--radius-sm); padding: 16px;">
                        <label style="font-weight: 600; color: var(--wpp-navy); margin-bottom: 6px;">Enlace de Acceso Directo</label>
                        <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 10px 0;">Este enlace permite ingresar a la plataforma directamente sin solicitar magic link por correo:</p>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" readonly value="{{ route('login.token', $user->login_token) }}" style="font-family: monospace; font-size: 12px; background: white;" id="tokenUrlInput">
                            <button type="button" class="btn-submit" onclick="copyTokenLink()" style="width: auto; padding: 10px 16px; margin: 0; white-space: nowrap;" id="btnCopyToken">Copiar</button>
                        </div>
                    </div>
                    @endif

                    <button type="submit" class="btn-submit">Guardar cambios</button>
                </form>

                @if($user->login_token)
                <form action="{{ route('users.regenerate-token', $user) }}" method="POST" style="margin-top: 14px;" onsubmit="return confirm('¿Regenerar el token invalidará el enlace anterior. Continuar?');">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: var(--text-muted); text-decoration: none; font-size: 12px; cursor: pointer; padding: 0;">
                        ↻ Regenerar token de acceso directo
                    </button>
                </form>
                @endif
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
            if (role === 'local' || role === 'mercado') {
                marketGroup.style.display = 'block';
            } else {
                marketGroup.style.display = 'none';
            }
        }

        function copyTokenLink() {
            const input = document.getElementById('tokenUrlInput');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById('btnCopyToken');
                const orig = btn.innerHTML;
                btn.innerHTML = '✓ ¡Copiado!';
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            });
        }
    </script>
</body>
</html>
