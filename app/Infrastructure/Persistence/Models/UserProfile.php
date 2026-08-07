<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'education',
        'occupation',
        'digital_literacy',
        'device_type',
        'visual_preference',
        'context_environment',
        'ai_familiarity',
        'daily_device_usage',
        'device_usage_time',
        'primary_device_purpose',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'additional_data' => 'array',
            'age' => 'integer',
            'digital_literacy' => 'integer',
            'ai_familiarity' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
