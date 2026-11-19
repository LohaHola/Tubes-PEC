<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'order_number',
    'recipient_name',
    'recipient_phone',
    'recipient_address',
    'city',
    'province',
    'postal_code',
    'notes',
    'subtotal',
    'shipping_cost',
    'total_amount',
    'payment_status',
    'fulfillment_status',
    'inventory_status',
    'reserved_until',
])]
class Order extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'reserved_until' => 'datetime',
        ];
    }
}
