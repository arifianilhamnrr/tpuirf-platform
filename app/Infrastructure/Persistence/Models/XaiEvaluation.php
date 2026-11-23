<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class XaiEvaluation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id', 'experiment_id', 'recommendation_id',
        'perceived_transparency', 'system_trust', 'decision_confidence',
        'overall_satisfaction', 'explanation_understandability',
        'causal_attribution_coherence', 'perceived_scrutability',
        'cognitive_load_appropriateness', 'instrument_version', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'perceived_transparency' => 'integer',
            'system_trust' => 'integer',
            'decision_confidence' => 'integer',
            'overall_satisfaction' => 'integer',
            'explanation_understandability' => 'integer',
            'causal_attribution_coherence' => 'integer',
            'perceived_scrutability' => 'integer',
            'cognitive_load_appropriateness' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function experiment() { return $this->belongsTo(Experiment::class); }
    public function recommendation() { return $this->belongsTo(Recommendation::class); }
}
