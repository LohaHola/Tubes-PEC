<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'sku',
    'name',
    'slug',
    'description',
    'specifications',
    'price',
    'stock',
    'image_path',
    'is_active',
])]
class Product extends Model
{
    use SoftDeletes;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }
    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)
            ->orderBy('sort_order');
    }
}