<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MedicalReceiver extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'codigo_medico',
        'servicio',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
