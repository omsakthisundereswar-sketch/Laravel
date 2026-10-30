<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unique_code',
        'price',
        'tax_percentage',
        'stock_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeLowStock($query, $threshold = null)
    {
        $threshold = $threshold ?? config('store.low_stock_threshold', 10);

        return $query->where('stock_quantity', '<', $threshold);
    }
}
