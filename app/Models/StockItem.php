<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'unit',
        'donation_type',
        'quantity_available',
        'minimum_threshold',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_available' => 'decimal:2',
            'minimum_threshold' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function donationItems(): HasMany
    {
        return $this->hasMany(DonationItem::class);
    }

    /**
     * Insumos activos que están por debajo de su umbral mínimo. Un
     * minimum_threshold null significa "no monitorear este insumo" —
     * whereNotNull lo deja fuera aunque su stock esté en cero.
     */
    public function scopeBelowThreshold(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->whereNotNull('minimum_threshold')
            ->whereColumn('quantity_available', '<', 'minimum_threshold');
    }
}
