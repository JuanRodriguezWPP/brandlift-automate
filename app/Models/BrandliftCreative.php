<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandliftCreative extends Model
{
    protected $fillable = [
        'brandlift_study_id',
        'question_number',
        'variant_key',
        'cm360_creative_id',
        'cm360_asset_id',
        'creative_html',
    ];

    /**
     * Get the parent brandlift study.
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(BrandliftStudy::class, 'brandlift_study_id');
    }
}
