<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_name',
        'product_name',
        'price',
        'quantity',
        'addons',
        'addons_total',
        'item_total',
    ];

    protected $casts = [
        'addons' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
