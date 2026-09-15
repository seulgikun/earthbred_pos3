<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtotal',
        'discount_id',      // 3NF fix: FK to discounts lookup table
        'discount_percent',
        'discount_amount',
        'total',
        'payment_method',
        'status',
        'cashier_id',       // 3NF fix: FK to users lookup table
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    /**
     * Backward-compat accessor: $order->cashier_name
     * Reads from the users table via the cashier relationship.
     */
    public function getCashierNameAttribute(): ?string
    {
        return $this->cashier->name ?? null;
    }

    /**
     * Backward-compat mutator: set cashier_name
     */
    public function setCashierNameAttribute(?string $value): void
    {
        if (!empty($value) && !$this->cashier_id) {
            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])->first();
            if ($user) {
                $this->attributes['cashier_id'] = $user->id;
            }
        }
    }
}
