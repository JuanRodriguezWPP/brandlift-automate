<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tags BrandLift — {{ $study->campaign_name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #1e293b; margin: 0; padding: 30px; }
        .container { max-width: 650px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background-color: #000050; padding: 24px 30px; text-align: left; }
        .header h1 { color: #b0f467; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { color: #93dfe3; margin: 5px 0 0; font-size: 13px; }
        .content { padding: 30px; }
        .message-box { background-color: #f8fafc; border-left: 4px solid #000050; padding: 16px 20px; border-radius: 4px; font-size: 14px; line-height: 1.6; color: #334155; margin-bottom: 24px; white-space: pre-wrap; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .meta-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .meta-table td.label { font-weight: 600; color: #64748b; width: 35%; }
        .meta-table td.val { color: #0f172a; font-weight: 500; }
        .badge { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .btn-preview { display: inline-block; background-color: #000050; color: #b0f467; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; text-align: center; margin-top: 10px; }
        .footer { background-color: #f8fafc; padding: 20px 30px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BrandLift Builder</h1>
            <p>(WMS Creative Services LATAM)</p>
        </div>
        <div class="content">
            <p style="font-size: 14px; color: #475569; margin-bottom: 20px; line-height: 1.5;">
                Se han generado los tags para el estudio de <strong>{{ $study->client_name ?: 'el anunciante' }}</strong> para la campaña <strong>{{ $study->campaign_name }}</strong>. En el archivo Excel adjunto encontrarás los scripts de tráfico completos y las especificaciones técnicas.
            </p>

            @if(!empty($customMessage))
            <div class="message-box">
<strong>Indicaciones y recomendaciones:</strong>
{{ $customMessage }}
            </div>
            @endif

            @if($study->sheet_id)
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px;">
                <strong style="color: #166534; font-size: 14px; display: block; margin-bottom: 4px;">
                    📊 Hoja de Respuestas en Google Sheets
                </strong>
                <p style="color: #15803d; font-size: 13px; line-height: 1.4; margin: 0 0 10px 0;">
                    Las respuestas y métricas recopiladas por este BrandLift se registran automáticamente en la siguiente hoja de cálculo en vivo:
                </p>
                <a href="https://docs.google.com/spreadsheets/d/{{ $study->sheet_id }}/edit" target="_blank" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 700;">
                    Abrir Hoja de Google Sheets &rarr;
                </a>
            </div>
            @endif

            <table class="meta-table">
                <tr>
                    <td class="label">Campaña</td>
                    <td class="val"><strong>{{ $study->campaign_name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Cliente / Anunciante</td>
                    <td class="val">{{ $study->client_name ?: 'No especificado' }}</td>
                </tr>
                <tr>
                    <td class="label">Mercado</td>
                    <td class="val"><span class="badge">{{ $study->market_name }} ({{ $study->market }})</span></td>
                </tr>
                <tr>
                    <td class="label">DSPs</td>
                    <td class="val">
                        @php
                            $dsps = $study->dps_tags ?? [];
                            if (is_string($dsps)) {
                                $dsps = json_decode($dsps, true) ?? [];
                            }
                        @endphp
                        @if(!empty($dsps) && count($dsps) > 0)
                            @foreach($dsps as $dsp)
                                <span class="badge" style="margin-right: 4px; margin-bottom: 2px;">{{ strtoupper($dsp) }}</span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8;">No especificado</span>
                        @endif
                    </td>
                </tr>
                @if($study->end_date)
                <tr>
                    <td class="label">Finaliza</td>
                    <td class="val">{{ \Carbon\Carbon::parse($study->end_date)->format('d M Y') }}</td>
                </tr>
                @endif
                @if($study->sheet_id)
                <tr>
                    <td class="label">Hoja de Respuestas</td>
                    <td class="val">
                        <a href="https://docs.google.com/spreadsheets/d/{{ $study->sheet_id }}/edit" target="_blank" style="color: #16a34a; font-weight: 600; text-decoration: underline;">Ver en Google Sheets</a>
                    </td>
                </tr>
                @endif
            </table>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('brandlift.public-preview', $study->liquid_id) }}" class="btn-preview" target="_blank">
                    Ver creativos en vivo
                </a>
            </div>
        </div>
        <div class="footer" style="line-height: 1.6;">
            <div style="font-weight: 700; color: #475569; font-size: 13px;">WPP Media Solutions</div>
            <div style="color: #64748b; font-size: 12px;">Creative Services LATAM</div>
            <div style="color: #94a3b8; font-size: 11.5px; margin-top: 4px;">{{ date('Y') }}</div>
        </div>
    </div>
</body>
</html>
