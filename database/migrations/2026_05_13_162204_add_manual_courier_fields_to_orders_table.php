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
            $table->unsignedBigInteger('manual_courier_id')->nullable()->after('requires_manual_shipping');
            $table->string('manual_tracking_id')->nullable()->after('manual_courier_id');
            $table->string('manual_courier_name')->nullable()->after('manual_tracking_id');

            $table->foreign('manual_courier_id')->references('id')->on('manual_couriers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['manual_courier_id']);
            $table->dropColumn(['manual_courier_id', 'manual_tracking_id', 'manual_courier_name']);
        });
    }
};
