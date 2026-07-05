<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'unit',
        'donation_type',
        'donation_type_id',
        'quantity_available',
        'units_available',
        'minimum_threshold',
        'active',
        'deactivated_by',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity_available' => 'decimal:2',
            'minimum_threshold' => 'decimal:2',
            'active' => 'boolean',
            'deactivated_at' => 'datetime',
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

    public function donationType(): BelongsTo
    {
        return $this->belongsTo(DonationType::class);
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
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
