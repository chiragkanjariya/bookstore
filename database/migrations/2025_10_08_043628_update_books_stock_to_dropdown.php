<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Add a temporary column
            $table->enum('stock_temp', ['in_stock', 'limited_stock', 'out_of_stock'])->default('in_stock');
        });
        
        // Update the temporary column based on existing stock values
        DB::statement("UPDATE books SET stock_temp = CASE 
            WHEN stock > 10 THEN 'in_stock'
            WHEN stock > 0 AND stock <= 10 THEN 'limited_stock'
            ELSE 'out_of_stock'
        END");
        
        Schema::table('books', function (Blueprint $table) {
            // Drop the old column and rename the temp column
            $table->dropColumn('stock');
        });
        
        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('stock_temp', 'stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('stock')->default(0)->change();
        });
    }
};
