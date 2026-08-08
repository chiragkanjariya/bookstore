<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shree_maruti_series', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->after('series_id')->index();
        });

        // Every existing series predates the year field and belongs to 2025.
        DB::table('shree_maruti_series')->whereNull('year')->update(['year' => 2025]);

        // Year is mandatory from here on.
        Schema::table('shree_maruti_series', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shree_maruti_series', function (Blueprint $table) {
            $table->dropIndex(['year']);
            $table->dropColumn('year');
        });
    }
};
