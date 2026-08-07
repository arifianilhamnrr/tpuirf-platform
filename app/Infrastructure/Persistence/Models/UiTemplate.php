<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class UiTemplate extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'version',
        'name',
        'description',
        'config',
        'is_active',
        'thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
