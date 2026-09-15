<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization Fix 1 — 3NF: Remove transitive dependency orders.cashier_name.
 * cashier_name is fully derivable via orders.cashier_id → users.name.
 * The Order model provides a getCashierNameAttribute() accessor for backward compatibility.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop index first if it exists
            try {
                $table->dropIndex(['cashier_name']);
            } catch (\Exception $e) {
                // Index may not exist — safe to ignore
            }
            $table->dropColumn('cashier_name');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cashier_name')->nullable()->after('cashier_id')->index();
        });
    }
};
