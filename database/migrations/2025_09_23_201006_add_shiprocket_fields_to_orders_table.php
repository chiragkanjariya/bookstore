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
            $table->string('shiprocket_order_id')->nullable()->after('razorpay_signature');
            $table->string('shiprocket_shipment_id')->nullable()->after('shiprocket_order_id');
            $table->string('tracking_number')->nullable()->after('shiprocket_shipment_id');
            $table->string('courier_company')->nullable()->after('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shiprocket_order_id', 'shiprocket_shipment_id', 'tracking_number', 'courier_company']);
        });
    }
};
