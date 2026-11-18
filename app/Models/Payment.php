<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'provider',
    'provider_order_id',
    'provider_transaction_id',
    'payment_method',
    'snap_token',
    'redirect_url',
    'qr_code_url',
    'amount',
    'currency',
    'status',
    'provider_status',
    'fraud_status',
    'paid_at',
    'expired_at',
    'metadata',
])]
class Payment extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }
}
