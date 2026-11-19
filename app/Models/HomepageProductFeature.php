<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageProductFeature extends Model
{
    protected $fillable = [
        'type',
        'section_title',
        'product_id',
        'item_title',
        'description',
        'promo_price',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'promo_price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_active', true)
            ->whereHas('product', fn ($product) => $product->where('is_active', true))
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
