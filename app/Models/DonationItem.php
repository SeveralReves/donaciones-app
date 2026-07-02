<?php

namespace App\Models;

use Database\Factories\DonationItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationItem extends Model
{
    /** @use HasFactory<DonationItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'donation_id',
        'stock_item_id',
        'name',
        'quantity',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
