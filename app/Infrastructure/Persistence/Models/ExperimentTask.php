<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExperimentTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'experiment_id',
        'task_key',
        'sequence',
        'status',
        'option_order',
        'selected_option',
        'selection_payload',
        'active_duration_ms',
        'idle_duration_ms',
        'change_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'option_order' => 'array',
            'selection_payload' => 'array',
            'sequence' => 'integer',
            'active_duration_ms' => 'integer',
            'idle_duration_ms' => 'integer',
            'change_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function interactionEvents()
    {
        return $this->hasMany(InteractionEvent::class);
    }
}
