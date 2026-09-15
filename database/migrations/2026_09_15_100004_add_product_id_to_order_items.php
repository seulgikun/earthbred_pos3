<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 4 — Referential Integrity: Add product_id FK to order_items.
 * product_name is kept as a historical snapshot (correct POS practice).
 * product_id links to the products table for relational integrity.
 */
return new class extends Migration
{
    public function up()
    {
        // Step 1: Add product_id column
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('order_id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');
            $table->index('product_id');
        });

        // Step 2: Populate product_id by matching existing product_name to products.name
        $products = DB::table('products')->get()->keyBy(fn($p) => strtolower(trim($p->name)));

        $items = DB::table('order_items')->whereNull('product_id')->get();
        foreach ($items as $item) {
            $key = strtolower(trim($item->product_name));
            $product = $products->get($key);
            if ($product) {
                DB::table('order_items')
                    ->where('id', $item->id)
                    ->update(['product_id' => $product->id]);
            }
        }
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
