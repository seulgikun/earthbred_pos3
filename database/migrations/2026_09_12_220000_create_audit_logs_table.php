<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('manager_id')->nullable();     // e.g. 'MGR-001' or user id
            $table->string('manager_name')->nullable();   // e.g. 'Earthbred Owner' or 'Juan Reyes'
            $table->string('manager_role')->nullable();   // e.g. 'owner', 'manager'
            $table->string('action');                     // 'VOID_OVERRIDE', 'PRICE_UPDATE', 'PRODUCT_CREATE', 'VOID_PIN_UPDATE'
            $table->string('target')->nullable();         // e.g. 'Order #104', 'Product: Iced Latte'
            $table->text('details');                      // Human-readable description of change
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
