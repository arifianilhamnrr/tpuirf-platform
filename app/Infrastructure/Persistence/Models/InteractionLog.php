<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InteractionLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'experiment_id',
        'user_id',
        'ui_version',
        'event_type',
        'event_data',
        'target_element',
        'page_url',
        'viewport_width',
        'viewport_height',
        'browser',
        'os',
        'task_time',
        'session_time',
        'idle_time',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'event_data' => 'array',
            'viewport_width' => 'integer',
            'viewport_height' => 'integer',
            'task_time' => 'float',
            'session_time' => 'float',
            'idle_time' => 'float',
            'timestamp' => 'datetime',
        ];
    }

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
