<!-- Top Header -->
<header class="top-header">
    <h1 class="top-header-title">{{ $title ?? 'BrandLift Automate' }}</h1>
    <div class="top-header-profile">
        <div class="top-header-user">
            <div class="top-header-user-info" title="{{ Auth::user()->email ?? '' }}">
                <div class="top-header-user-name">{{ Auth::user()->name ?? 'Usuario' }}</div>
                
                @php
                    $assignedMarkets = Auth::user()?->assigned_markets ?? [];
                    $marketCount = count($assignedMarkets);
                    $activeMarket = session('active_market');
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
                @endphp

                @if(Auth::user()?->isAdmin())
                    <div class="top-header-market-switcher" style="margin-top: 3px;">
                        <select onchange="switchUserMarket(this.value)" style="font-size: 11.5px; padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-input); background: var(--bg-secondary); color: var(--text-primary); cursor: pointer; font-weight: 500; outline: none;">
                            <option value="" {{ empty($activeMarket) ? 'selected' : '' }}>🌐 Todos los mercados</option>
                            @foreach($allMarkets as $code => $info)
                                <option value="{{ $code }}" {{ $activeMarket === $code ? 'selected' : '' }}>{{ $info['flag'] }} {{ $info['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($marketCount > 1)
                    <div class="top-header-market-switcher" style="margin-top: 3px;">
                        <select onchange="switchUserMarket(this.value)" style="font-size: 11.5px; padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-input); background: var(--bg-secondary); color: var(--text-primary); cursor: pointer; font-weight: 500; outline: none;">
                            <option value="" {{ empty($activeMarket) ? 'selected' : '' }}>🌐 Mis mercados ({{ $marketCount }})</option>
                            @foreach($assignedMarkets as $code)
                                @if(isset($allMarkets[$code]))
                                    <option value="{{ $code }}" {{ $activeMarket === $code ? 'selected' : '' }}>{{ $allMarkets[$code]['flag'] }} {{ $allMarkets[$code]['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @elseif($marketCount === 1)
                    <div class="top-header-user-country">
                        <span class="top-header-user-flag">{!! Auth::user()->country_flag_svg !!}</span>
                        <span>{{ Auth::user()->country_name }}</span>
                    </div>
                @else
                    <div class="top-header-user-email">{{ Auth::user()->email ?? '' }}</div>
                @endif
            </div>
            <div class="top-header-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="top-header-logout-form">
            @csrf
            <button type="submit" class="top-header-logout-btn" title="Cerrar sesión">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</header>
<script>
    if (typeof switchUserMarket === 'undefined') {
        function switchUserMarket(market) {
            fetch('{{ route('user.switch-market') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ market: market })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    }
</script>
