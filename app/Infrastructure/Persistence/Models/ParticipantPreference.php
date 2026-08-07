<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ParticipantPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'experiment_id',
        'user_id',
        'theme',
        'font_family',
        'font_size',
        'layout_density',
        'navigation_position',
        'icon_style',
        'design_style',
        'final_config',
    ];

    protected function casts(): array
    {
        return [
            'final_config' => 'array',
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
