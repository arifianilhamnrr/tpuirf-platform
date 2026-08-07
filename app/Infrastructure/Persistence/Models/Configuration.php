<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Configuration extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
        'description',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
