<?php

namespace App\Models;

use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_name',
        'donation_type',
        'location',
        'receiving_doctor_name',
        'receiving_doctor_code',
        'receiving_service',
        'contact_number',
        'cedula',
        'vehicle_type',
        'delivery_name',
        'delivery_contact',
        'status',
        'created_by',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DonationItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DonationStatusLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
