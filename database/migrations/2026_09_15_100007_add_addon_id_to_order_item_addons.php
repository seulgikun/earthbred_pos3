<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 7 — Referential Integrity: Add addon_id FK to order_item_addons.
 * addon_name is retained as historical snapshot; addon_id links to addons table.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('addon_id')->nullable()->after('order_item_id');
            $table->foreign('addon_id')
                  ->references('id')
                  ->on('addons')
                  ->onDelete('set null');
            $table->index('addon_id');
        });

        // Backfill addon_id matching addon_name to addons.name
        $addons = DB::table('addons')->get()->keyBy(fn($a) => strtolower(trim($a->name)));
        $rows = DB::table('order_item_addons')->whereNull('addon_id')->get();

        foreach ($rows as $row) {
            $key = strtolower(trim($row->addon_name));
            if ($addons->has($key)) {
                DB::table('order_item_addons')->where('id', $row->id)->update([
                    'addon_id' => $addons->get($key)->id
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            $table->dropForeign(['addon_id']);
            $table->dropIndex(['addon_id']);
            $table->dropColumn('addon_id');
        });
    }
};
