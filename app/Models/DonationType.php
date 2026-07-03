<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DonationType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'requires_doctor_data',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'requires_doctor_data' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
