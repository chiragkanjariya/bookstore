<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('shipment_created_at')->nullable()->after('shipping_partner_error');
            $table->timestamp('label_printed_at')->nullable()->after('shipment_created_at');
        });

        // Collapse legacy shipping partner statuses onto the three supported ones:
        // pending -> shipment_created -> ready_to_ship
        DB::table('orders')
            ->where(function ($query) {
                $query->whereNull('shipping_partner_status')
                    ->orWhereNotIn('shipping_partner_status', ['pending', 'shipment_created', 'ready_to_ship']);
            })
            ->update(['shipping_partner_status' => 'pending']);

        // Backfill the shipment date for orders that already have a shipment.
        DB::table('orders')
            ->whereIn('shipping_partner_status', ['shipment_created', 'ready_to_ship'])
            ->whereNull('shipment_created_at')
            ->update([
                'shipment_created_at' => DB::raw('COALESCE(manual_shipping_marked_at, shipped_at, updated_at)'),
            ]);

        // Orders already at ready_to_ship got there by having their label printed.
        DB::table('orders')
            ->where('shipping_partner_status', 'ready_to_ship')
            ->whereNull('label_printed_at')
            ->update(['label_printed_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipment_created_at', 'label_printed_at']);
        });
    }
};
