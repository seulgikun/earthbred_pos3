<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 5 — 3NF: Replace free-text category VARCHAR columns in
 * products, addons, inventories, and shift_notes with a proper categories
 * lookup table and category_id FK.
 * Existing category string values are migrated automatically.
 */
return new class extends Migration
{
    public function up()
    {
        // Step 1: Create the categories lookup table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['product', 'addon', 'inventory', 'shift_note']);
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        // Step 2: Add category_id FK columns to each table
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('category');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
        Schema::table('addons', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('category');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('category');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
        Schema::table('shift_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('is_done');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        // Step 3: Migrate existing category strings → categories table + set category_id
        $this->migrateCategories('products',   'product');
        $this->migrateCategories('addons',      'addon');
        $this->migrateCategories('inventories', 'inventory');
        $this->migrateCategories('shift_notes', 'shift_note');

        // Step 4: Drop old category VARCHAR columns
        Schema::table('products',    fn(Blueprint $t) => $t->dropColumn('category'));
        Schema::table('addons',      fn(Blueprint $t) => $t->dropColumn('category'));
        Schema::table('inventories', fn(Blueprint $t) => $t->dropColumn('category'));
        Schema::table('shift_notes', fn(Blueprint $t) => $t->dropColumn('category'));
    }

    public function down()
    {
        // Restore category varchar columns
        Schema::table('products',    fn(Blueprint $t) => $t->string('category')->nullable()->after('name'));
        Schema::table('addons',      fn(Blueprint $t) => $t->string('category')->nullable()->after('price'));
        Schema::table('inventories', fn(Blueprint $t) => $t->string('category')->nullable()->after('item_name'));
        Schema::table('shift_notes', fn(Blueprint $t) => $t->string('category')->default('General')->after('is_done'));

        // Re-populate category strings from category_id
        foreach (['products' => 'product', 'addons' => 'addon', 'inventories' => 'inventory', 'shift_notes' => 'shift_note'] as $table => $type) {
            $rows = DB::table($table)->whereNotNull('category_id')->get();
            foreach ($rows as $row) {
                $cat = DB::table('categories')->find($row->category_id);
                if ($cat) {
                    DB::table($table)->where('id', $row->id)->update(['category' => $cat->name]);
                }
            }
        }

        // Drop FKs and category_id columns
        Schema::table('products',    fn(Blueprint $t) => $t->dropForeign(['category_id']));
        Schema::table('addons',      fn(Blueprint $t) => $t->dropForeign(['category_id']));
        Schema::table('inventories', fn(Blueprint $t) => $t->dropForeign(['category_id']));
        Schema::table('shift_notes', fn(Blueprint $t) => $t->dropForeign(['category_id']));

        Schema::table('products',    fn(Blueprint $t) => $t->dropColumn('category_id'));
        Schema::table('addons',      fn(Blueprint $t) => $t->dropColumn('category_id'));
        Schema::table('inventories', fn(Blueprint $t) => $t->dropColumn('category_id'));
        Schema::table('shift_notes', fn(Blueprint $t) => $t->dropColumn('category_id'));

        Schema::dropIfExists('categories');
    }

    private function migrateCategories(string $table, string $type): void
    {
        // Get distinct category values
        $rows = DB::table($table)->whereNotNull('category')->where('category', '!=', '')->get(['id', 'category']);

        $categoryCache = [];

        foreach ($rows as $row) {
            $name = trim($row->category);
            if (empty($name)) continue;

            $cacheKey = strtolower($name);
            if (!isset($categoryCache[$cacheKey])) {
                // Insert or find the category
                $existing = DB::table('categories')
                    ->where('type', $type)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                    ->first();

                if ($existing) {
                    $categoryCache[$cacheKey] = $existing->id;
                } else {
                    $id = DB::table('categories')->insertGetId([
                        'name'       => $name,
                        'type'       => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $categoryCache[$cacheKey] = $id;
                }
            }

            DB::table($table)->where('id', $row->id)->update(['category_id' => $categoryCache[$cacheKey]]);
        }
    }
};
