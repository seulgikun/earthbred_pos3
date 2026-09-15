<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 3 — 1NF: Move order_items.addons JSON blob
 * to a proper order_item_addons table (one row per addon per order item).
 * Existing JSON data is fully migrated. The OrderItem model provides
 * a getAddonsAttribute() accessor returning the same array shape for backward compat.
 */
return new class extends Migration
{
    public function up()
    {
        // Step 1: Create the normalized order_item_addons table
        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->string('addon_name');
            $table->decimal('addon_price', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('order_item_id')
                  ->references('id')
                  ->on('order_items')
                  ->onDelete('cascade');

            $table->index('order_item_id');
        });

        // Step 2: Migrate existing JSON addons → rows in order_item_addons
        $items = DB::table('order_items')
            ->whereNotNull('addons')
            ->where('addons', '!=', '[]')
            ->where('addons', '!=', 'null')
            ->get();

        foreach ($items as $item) {
            $addons = json_decode($item->addons, true);
            if (!is_array($addons)) continue;

            foreach ($addons as $addon) {
                if (empty($addon)) continue;

                $name  = is_array($addon) ? ($addon['name']  ?? '') : (string) $addon;
                $price = is_array($addon) ? ($addon['price'] ?? 0)  : 0;

                if (empty($name)) continue;

                DB::table('order_item_addons')->insert([
                    'order_item_id' => $item->id,
                    'addon_name'    => $name,
                    'addon_price'   => (float) $price,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // Step 3: Drop the old JSON column
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('addons');
        });
    }

    public function down()
    {
        // Restore the JSON addons column
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('addons')->nullable()->after('quantity');
        });

        // Reconstruct JSON blobs from order_item_addons rows
        $addonRows = DB::table('order_item_addons')->get()->groupBy('order_item_id');
        foreach ($addonRows as $orderItemId => $addons) {
            $json = $addons->map(fn($a) => ['name' => $a->addon_name, 'price' => (float) $a->addon_price])->values()->toJson();
            DB::table('order_items')->where('id', $orderItemId)->update(['addons' => $json]);
        }

        Schema::dropIfExists('order_item_addons');
    }
};
