<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 6 — Referential Integrity / 3NF: Add discount_id FK to orders table.
 * Links applied discounts directly to the discounts lookup table.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_id')->nullable()->after('subtotal');
            $table->foreign('discount_id')
                  ->references('id')
                  ->on('discounts')
                  ->onDelete('set null');
            $table->index('discount_id');
        });

        // Backfill discount_id for existing orders with discount_percent > 0
        $discounts = DB::table('discounts')->get()->keyBy('percentage');
        $ordersWithDiscounts = DB::table('orders')->where('discount_percent', '>', 0)->get();

        foreach ($ordersWithDiscounts as $order) {
            $pct = (int) $order->discount_percent;
            if ($discounts->has($pct)) {
                DB::table('orders')->where('id', $order->id)->update([
                    'discount_id' => $discounts->get($pct)->id
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropIndex(['discount_id']);
            $table->dropColumn('discount_id');
        });
    }
};
