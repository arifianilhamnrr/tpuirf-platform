<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recommendation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'experiment_id',
        'recommended_ui',
        'recommended_config',
        'confidence_score',
        'confidence_by_target',
        'shap_values',
        'features',
        'explanations',
        'model_outputs',
        'input_features',
        'model_version',
        'algorithm',
        'predicted_at',
    ];

    protected function casts(): array
    {
        return [
            'recommended_config' => 'array',
            'confidence_by_target' => 'array',
            'shap_values' => 'array',
            'features' => 'array',
            'explanations' => 'array',
            'model_outputs' => 'array',
            'input_features' => 'array',
            'confidence_score' => 'float',
            'predicted_at' => 'datetime',
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

    public function xaiEvaluation()
    {
        return $this->hasOne(XaiEvaluation::class);
    }
}
