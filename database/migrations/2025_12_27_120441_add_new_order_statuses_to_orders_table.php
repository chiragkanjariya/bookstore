<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status column to include new statuses
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending_to_be_prepared', 'ready_to_ship', 'pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending_to_be_prepared'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original status values
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
