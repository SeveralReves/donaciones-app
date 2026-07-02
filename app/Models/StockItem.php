<?php

namespace App\Models;

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
        'active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_available' => 'decimal:2',
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
}
