<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalization Fix 2 — 3NF: Replace shift_notes.cashier_name (plain string)
 * with cashier_id (FK → users.id). Existing cashier_name values are matched
 * to users by name and migrated. Unmatched rows get cashier_id = null.
 */
return new class extends Migration
{
    public function up()
    {
        // Step 1: Add cashier_id FK column
        Schema::table('shift_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('cashier_id')->nullable()->after('cashier_name');
            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
        });

        // Step 2: Migrate existing cashier_name → cashier_id by name matching
        $notes = DB::table('shift_notes')->whereNotNull('cashier_name')->get();
        foreach ($notes as $note) {
            $user = DB::table('users')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($note->cashier_name))])
                ->first();
            if ($user) {
                DB::table('shift_notes')
                    ->where('id', $note->id)
                    ->update(['cashier_id' => $user->id]);
            }
        }

        // Step 3: Drop old cashier_name column
        Schema::table('shift_notes', function (Blueprint $table) {
            $table->dropColumn('cashier_name');
        });
    }

    public function down()
    {
        Schema::table('shift_notes', function (Blueprint $table) {
            $table->string('cashier_name')->nullable()->after('note');
        });

        // Restore cashier_name from user records where possible
        $notes = DB::table('shift_notes')->whereNotNull('cashier_id')->get();
        foreach ($notes as $note) {
            $user = DB::table('users')->find($note->cashier_id);
            if ($user) {
                DB::table('shift_notes')
                    ->where('id', $note->id)
                    ->update(['cashier_name' => $user->name]);
            }
        }

        Schema::table('shift_notes', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropColumn('cashier_id');
        });
    }
};
