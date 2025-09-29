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
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('height', 8, 2)->nullable()->after('shipping_price')->comment('Height in cm');
            $table->decimal('width', 8, 2)->nullable()->after('height')->comment('Width in cm');
            $table->decimal('depth', 8, 2)->nullable()->after('width')->comment('Depth in cm');
            $table->decimal('weight', 8, 2)->nullable()->after('depth')->comment('Weight in kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['height', 'width', 'depth', 'weight']);
        });
    }
};
