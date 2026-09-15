<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 9 — 3NF / Referential Integrity: Add user_id FK to audit_logs table.
 * manager_id is retained as legacy identifier, while user_id establishes direct FK to users table.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            $table->index('user_id');
        });

        // Populate user_id where manager_id is a valid integer user ID
        $users = DB::table('users')->pluck('id')->toArray();
        $logs = DB::table('audit_logs')->get();

        foreach ($logs as $log) {
            if (is_numeric($log->manager_id) && in_array((int)$log->manager_id, $users)) {
                DB::table('audit_logs')->where('id', $log->id)->update([
                    'user_id' => (int) $log->manager_id
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
