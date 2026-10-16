<?php

namespace App\Models;

use App\Services\LiquidId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandliftStudy extends Model
{
    protected $fillable = [
        'market',
        'campaign_name',
        'campaign_objective',
        'question_count',
        'creative_width',
        'creative_height',
        'sheet_id',
        'cm360_profile_id',
        'cm360_advertiser_id',
        'client_name',
        'audiences',
        'dps_tags',
        'cm360_campaign_id',
        'cm360_pushed',
        'cm360_pushed_at',
        'status',
        'error_message',
        'created_by',
        'assigned_users',
        'cm360_tags',
        'end_date',
        'investment',
        'cm360_site_id',
        'theme_colors',
    ];

    protected $casts = [
        'cm360_pushed' => 'boolean',
        'cm360_pushed_at' => 'datetime',
        'question_count' => 'integer',
        'creative_width' => 'integer',
        'creative_height' => 'integer',
        'audiences' => 'array',
        'dps_tags' => 'array',
        'cm360_tags' => 'array',
        'theme_colors' => 'array',
        'assigned_users' => 'array',
    ];

    protected $appends = [
        'liquid_id',
        'cm360_account_id',
        'cm360_url',
        'google_sheet_url',
        'creator_name',
        'assigned_users_info',
    ];

    /**
     * Get the obfuscated Liquid ID for URL sharing/editing.
     */
    public function getLiquidIdAttribute(): string
    {
        return LiquidId::encode($this->id);
    }

    /**
     * Get the full direct URL to the Google Sheet.
     */
    public function getGoogleSheetUrlAttribute(): ?string
    {
        if (! $this->sheet_id) {
            return null;
        }

        return "https://docs.google.com/spreadsheets/d/{$this->sheet_id}/edit";
    }

    /**
     * Find a study by its Liquid ID or numeric ID.
     */
    public static function findByLiquidId(string|int $liquidId, array $with = []): ?self
    {
        $id = is_numeric($liquidId) ? (int) $liquidId : LiquidId::decode((string) $liquidId);
        if (! $id) {
            return null;
        }

        $query = static::query();
        if (! empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find a study by its Liquid ID or throw 404.
     */
    public static function findOrFailByLiquidId(string|int $liquidId, array $with = []): self
    {
        $id = is_numeric($liquidId) ? (int) $liquidId : LiquidId::decode((string) $liquidId);
        if (! $id) {
            abort(404, 'Brandlift no encontrado.');
        }

        $query = static::query();
        if (! empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Get the CM360 account ID.
     */
    public function getCm360AccountIdAttribute(): string
    {
        return $this->attributes['cm360_account_id'] ?? '732535';
    }

    /**
     * Get the full direct URL to the campaign in Campaign Manager 360.
     */
    public function getCm360UrlAttribute(): ?string
    {
        if (! $this->cm360_campaign_id) {
            return null;
        }

        $accountId = $this->cm360_account_id;

        return "https://campaignmanager.google.com/trafficking/#/accounts/{$accountId}/campaigns/{$this->cm360_campaign_id}/explorer?statuses=0;2";
    }

    /**
     * Get the questions for this brandlift study.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(BrandliftQuestion::class)->orderBy('question_number');
    }

    /**
     * Get the creatives for this brandlift study.
     */
    public function creatives(): HasMany
    {
        return $this->hasMany(BrandliftCreative::class);
    }

    /**
     * Get the CM360 tags generated for this brandlift study.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(BrandliftTag::class);
    }

    /**
     * Scope: filter by market
     */
    public function scopeMarket($query, string $market)
    {
        return $query->where('market', $market);
    }

    /**
     * Scope: filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get a human-readable market name.
     */
    public function getMarketNameAttribute(): string
    {
        $markets = [
            'PE' => 'Perú',
            'PRI' => 'Puerto Rico',
            'ARG' => 'Argentina',
            'MIA' => 'Miami',
            'MEX' => 'México',
            'CHL' => 'Chile',
            'COL' => 'Colombia',
            'ECU' => 'Ecuador',
        ];

        return $markets[$this->market] ?? $this->market;
    }

    public function editLogs()
    {
        return $this->hasMany(BrandliftEditLog::class, 'brandlift_study_id')->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatorNameAttribute(): ?string
    {
        if (! $this->created_by) {
            return null;
        }

        if ($this->relationLoaded('creator')) {
            return $this->getRelation('creator')?->name;
        }

        return User::where('id', $this->created_by)->value('name');
    }

    /**
     * Return basic info (id, name, email, markets) of users assigned in copy (OOO / follow-up).
     */
    public function getAssignedUsersInfoAttribute(): array
    {
        $ids = $this->assigned_users;
        if (empty($ids) || ! is_array($ids)) {
            return [];
        }

        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($cleanIds)) {
            return [];
        }

        try {
            return User::whereIn('id', $cleanIds)
                ->get(['id', 'name', 'email', 'role', 'market', 'markets'])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'markets' => $u->assigned_markets,
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Check whether a given user can access/manage this Brandlift study.
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if (! $user) {
            return true;
        }

        if ($user->hasGlobalAccess()) {
            return true;
        }

        if (! $user->hasMarket($this->market)) {
            return false;
        }

        $userId = (int) $user->id;
        $assignedIds = array_map('intval', is_array($this->assigned_users) ? $this->assigned_users : []);

        if (in_array($userId, $assignedIds, true)) {
            return true;
        }

        if (is_null($this->created_by) || (int) $this->created_by === $userId) {
            return true;
        }

        return false;
    }

    /**
     * Scope studies visible to the given user on the dashboard.
     */
    public function scopeVisibleTo($query, ?User $user, ?string $activeMarket = null)
    {
        if (! $user) {
            return $query;
        }

        if ($user->hasGlobalAccess()) {
            if (! empty($activeMarket)) {
                $query->where('market', $activeMarket);
            }

            return $query;
        }

        $assignedMarkets = $user->assigned_markets;
        if (! empty($assignedMarkets)) {
            $query->whereIn('market', $assignedMarkets);
        }

        $userId = (int) $user->id;
        $query->where(function ($q) use ($userId) {
            $q->whereNull('created_by')
                ->orWhere('created_by', $userId)
                ->orWhereJsonContains('assigned_users', $userId)
                ->orWhereJsonContains('assigned_users', (string) $userId);
        });

        return $query;
    }
}

