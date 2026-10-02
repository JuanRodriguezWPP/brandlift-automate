<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'market',
        'markets',
        'status',
        'login_token',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'markets' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine if the user is local.
     */
    public function isLocal(): bool
    {
        return in_array($this->role, ['local', 'mercado']);
    }

    /**
     * Get the list of assigned market codes.
     *
     * @return array<string>
     */
    public function getAssignedMarketsAttribute(): array
    {
        if (is_array($this->markets) && ! empty($this->markets)) {
            return array_values(array_filter($this->markets));
        }

        if (! empty($this->market)) {
            return [$this->market];
        }

        return [];
    }

    /**
     * Check if user has access to a specific market.
     */
    public function hasMarket(?string $market): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (empty($market)) {
            return false;
        }

        return in_array(strtoupper($market), array_map('strtoupper', $this->assigned_markets));
    }

    /**
     * Get the assigned country name for the user.
     */
    public function getCountryNameAttribute(): ?string
    {
        $assigned = $this->assigned_markets;

        if (empty($assigned)) {
            return $this->isAdmin() ? 'Global' : null;
        }

        $countries = [
            'PE' => 'Perú',
            'PER' => 'Perú',
            'PRI' => 'Puerto Rico',
            'PR' => 'Puerto Rico',
            'ARG' => 'Argentina',
            'AR' => 'Argentina',
            'MIA' => 'Miami',
            'USA' => 'Estados Unidos',
            'US' => 'Estados Unidos',
            'MEX' => 'México',
            'MX' => 'México',
            'CHL' => 'Chile',
            'CL' => 'Chile',
            'COL' => 'Colombia',
            'CO' => 'Colombia',
            'ECU' => 'Ecuador',
            'EC' => 'Ecuador',
        ];

        if (count($assigned) === 1) {
            $code = strtoupper($assigned[0]);

            return $countries[$code] ?? $code;
        }

        $names = array_map(fn ($m) => $countries[strtoupper($m)] ?? $m, $assigned);

        return implode(', ', $names);
    }

    /**
     * Get the SVG flag icon markup for the user's assigned country/market.
     */
    public function getCountryFlagSvgAttribute(): string
    {
        $assigned = $this->assigned_markets;

        if (count($assigned) !== 1) {
            return '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="6" r="4.5" stroke="#475569" stroke-width="1"/><line x1="3.5" y1="6" x2="12.5" y2="6" stroke="#475569" stroke-width="0.8"/><ellipse cx="8" cy="6" rx="2.5" ry="4.5" stroke="#475569" stroke-width="0.8"/></svg>';
        }

        $market = strtoupper($assigned[0]);

        return match ($market) {
            'PE', 'PER' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#D91023"/><rect x="5.33" width="5.34" height="12" fill="#FFFFFF"/></svg>',
            'PRI', 'PR' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#ED0000"/><rect y="2.4" width="16" height="2.4" fill="#FFFFFF"/><rect y="7.2" width="16" height="2.4" fill="#FFFFFF"/><polygon points="0,0 7,6 0,12" fill="#0038A8"/><polygon points="2.5,4.3 2.9,5.4 4.1,5.5 3.1,6.3 3.5,7.4 2.5,6.7 1.5,7.4 1.9,6.3 0.9,5.5 2.1,5.4" fill="#FFFFFF"/></svg>',
            'ARG', 'AR' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#74ACDF"/><rect y="4" width="16" height="4" fill="#FFFFFF"/><circle cx="8" cy="6" r="1.2" fill="#F6B40E" stroke="#85340A" stroke-width="0.25"/><path d="M8 4.3v0.5M8 7.2v0.5M6.3 6h0.5M9.2 6h0.5M6.8 4.8l0.4 0.4M8.8 6.8l0.4 0.4M6.8 7.2l0.4-0.4M8.8 5.2l0.4-0.4" stroke="#F6B40E" stroke-width="0.35" stroke-linecap="round"/></svg>',
            'COL', 'CO' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="6" fill="#FCD116"/><rect y="6" width="16" height="3" fill="#003893"/><rect y="9" width="16" height="3" fill="#CE1126"/></svg>',
            'CHL', 'CL' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#FFFFFF"/><rect y="6" width="16" height="6" fill="#D52B1E"/><rect width="6" height="6" fill="#0039A6"/><polygon points="3,1.6 3.4,2.5 4.4,2.6 3.5,3.2 3.9,4.2 3,3.6 2.1,4.2 2.5,3.2 1.6,2.6 2.6,2.5" fill="#FFFFFF"/></svg>',
            'ECU', 'EC' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="6" fill="#FFDD00"/><rect y="6" width="16" height="3" fill="#034EA2"/><rect y="9" width="16" height="3" fill="#ED1C24"/><ellipse cx="8" cy="6" rx="1.5" ry="1.7" fill="#034EA2" stroke="#85340A" stroke-width="0.25"/><ellipse cx="8" cy="6" rx="1.1" ry="1.3" fill="#FFDD00"/><circle cx="8" cy="5.4" r="0.6" fill="#FFFFFF"/></svg>',
            'MEX', 'MX' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#FFFFFF"/><rect width="5.33" height="12" fill="#006847"/><rect x="10.67" width="5.33" height="12" fill="#CE1126"/><circle cx="8" cy="6" r="1.2" fill="#8B5A2B"/><path d="M7.4 6.8c.4.3.8.3 1.2 0" stroke="#006847" stroke-width="0.3" fill="none"/></svg>',
            'MIA', 'USA', 'US' => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="12" fill="#B22234"/><rect y="0.92" width="16" height="0.92" fill="#FFFFFF"/><rect y="2.77" width="16" height="0.92" fill="#FFFFFF"/><rect y="4.62" width="16" height="0.92" fill="#FFFFFF"/><rect y="6.46" width="16" height="0.92" fill="#FFFFFF"/><rect y="8.31" width="16" height="0.92" fill="#FFFFFF"/><rect y="10.15" width="16" height="0.92" fill="#FFFFFF"/><rect width="6.5" height="6.46" fill="#3C3B6E"/><circle cx="1.6" cy="1.6" r="0.45" fill="#FFFFFF"/><circle cx="3.25" cy="1.6" r="0.45" fill="#FFFFFF"/><circle cx="4.9" cy="1.6" r="0.45" fill="#FFFFFF"/><circle cx="2.4" cy="3.2" r="0.45" fill="#FFFFFF"/><circle cx="4.1" cy="3.2" r="0.45" fill="#FFFFFF"/><circle cx="1.6" cy="4.8" r="0.45" fill="#FFFFFF"/><circle cx="3.25" cy="4.8" r="0.45" fill="#FFFFFF"/><circle cx="4.9" cy="4.8" r="0.45" fill="#FFFFFF"/></svg>',
            default => '<svg viewBox="0 0 16 12" width="16" height="12" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="6" r="4.5" stroke="#475569" stroke-width="1"/><line x1="3.5" y1="6" x2="12.5" y2="6" stroke="#475569" stroke-width="0.8"/><ellipse cx="8" cy="6" rx="2.5" ry="4.5" stroke="#475569" stroke-width="0.8"/></svg>',
        };
    }

    /**
     * Get the flag emoji for the user's assigned country/market.
     */
    public function getCountryFlagEmojiAttribute(): string
    {
        $assigned = $this->assigned_markets;
        if (count($assigned) !== 1) {
            return '🌐';
        }

        $market = strtoupper($assigned[0]);

        return match ($market) {
            'PE', 'PER' => '🇵🇪',
            'PRI', 'PR' => '🇵🇷',
            'ARG', 'AR' => '🇦🇷',
            'COL', 'CO' => '🇨🇴',
            'CHL', 'CL' => '🇨🇱',
            'ECU', 'EC' => '🇪🇨',
            'MEX', 'MX' => '🇲🇽',
            'MIA', 'USA', 'US' => '🇺🇸',
            default => '🌐',
        };
    }
}
