<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 1. Remove duplicate inventory rows (keep the latest by id).
     * 2. Add a unique index on item_name.
     */
    public function up(): void
    {
        // Step 1: Remove duplicate active (non-soft-deleted) rows, keep highest id
        $dupes = DB::select(
            'SELECT item_name FROM inventories WHERE deleted_at IS NULL GROUP BY item_name HAVING COUNT(*) > 1'
        );

        foreach ($dupes as $dupe) {
            $ids = DB::table('inventories')
                ->where('item_name', $dupe->item_name)
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();

            // Remove all but the last (highest id)
            array_pop($ids);
            if (!empty($ids)) {
                DB::table('inventories')->whereIn('id', $ids)->delete();
            }
        }

        // Step 2: Add unique index using raw SQL check (avoid Doctrine DBAL)
        $indexes = DB::select("SHOW INDEX FROM inventories WHERE Key_name = 'inventories_item_name_unique'");
        if (empty($indexes)) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->unique('item_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(['item_name']);
        });
    }
};
