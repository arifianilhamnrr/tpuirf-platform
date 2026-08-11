<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recommendation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'experiment_id',
        'recommended_ui',
        'confidence_score',
        'shap_values',
        'features',
        'explanations',
        'model_outputs',
        'input_features',
        'model_version',
    ];

    protected function casts(): array
    {
        return [
            'shap_values' => 'array',
            'features' => 'array',
            'explanations' => 'array',
            'model_outputs' => 'array',
            'input_features' => 'array',
            'confidence_score' => 'float',
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
