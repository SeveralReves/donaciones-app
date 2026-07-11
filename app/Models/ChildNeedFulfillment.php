<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildNeedFulfillment extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'child_need_id',
        'donation_id',
        'fulfilled_by',
        'fulfilled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fulfilled_at' => 'datetime',
        ];
    }

    public function childNeed(): BelongsTo
    {
        return $this->belongsTo(ChildNeed::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }
}
