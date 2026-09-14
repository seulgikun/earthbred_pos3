<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentage',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('checkout_discounts');
        });

        static::deleted(function () {
            Cache::forget('checkout_discounts');
        });
    }
}
