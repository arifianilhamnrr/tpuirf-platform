<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrustEvaluation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'experiment_id',
        'trust_score',
        'transparency_score',
        'ui_satisfaction',
        'recommendation_quality',
        'feedback',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'trust_score' => 'float',
            'transparency_score' => 'float',
            'ui_satisfaction' => 'float',
            'recommendation_quality' => 'float',
            'additional_data' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }
}
