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
        'ai_usage_frequency',
        'ai_answer_trust',
        'ai_explanation_preference',
    ];

    protected function casts(): array
    {
        return [
            'trust_score' => 'float',
            'transparency_score' => 'float',
            'ui_satisfaction' => 'float',
            'recommendation_quality' => 'float',
            'additional_data' => 'array',
            'ai_answer_trust' => 'float',
            'ai_explanation_preference' => 'float',
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
