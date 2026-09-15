<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 8 — Many-to-Many Normalization: Normalize product ingredients/recipes.
 * Replaces hardcoded string heuristics with a normalized product_ingredients pivot table.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_id');
            $table->decimal('quantity_required', 8, 2)->default(1.00);
            $table->timestamps();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            $table->foreign('inventory_id')
                  ->references('id')
                  ->on('inventories')
                  ->onDelete('cascade');

            $table->unique(['product_id', 'inventory_id']);
            $table->index('product_id');
            $table->index('inventory_id');
        });

        // Seed initial recipes based on products and inventories
        $products = DB::table('products')->get();
        $inventories = DB::table('inventories')->get();

        if ($products->isNotEmpty() && $inventories->isNotEmpty()) {
            foreach ($products as $p) {
                $pName = strtolower(trim($p->name));

                // Find matching inventory items
                foreach ($inventories as $inv) {
                    $invName = strtolower(trim($inv->item_name));
                    $matched = false;

                    // Direct name match
                    if ($invName === $pName || str_contains($invName, $pName) || str_contains($pName, $invName)) {
                        $matched = true;
                    }
                    // Coffee products -> Espresso / Coffee beans
                    elseif ((str_contains($pName, 'americano') || str_contains($pName, 'latte') || str_contains($pName, 'mocha') || str_contains($pName, 'cappuccino') || str_contains($pName, 'espresso')) && (str_contains($invName, 'bean') || str_contains($invName, 'coffee') || str_contains($invName, 'espresso'))) {
                        $matched = true;
                    }
                    // Matcha products -> Matcha ingredients
                    elseif (str_contains($pName, 'matcha') && str_contains($invName, 'matcha')) {
                        $matched = true;
                    }
                    // Strawberry products -> Strawberry ingredients
                    elseif (str_contains($pName, 'strawberry') && str_contains($invName, 'strawberry')) {
                        $matched = true;
                    }
                    // Lemonade products -> Lemon ingredients
                    elseif ((str_contains($pName, 'lemonade') || str_contains($pName, 'lemon')) && str_contains($invName, 'lemon')) {
                        $matched = true;
                    }
                    // Bacon / Chicken / Beef / Longganisa
                    elseif (str_contains($pName, 'bacon') && str_contains($invName, 'bacon')) {
                        $matched = true;
                    } elseif (str_contains($pName, 'chicken') && str_contains($invName, 'chicken')) {
                        $matched = true;
                    } elseif ((str_contains($pName, 'beef') || str_contains($pName, 'tapa')) && (str_contains($invName, 'beef') || str_contains($invName, 'tapa'))) {
                        $matched = true;
                    } elseif (str_contains($pName, 'longganisa') && str_contains($invName, 'longganisa')) {
                        $matched = true;
                    }

                    if ($matched) {
                        DB::table('product_ingredients')->updateOrInsert(
                            ['product_id' => $p->id, 'inventory_id' => $inv->id],
                            ['quantity_required' => 1.00, 'created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_ingredients');
    }
};
