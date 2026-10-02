<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Brandlift Automate</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #1e293b; margin: 0; padding: 30px; }
        .container { max-width: 620px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background-color: #000050; padding: 26px 32px; text-align: left; }
        .header h1 { color: #b0f467; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { color: #93dfe3; margin: 5px 0 0; font-size: 13px; }
        .content { padding: 32px; }
        .title { font-size: 20px; font-weight: 700; color: #000050; margin-bottom: 14px; }
        .meta-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13.5px; }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { font-weight: 600; color: #64748b; }
        .meta-value { font-weight: 600; color: #000050; }
        .btn-access { display: inline-block; background-color: #000050; color: #b0f467; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; text-align: center; margin: 15px 0; }
        .token-box { background-color: #f1f5f9; border-radius: 6px; padding: 12px 14px; font-family: monospace; font-size: 12px; color: #475569; word-break: break-all; margin-top: 15px; }
        .footer { background-color: #f8fafc; padding: 20px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>WPP MEDIA</h1>
            <p>Creative Services LATAM · Brandlift Automate</p>
        </div>
        <div class="content">
            <div class="title">¡Hola, {{ $user->name }}!</div>

            <p style="font-size: 14.5px; color: #334155; line-height: 1.6; margin-bottom: 20px;">
                Te damos la bienvenida a <strong>Brandlift Automate</strong>. Tu cuenta ha sido habilitada en la plataforma para gestionar y supervisar campañas de brandlift en la región.
            </p>

            <div class="meta-card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Correo registrado:</td>
                        <td style="padding: 6px 0; color: #000050; font-weight: 700; text-align: right;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Rol asignado:</td>
                        <td style="padding: 6px 0; color: #000050; font-weight: 700; text-align: right; text-transform: capitalize;">{{ $user->role === 'local' ? 'Mercado local' : $user->role }}</td>
                    </tr>
                    @php
                        $assignedMarkets = $user->assigned_markets;
                    @endphp
                    @if(!empty($assignedMarkets))
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Mercados autorizados:</td>
                        <td style="padding: 6px 0; color: #000050; font-weight: 700; text-align: right;">{{ implode(', ', $assignedMarkets) }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p style="font-size: 14px; color: #334155; margin-bottom: 15px;">
                Puedes ingresar directamente a la plataforma usando tu enlace de acceso seguro:
            </p>

            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ url('/login/token/' . $user->login_token) }}" class="btn-access">
                    Ingresar a Brandlift Automate
                </a>
            </div>

            <div class="token-box">
                <strong>Enlace directo alternativo:</strong><br>
                {{ url('/login/token/' . $user->login_token) }}
            </div>

            <p style="font-size: 12.5px; color: #64748b; margin-top: 25px; line-height: 1.5;">
                También puedes acceder en cualquier momento mediante enlace mágico solicitándolo con tu correo corporativo en la página de inicio de sesión.
            </p>
        </div>
        <div class="footer">
            Este correo es confidencial y para uso exclusivo de WPP Media Solutions y sus filiales.<br>
            Creative Services LATAM · © {{ date('Y') }} Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
