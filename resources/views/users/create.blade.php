<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario — Brandlift Automate</title>
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
        @include('partials.sidebar', ['active' => 'users'])

        <!-- Main Content -->
        <main class="main-content">
            @include('partials.top-header', ['title' => 'Crear usuario'])

            <div class="content-area">
        <div class="form-wrapper">
            <a href="{{ route('users.index') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver a usuarios
            </a>

            <div class="header">
                <h1>Crear nuevo usuario</h1>
            </div>

            <div class="page-card">
                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Juan Perez" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email">Correo electrónico (@wppmedia.com)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@wppmedia.com" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="role">Rol en la plataforma</label>
                        <select id="role" name="role" required onchange="toggleMarketSelect()">
                            <option value="">Selecciona un rol...</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador (acceso total)</option>
                            <option value="local" {{ old('role') === 'local' ? 'selected' : '' }}>Local (acceso por mercado/s asignados)</option>
                            <option value="diseñador" {{ old('role') === 'diseñador' ? 'selected' : '' }}>Diseñador</option>
                        </select>
                    </div>

                    <div class="form-group" id="market-group" style="{{ old('role') === 'local' ? 'display: block;' : 'display: none;' }} margin-bottom: 20px;">
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
                                $oldMarkets = (array) old('markets', []);
                            @endphp
                            @foreach($allMarkets as $code => $info)
                            <label style="display: flex; align-items: center; gap: 8px; background: var(--bg-secondary); border: 1px solid var(--border-input); padding: 10px 12px; border-radius: var(--radius-sm); cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-primary); margin: 0;">
                                <input type="checkbox" name="markets[]" value="{{ $code }}" {{ in_array($code, $oldMarkets) ? 'checked' : '' }} style="width: auto; margin: 0;">
                                <span>{{ $info['flag'] }} {{ $code }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="status">Estado de la cuenta</label>
                        <select id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Guardar usuario</button>
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
            if (role === 'local' || role === 'mercado') {
                marketGroup.style.display = 'block';
            } else {
                marketGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>
