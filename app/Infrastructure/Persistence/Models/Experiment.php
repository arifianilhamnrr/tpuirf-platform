<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experiment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'protocol_version',
        'current_task',
        'task_order',
        'current_ui_version',
        'ui_order',
        'current_step',
        'started_at',
        'completed_at',
        'last_activity_at',
        'total_active_duration_ms',
        'consent_version',
        'consented_at',
        'preferred_ui',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'ui_order' => 'array',
            'task_order' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'consented_at' => 'datetime',
            'current_step' => 'integer',
            'total_active_duration_ms' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactionLogs()
    {
        return $this->hasMany(InteractionLog::class);
    }

    public function trustEvaluation()
    {
        return $this->hasOne(TrustEvaluation::class);
    }

    public function tasks()
    {
        return $this->hasMany(ExperimentTask::class)->orderBy('sequence');
    }

    public function interactionEvents()
    {
        return $this->hasMany(InteractionEvent::class);
    }

    public function participantPreference()
    {
        return $this->hasOne(ParticipantPreference::class);
    }

    public function xaiEvaluation()
    {
        return $this->hasOne(XaiEvaluation::class);
    }
}
