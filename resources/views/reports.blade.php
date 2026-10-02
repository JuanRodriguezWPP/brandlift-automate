<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reportes & Analítica — WPP Media</title>
    <meta name="description" content="Reportes consolidados y analítica de estudios de Brandlift para WPP Media LATAM">
    <link rel="stylesheet" href="{{ asset('css/wpp-design-system.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <style>
        :root {
            --bg-card-border: var(--border-card);
            --accent-blue: var(--wpp-lime);
            --accent-blue-light: var(--wpp-cyan);
            --accent-cyan: var(--wpp-cyan);
            --accent-gradient: var(--gradient-lime-cyan);
            --success-green: var(--wpp-lime);
            --danger-red: var(--color-error);
            --warning-amber: #f59e0b;
        }

        body {
            background-color: var(--bg-secondary);
            font-family: 'WPP', sans-serif;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            padding: 18px 22px;
            box-shadow: var(--shadow-card);
            transition: all var(--transition-med);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .kpi-card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-3px);
        }

        .content-area {
            padding: 24px 40px 40px;
            width: 100%;
            box-sizing: border-box;
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .kpi-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.2px;
        }

        .kpi-icon {
            width: 34px; height: 34px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: var(--bg-secondary);
            color: var(--wpp-navy);
            flex-shrink: 0;
        }

        .kpi-icon svg {
            width: 18px;
            height: 18px;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
            color: var(--wpp-navy);
        }

        .kpi-subtext {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Filter Toolbar */
        .filter-bar {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;
        }

        .filter-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 180px;
        }

        .filter-input-group select,
        .filter-input-group input {
            width: 100%;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-input);
            background: var(--bg-input);
            font-family: 'WPP', sans-serif;
            font-size: 13.5px;
            color: var(--text-primary);
            outline: none;
            transition: border-color var(--transition-fast);
        }

        .filter-input-group select:focus,
        .filter-input-group input:focus {
            border-color: var(--wpp-navy);
        }

        .btn-filter {
            padding: 10px 20px;
            border-radius: var(--radius-md);
            background: var(--wpp-navy);
            color: #fff;
            border: none;
            font-family: 'WPP', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-filter:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-export-excel {
            padding: 10px 20px;
            border-radius: var(--radius-md);
            background: var(--wpp-lime);
            color: var(--wpp-navy);
            border: none;
            font-family: 'WPP', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            margin-left: auto;
        }

        .btn-export-excel:hover {
            box-shadow: 0 4px 12px rgba(176, 244, 103, 0.4);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="app-layout">
        @include('partials.sidebar', ['active' => 'reports'])

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            @include('partials.top-header', ['title' => 'Reportes & Métricas'])

            <div class="content-area">
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <span class="kpi-label">Estudios Creados</span>
                            <div class="kpi-icon" style="background: rgba(0,0,80,0.06); color: var(--wpp-navy);"><span class="material-symbols-outlined" style="font-size: 20px;">bar_chart</span></div>
                        </div>
                        <div>
                            <div class="kpi-value">{{ $totalStudies }}</div>
                            <p class="kpi-subtext">Total histórico registrado</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-header">
                            <span class="kpi-label">Desplegados en CM360</span>
                            <div class="kpi-icon" style="background: rgba(176,244,103,0.2); color: var(--wpp-navy);"><span class="material-symbols-outlined" style="font-size: 20px;">rocket_launch</span></div>
                        </div>
                        <div>
                            <div class="kpi-value" style="color: #10b981;">{{ $totalPushed }}</div>
                            <p class="kpi-subtext">{{ $totalStudies > 0 ? round(($totalPushed / $totalStudies) * 100) : 0 }}% tasa de éxito</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-header">
                            <span class="kpi-label">Preguntas Registradas</span>
                            <div class="kpi-icon" style="background: rgba(147,223,227,0.2); color: var(--wpp-navy);"><span class="material-symbols-outlined" style="font-size: 20px;">quiz</span></div>
                        </div>
                        <div>
                            <div class="kpi-value">{{ $totalQuestions }}</div>
                            <p class="kpi-subtext">Encuestas estructuradas</p>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-header">
                            <span class="kpi-label">Creativos & Variantes</span>
                            <div class="kpi-icon" style="background: rgba(0,0,80,0.06); color: var(--wpp-navy);"><span class="material-symbols-outlined" style="font-size: 20px;">palette</span></div>
                        </div>
                        <div>
                            <div class="kpi-value">{{ $totalCreatives }}</div>
                            <p class="kpi-subtext">HTML5 interactivos generados</p>
                        </div>
                    </div>
                </div>

                <!-- Filters Bar -->
                <form method="GET" action="{{ route('reports') }}" class="filter-bar">
                    <div class="filter-input-group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cliente o campaña...">
                    </div>

                    @if(count($availableMarkets) > 1)
                        <div class="filter-input-group" style="max-width: 200px;">
                            <select name="market">
                                <option value="">Todos los mercados</option>
                                @foreach($availableMarkets as $code => $name)
                                    <option value="{{ $code }}" {{ request('market') === $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="filter-input-group" style="max-width: 170px;">
                        <select name="status">
                            <option value="">Todos los estados</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activa</option>
                            <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Finalizada</option>
                            <option value="created" {{ request('status') === 'created' ? 'selected' : '' }}>Pendiente</option>
                            <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Con Error</option>
                        </select>
                    </div>

                    <div class="filter-input-group" style="max-width: 170px; position: relative;">
                        <input type="text" id="report-date-visual" value="{{ !empty(request('date_to')) ? date('d/m/Y', strtotime(request('date_to'))) : '' }}" placeholder="Hasta (dd/mm/aaaa)" readonly style="cursor: pointer; padding-right: 36px;">
                        <input type="date" id="report-date-to" name="date_to" value="{{ request('date_to') }}" style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="
                            const visual = document.getElementById('report-date-visual');
                            if(this.value) {
                                const [y, m, d] = this.value.split('-');
                                visual.value = `${d}/${m}/${y}`;
                            } else {
                                visual.value = '';
                            }
                        ">
                        <span class="material-symbols-outlined" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted); pointer-events: none;">calendar_today</span>
                    </div>

                    <button type="submit" class="btn-filter">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filtrar
                    </button>

                    @if(request()->hasAny(['search', 'market', 'status', 'date_to']))
                        <a href="{{ route('reports') }}" style="font-size: 13px; color: var(--text-muted); text-decoration: none;">Limpiar</a>
                    @endif

                <button type="button" onclick="exportReportsTableToExcel()" class="btn-export-excel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Exportar Excel
                </button>
            </form>

            <!-- Detailed Table Card -->
            <div class="card" style="padding: 24px;">
                <div class="card-header" style="margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(0,0,80,0.06); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 style="font-size: 17px; font-weight: 700; color: var(--wpp-navy); margin: 0;">Campañas</h2>
                        <span style="font-size: 13px; color: var(--text-muted);">{{ $allStudies->count() }} registros filtrados</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="reports-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(0,0,80,0.08); text-align: left;">
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Mercado</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Cliente</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Campaña</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Preguntas</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">DSPs</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Finaliza</th>
                                <th style="padding: 12px 14px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allStudies as $study)
                                <tr style="border-bottom: 1px solid rgba(0,0,80,0.04);">
                                    <td style="padding: 14px; font-size: 13px; font-weight: 700; color: var(--wpp-navy);">
                                        {{ $study->market_name }} ({{ $study->market }})
                                    </td>
                                    <td style="padding: 14px; font-size: 13.5px; color: var(--text-primary);">
                                        {{ $study->client_name ?: '—' }}
                                    </td>
                                    <td style="padding: 14px; font-size: 13.5px; font-weight: 600; color: var(--wpp-navy);">
                                        {{ $study->campaign_name }}
                                    </td>
                                    <td style="padding: 14px; font-size: 13px; color: var(--text-secondary);">
                                        {{ $study->question_count }} preguntas
                                    </td>
                                    <td style="padding: 14px; font-size: 12px;">
                                        @php
                                            $dps = (array) ($study->dps_tags ?? []);
                                        @endphp
                                        @foreach($dps as $tag)
                                            <span style="display:inline-block; padding: 2px 6px; border-radius: 6px; background: rgba(0,0,80,0.05); font-weight: 600; color: var(--wpp-navy); margin-right: 4px;">{{ $tag }}</span>
                                        @endforeach
                                    </td>
                                    <td style="padding: 14px; font-size: 13px; font-weight: 500; color: var(--text-secondary);">
                                        {{ $study->end_date ? \Carbon\Carbon::parse($study->end_date)->locale('es')->translatedFormat('d M, Y') : '—' }}
                                    </td>
                                    <td style="padding: 14px;">
                                        @php
                                            $isPushed = $study->cm360_pushed || in_array($study->status, ['pushed', 'cm360_pushed']);
                                            $isFinished = $study->end_date && \Carbon\Carbon::parse($study->end_date)->startOfDay()->lt(now()->startOfDay());
                                        @endphp

                                        @if($study->status === 'inactive')
                                            <span style="display:inline-flex; align-items:center; gap:6px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; background: rgba(100, 116, 139, 0.12); color: #64748b;">
                                                <span style="width:6px; height:6px; border-radius:50%; background:#64748b;"></span>
                                                Desactivada
                                            </span>
                                        @elseif($study->status === 'error')
                                            <span style="display:inline-flex; align-items:center; gap:6px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; background: rgba(239, 68, 68, 0.12); color: #dc2626;">
                                                <span style="width:6px; height:6px; border-radius:50%; background:#dc2626;"></span>
                                                Error
                                            </span>
                                        @elseif($isPushed)
                                            @if($isFinished)
                                                <span style="display:inline-flex; align-items:center; gap:6px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; background: rgba(100, 116, 139, 0.12); color: #64748b;">
                                                    <span style="width:6px; height:6px; border-radius:50%; background:#64748b;"></span>
                                                    Finalizada
                                                </span>
                                            @else
                                                <span style="display:inline-flex; align-items:center; gap:6px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; background: rgba(16, 185, 129, 0.12); color: #059669;">
                                                    <span style="width:6px; height:6px; border-radius:50%; background:#10b981;"></span>
                                                    Activa
                                                </span>
                                            @endif
                                        @else
                                            <span style="display:inline-flex; align-items:center; gap:6px; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; background: rgba(245, 158, 11, 0.12); color: #d97706;">
                                                <span style="width:6px; height:6px; border-radius:50%; background:#d97706;"></span>
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 32px; text-align: center; color: var(--text-muted); font-size: 14px;">
                                        No se encontraron campañas registradas con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div><!-- .content-area -->
        </main>
    </div>

    <!-- Excel Export Script -->
    <script>
        function exportReportsTableToExcel() {
            const table = document.getElementById('reports-table');
            if (!table) return;

            const rows = [];
            const headers = ['Mercado', 'Cliente', 'Campaña', 'Preguntas', 'DSPs', 'Fecha Fin', 'Estado'];
            rows.push(headers);

            const trs = table.querySelectorAll('tbody tr');
            trs.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 7) {
                    const rowData = [
                        tds[0].innerText.trim(),
                        tds[1].innerText.trim(),
                        tds[2].innerText.trim(),
                        tds[3].innerText.trim(),
                        tds[4].innerText.trim(),
                        tds[5].innerText.trim(),
                        tds[6].innerText.trim()
                    ];
                    rows.push(rowData);
                }
            });

            if (rows.length <= 1) {
                alert('No hay datos disponibles para exportar.');
                return;
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);

            // Styling header row
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cellRef = XLSX.utils.encode_cell({ r: 0, c: C });
                if (!ws[cellRef]) continue;
                ws[cellRef].s = {
                    fill: { fgColor: { rgb: "000050" } },
                    font: { color: { rgb: "FFFFFF" }, bold: true, name: "Calibri" },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            }

            ws['!cols'] = [
                { wch: 18 },
                { wch: 22 },
                { wch: 35 },
                { wch: 14 },
                { wch: 22 },
                { wch: 18 },
                { wch: 16 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, "Reporte_BrandLift");
            const filename = `Reporte_BrandLift_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
        }
    </script>
</body>
</html>
