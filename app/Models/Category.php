<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];

    /**
     * Find or create a category by name + type.
     */
    public static function findOrCreate(string $name, string $type): self
    {
        $name = trim($name);
        return static::firstOrCreate(
            ['name' => $name, 'type' => $type],
            ['name' => $name, 'type' => $type]
        );
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function shiftNotes()
    {
        return $this->hasMany(ShiftNote::class);
    }
}
