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
        'category_id',  // replaces category string (3NF fix)
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected $appends = ['category'];
    protected $with = ['categoryRecord'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('pos_addons');
        });

        static::deleted(function () {
            Cache::forget('pos_addons');
        });
    }

    public function categoryRecord()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Backward-compat accessor: $addon->category
     * Returns the category name string (e.g. 'food', 'drinks', 'all').
     */
    public function getCategoryAttribute(): ?string
    {
        return $this->categoryRecord->name ?? null;
    }

    /**
     * Backward-compat mutator: $addon->category = 'drinks'
     */
    public function setCategoryAttribute(?string $value): void
    {
        if (!empty($value)) {
            $cat = Category::findOrCreate($value, 'addon');
            $this->attributes['category_id'] = $cat->id;
        }
    }
}
