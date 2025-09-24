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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('address')->constrained()->onDelete('set null');
            $table->foreignId('district_id')->nullable()->after('state_id')->constrained()->onDelete('set null');
            $table->foreignId('taluka_id')->nullable()->after('district_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['taluka_id']);
            $table->dropColumn(['state_id', 'district_id', 'taluka_id']);
        });
    }
};
