<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('requires_manual_shipping')->default(false)->after('courier_awb_number');
            $table->timestamp('manual_shipping_marked_at')->nullable()->after('requires_manual_shipping');

            // Index for filtering manual shipping orders
            $table->index(['requires_manual_shipping', 'manual_shipping_marked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['requires_manual_shipping', 'manual_shipping_marked_at']);
            $table->dropColumn(['requires_manual_shipping', 'manual_shipping_marked_at']);
        });
    }
};
