<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('ready_to_ship_email_sent')->default(false)->after('shipped_email_sent');
            $table->boolean('delivered_email_sent')->default(false)->after('ready_to_ship_email_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ready_to_ship_email_sent', 'delivered_email_sent']);
        });
    }
};
