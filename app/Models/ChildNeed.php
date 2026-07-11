<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ChildNeed extends Model
{
    use HasUuids;

    protected $fillable = [
        'child_id',
        'description',
        'status',
        'is_recurring',
        'recurrence_interval_days',
        'last_covered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'recurrence_interval_days' => 'integer',
            'last_covered_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(ChildNeedFulfillment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Conteo anónimo de necesidades pendientes, agrupadas por descripción —
     * usado tanto por la página pública (/necesidades) como por el panel de
     * administración (/admin/needs). Deliberadamente nunca selecciona
     * child_id (ni ninguna columna de children): esta info puede llegar a
     * admins sin acceso al módulo de niños, así que no debe poder revelar a
     * qué niño corresponde cada necesidad ni ningún dato suyo.
     *
     * @return Collection<int, array{description: string, total_children_needing: int}>
     */
    public static function pendingCountsGroupedByDescription(): Collection
    {
        return static::query()
            ->where('status', 'pendiente')
            ->selectRaw('description, count(distinct child_id) as total_children_needing')
            ->groupBy('description')
            ->orderBy('description')
            ->get()
            ->map(fn ($row) => [
                'description' => $row->description,
                'total_children_needing' => (int) $row->total_children_needing,
            ]);
    }
}
