<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'category',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('pos_addons');
        });

        static::deleted(function () {
            Cache::forget('pos_addons');
        });
    }
}
