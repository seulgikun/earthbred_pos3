<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',  // replaces category string (3NF fix)
        'price',
        'discounted_price',
        'picture'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('pos_products');
        });

        static::deleted(function () {
            Cache::forget('pos_products');
        });
    }

    public function categoryRecord()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Normalized M:N relationship with Inventories (Recipes / Ingredients)
     */
    public function ingredients()
    {
        return $this->belongsToMany(Inventory::class, 'product_ingredients')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }

    /**
     * Backward-compat accessor: $product->category
     * Returns the category name string.
     */
    public function getCategoryAttribute(): ?string
    {
        return $this->categoryRecord->name ?? null;
    }

    /**
     * Backward-compat mutator: $product->category = 'Coffee'
     * Automatically resolves or creates category in categories table.
     */
    public function setCategoryAttribute(?string $value): void
    {
        if (!empty($value)) {
            $cat = Category::findOrCreate($value, 'product');
            $this->attributes['category_id'] = $cat->id;
        }
    }

    /**
     * Determine if product is out of stock based on ingredient inventory.
     * Checks normalized product_ingredients relationship first, with fallback heuristics.
     *
     * @param \Illuminate\Support\Collection|null $inventories
     * @return bool
     */
    public function isOutOfStock($inventories = null)
    {
        if ($inventories === null) {
            $inventories = Inventory::all(['id', 'item_name', 'quantity', 'category_id']);
        }

        // 1. Normalized relationship check
        $linkedIngredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        if ($linkedIngredients->isNotEmpty()) {
            $invKeyed = $inventories->keyBy('id');
            foreach ($linkedIngredients as $ing) {
                $stock = $invKeyed->get($ing->id);
                $qty = $stock ? (float) $stock->quantity : (float) $ing->quantity;
                $required = (float) ($ing->pivot->quantity_required ?? 1.0);
                if ($qty < $required || $qty <= 0) {
                    return true;
                }
            }
            return false;
        }

        $prodName = strtolower(trim($this->name));
        $prodCat = strtolower(trim((string) $this->category));

        // 2. Direct item match in inventory
        foreach ($inventories as $inv) {
            $invName = strtolower(trim($inv->item_name));
            if (($invName === $prodName || str_contains($invName, $prodName) || str_contains($prodName, $invName)) && $inv->quantity <= 0) {
                return true;
            }
        }

        // 3. Coffee drinks: ANY coffee/espresso bean at 0 → out of stock
        if ($prodCat === 'coffee' || str_contains($prodName, 'americano') || str_contains($prodName, 'latte') || str_contains($prodName, 'mocha') || str_contains($prodName, 'cappuccino') || str_contains($prodName, 'macchiato') || str_contains($prodName, 'espresso')) {
            $coffeeBeans = $inventories->filter(function($i) {
                $name = strtolower($i->item_name);
                return str_contains($name, 'espresso bean') || str_contains($name, 'coffee bean') || (str_contains($name, 'bean') && !str_contains($name, 'jelly'));
            });

            if ($coffeeBeans->isNotEmpty() && $coffeeBeans->contains(fn($b) => $b->quantity <= 0)) {
                return true;
            }
        }

        // 4. Matcha drinks: ANY matcha ingredient at 0 → out of stock
        if (str_contains($prodName, 'matcha')) {
            $matchaStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'matcha'));
            if ($matchaStock->isNotEmpty() && $matchaStock->contains(fn($m) => $m->quantity <= 0)) {
                return true;
            }
        }

        // 5. Strawberry drinks/foods
        if (str_contains($prodName, 'strawberry')) {
            $strawStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'strawberry'));
            if ($strawStock->isNotEmpty() && $strawStock->contains(fn($s) => $s->quantity <= 0)) {
                return true;
            }
        }

        // 6. Lemonade drinks
        if ($prodCat === 'lemonade' || str_contains($prodName, 'lemonade') || str_contains($prodName, 'lemon')) {
            $lemonStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'lemon'));
            if ($lemonStock->isNotEmpty() && $lemonStock->contains(fn($l) => $l->quantity <= 0)) {
                return true;
            }
        }

        // 7. Caramel flavored products
        if (str_contains($prodName, 'caramel')) {
            $caramelStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'caramel'));
            if ($caramelStock->isNotEmpty() && $caramelStock->contains(fn($c) => $c->quantity <= 0)) {
                return true;
            }
        }

        // 8. Blueberry flavored products
        if (str_contains($prodName, 'blueberry')) {
            $blueStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'blueberry'));
            if ($blueStock->isNotEmpty() && $blueStock->contains(fn($b) => $b->quantity <= 0)) {
                return true;
            }
        }

        // 9. Buldak foods
        if (str_contains($prodName, 'buldak')) {
            $buldakStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'buldak'));
            if ($buldakStock->isNotEmpty() && $buldakStock->contains(fn($b) => $b->quantity <= 0)) {
                return true;
            }
        }

        // 10. Chicken dishes
        if (str_contains($prodName, 'chicken')) {
            $chickenStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'chicken'));
            if ($chickenStock->isNotEmpty() && $chickenStock->contains(fn($c) => $c->quantity <= 0)) {
                return true;
            }
        }

        // 11. Beef Tapa
        if (str_contains($prodName, 'beef') || str_contains($prodName, 'tapa')) {
            $beefStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'beef') || str_contains(strtolower($i->item_name), 'tapa'));
            if ($beefStock->isNotEmpty() && $beefStock->contains(fn($b) => $b->quantity <= 0)) {
                return true;
            }
        }

        // 12. Longganisa
        if (str_contains($prodName, 'longganisa')) {
            $longStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'longganisa'));
            if ($longStock->isNotEmpty() && $longStock->contains(fn($l) => $l->quantity <= 0)) {
                return true;
            }
        }

        // 13. Bacon
        if (str_contains($prodName, 'bacon')) {
            $baconStock = $inventories->filter(fn($i) => str_contains(strtolower($i->item_name), 'bacon'));
            if ($baconStock->isNotEmpty() && $baconStock->contains(fn($b) => $b->quantity <= 0)) {
                return true;
            }
        }

        return false;
    }
}
