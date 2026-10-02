<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandliftEditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'brandlift_study_id',
        'user_id',
        'changes_made',
    ];

    protected $casts = [
        'changes_made' => 'array',
    ];

    public function study()
    {
        return $this->belongsTo(BrandliftStudy::class, 'brandlift_study_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
