<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCategoryToAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('addons', 'category')) {
            Schema::table('addons', function (Blueprint $table) {
                $table->string('category')->default('drinks')->after('price');
            });
        }

        // Seed default food add-ons (Extra Rice and Extra Egg) if not already present
        $existingRice = DB::table('addons')->where('name', 'Extra Rice')->first();
        if (!$existingRice) {
            DB::table('addons')->insert([
                'name' => 'Extra Rice',
                'price' => 20.00,
                'category' => 'food',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingEgg = DB::table('addons')->where('name', 'Extra Egg')->first();
        if (!$existingEgg) {
            DB::table('addons')->insert([
                'name' => 'Extra Egg',
                'price' => 15.00,
                'category' => 'food',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('addons', 'category')) {
            Schema::table('addons', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
}
