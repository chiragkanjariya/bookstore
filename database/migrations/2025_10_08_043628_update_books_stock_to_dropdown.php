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
        if (Schema::hasColumn('books', 'stock') && !Schema::hasColumn('books', 'stock_temp')) {
            Schema::table('books', function (Blueprint $table) {
                $table->enum('stock_temp', ['in_stock', 'limited_stock', 'out_of_stock'])->default('in_stock');
            });
            
            DB::statement("UPDATE books SET stock_temp = CASE 
                WHEN stock > 10 THEN 'in_stock'
                WHEN stock > 0 AND stock <= 10 THEN 'limited_stock'
                ELSE 'out_of_stock'
            END");
            
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('stock');
            });

            DB::statement("ALTER TABLE books CHANGE stock_temp stock ENUM('in_stock', 'limited_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock'");
        } elseif (!Schema::hasColumn('books', 'stock') && Schema::hasColumn('books', 'stock_temp')) {
            DB::statement("ALTER TABLE books CHANGE stock_temp stock ENUM('in_stock', 'limited_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock'");
        }
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
