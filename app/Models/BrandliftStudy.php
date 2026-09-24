<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandliftStudy extends Model
{
    protected $fillable = [
        'market',
        'campaign_name',
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
        'created_by',
        'cm360_tags',
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
    ];

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
}
