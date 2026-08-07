<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'date_of_birth',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function experiments()
    {
        return $this->hasMany(Experiment::class);
    }

    public function interactionLogs()
    {
        return $this->hasMany(InteractionLog::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function trustEvaluations()
    {
        return $this->hasMany(TrustEvaluation::class);
    }

    public function participantPreferences()
    {
        return $this->hasMany(ParticipantPreference::class);
    }

    public function interactionEvents()
    {
        return $this->hasMany(InteractionEvent::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isResearcher(): bool
    {
        return $this->role === 'researcher';
    }

    public function isRespondent(): bool
    {
        return $this->role === 'respondent';
    }
}
