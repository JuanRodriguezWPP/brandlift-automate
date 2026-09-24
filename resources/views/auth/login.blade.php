<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Brandlift Automate</title>
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <style>
        /* ===== LOGIN SPLIT LAYOUT ===== */
        body {
            background-color: var(--bg-primary);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .split-layout {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* --- Left Side: Hero --- */
        .login-hero {
            flex: 1;
            background-color: var(--wpp-navy);
            /* Subtle geometric overlay using radial gradients */
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(255,255,255,0.03) 0%, transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(84, 101, 255, 0.1) 0%, transparent 50%);
            color: var(--wpp-white);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
        }

        /* Add some sleek curved lines like the screenshot */
        .login-hero::before, .login-hero::after {
            content: '';
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-hero::before {
            width: 800px;
            height: 800px;
            top: -200px;
            left: -300px;
        }

        .login-hero::after {
            width: 1000px;
            height: 1000px;
            top: -100px;
            left: -200px;
        }

        .hero-content {
            max-width: 480px;
            margin-top: 40px;
            z-index: 1;
        }

        .hero-icon {
            margin-bottom: 40px;
            color: var(--wpp-white);
        }

        .hero-title {
            font-size: 48px;
            font-weight: 500;
            line-height: 1.1;
            margin-bottom: 40px;
            color: var(--wpp-white);
        }

        .hero-subtitle {
            font-size: 14px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
            margin-top: 40px;
        }

        .hero-footer {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            z-index: 1;
            position: absolute;
            bottom: 60px;
            left: 60px;
        }

        /* --- Right Side: Form --- */
        .login-side {
            flex: 1;
            background: var(--bg-card);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            max-width: 600px;
        }

        .brand-logo {
            font-weight: 500;
            font-size: 20px;
            color: var(--text-primary);
            letter-spacing: 0.5px;
            margin-bottom: auto;
        }

        .form-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 12px;
            text-align: center;
        }

        .form-header p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin: 0;
            text-align: center;
        }

        .login-side label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .login-side input {
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border-input);
            border-bottom: 2px solid var(--border-input); /* Slight accent on bottom like standard inputs */
            color: var(--text-primary);
            padding: 14px 16px;
            border-radius: var(--radius-sm);
            font-family: 'WPP', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all var(--transition-fast);
            margin-bottom: 24px;
        }

        .login-side input:focus {
            border-color: var(--wpp-navy);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.05);
        }

        .login-side input::placeholder {
            color: var(--text-muted);
        }

        .btn-login {
            width: 100%;
            background: var(--text-primary); /* Dark navy/black feel like the screenshot */
            color: var(--wpp-white);
            border: none;
            padding: 14px;
            border-radius: var(--radius-sm);
            font-family: 'WPP', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            box-shadow: 0 4px 12px rgba(0, 0, 80, 0.15);
            margin-bottom: 16px;
        }

        .btn-login:hover {
            background: #000030;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-hero {
                display: none;
            }
            .login-side {
                max-width: 100%;
                align-items: center;
                padding: 40px 20px;
            }
            .brand-logo {
                width: 100%;
                max-width: 380px;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="split-layout">
        
        <!-- Left Hero Section -->
        <div class="login-hero">
            <div class="hero-content">
                <h1 class="hero-title" style="text-transform: uppercase;">
                    WPP MEDIA SOLUTIONS <br>
                    <span style="color: var(--wpp-lime); font-size: 0.6em; display: inline-block; margin-top: 8px;">| Creative Services LATAM</span>
                </h1>
                <p class="hero-subtitle">
                    Crea fácilmente tus creativos Brandlift y obtén al instante los tags listos para tus DSPs. Optimiza tu flujo de trabajo y ahorra horas de comunicación.
                </p>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="login-side">
            <div class="form-container">
                <div class="form-header">
                    <h2>¡Bienvenido!</h2>
                    <p>Ingresa tu correo corporativo</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success" style="margin-bottom: 24px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error" style="margin-bottom: 24px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="usuario@wppmedia.com" required autofocus autocomplete="email">
                    </div>
                    
                    <button type="submit" class="btn-login">Ingresar ahora</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
