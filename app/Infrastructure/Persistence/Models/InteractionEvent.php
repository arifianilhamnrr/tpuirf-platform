<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InteractionEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_uuid',
        'experiment_id',
        'user_id',
        'experiment_task_id',
        'sequence_number',
        'protocol_version',
        'task_key',
        'option_id',
        'component_id',
        'event_type',
        'client_timestamp',
        'server_received_at',
        'elapsed_task_ms',
        'elapsed_experiment_ms',
        'delta_previous_event_ms',
        'delta_previous_click_ms',
        'previous_component_id',
        'viewport_width',
        'viewport_height',
        'input_method',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'client_timestamp' => 'datetime',
            'server_received_at' => 'datetime',
            'elapsed_task_ms' => 'integer',
            'elapsed_experiment_ms' => 'integer',
            'delta_previous_event_ms' => 'integer',
            'delta_previous_click_ms' => 'integer',
            'viewport_width' => 'integer',
            'viewport_height' => 'integer',
            'payload' => 'array',
        ];
    }

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function experimentTask()
    {
        return $this->belongsTo(ExperimentTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
