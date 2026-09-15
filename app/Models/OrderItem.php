<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',       // new — FK to products (referential integrity fix)
        'customer_name',
        'product_name',     // kept as historical snapshot (correct POS practice)
        'price',
        'quantity',
        // 'addons' removed — 1NF fix, moved to order_item_addons table
        'addons_total',
        'item_total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function addonRows()
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    /**
     * Backward-compat accessor: $item->addons
     * Returns the same [['name'=>…,'price'=>…], …] array shape as before.
     */
    public function getAddonsAttribute(): array
    {
        return $this->addonRows
            ->map(fn($a) => ['name' => $a->addon_name, 'price' => (float) $a->addon_price])
            ->values()
            ->toArray();
    }
}
