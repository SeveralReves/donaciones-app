<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'birthdate',
        'guardian_name',
        'guardian_phone',
        'address',
        'active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            // Formato explícito: sin esto, Carbon serializa como ISO8601
            // completo y el <input type="date"> del formulario de edición no
            // reconoce el valor al precargarlo.
            'birthdate' => 'date:Y-m-d',
            'active' => 'boolean',
        ];
    }

    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'child_conditions');
    }

    public function needs(): HasMany
    {
        return $this->hasMany(ChildNeed::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
