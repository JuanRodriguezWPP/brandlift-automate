<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandliftQuestion extends Model
{
    protected $fillable = [
        'brandlift_study_id',
        'question_number',
        'question_text',
        'answers',
        'creative_html',
    ];

    protected $casts = [
        'answers' => 'array',
        'question_number' => 'integer',
    ];

    /**
     * Get the parent brandlift study.
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(BrandliftStudy::class, 'brandlift_study_id');
    }
}
