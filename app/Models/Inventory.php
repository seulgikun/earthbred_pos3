<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_name',
        'category_id',  // replaces category string (3NF fix)
        'quantity',
        'min_threshold',
        'latest_issue_type'
    ];

    protected $appends = ['category'];
    protected $with = ['categoryRecord'];

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function categoryRecord()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Normalized M:N relationship with Products (used in recipes)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * Backward-compat accessor: $item->category
     * Returns the category name string.
     */
    public function getCategoryAttribute(): ?string
    {
        return $this->categoryRecord->name ?? null;
    }

    /**
     * Backward-compat mutator: $item->category = 'Packaging'
     */
    public function setCategoryAttribute(?string $value): void
    {
        if (!empty($value)) {
            $cat = Category::findOrCreate($value, 'inventory');
            $this->attributes['category_id'] = $cat->id;
        }
    }
}
