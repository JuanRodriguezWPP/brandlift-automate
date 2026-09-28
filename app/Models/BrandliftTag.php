<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandliftTag extends Model
{
    protected $fillable = [
        'brandlift_study_id',
        'status',
        'question_number',
        'creative_name',
        'tag_type',
        'placement_id',
        'tag_script',
        'error',
    ];

    public function study()
    {
        return $this->belongsTo(BrandliftStudy::class, 'brandlift_study_id');
    }
}
