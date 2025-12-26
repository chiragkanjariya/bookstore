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
        Schema::create('serviceable_zipcodes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 10)->unique();
            $table->string('hub', 100);
            $table->string('city', 100);
            $table->string('state_code', 10);
            $table->enum('is_serviceable', ['YES', 'NO'])->default('YES');
            $table->timestamps();

            // Indexes for fast lookups
            $table->index('pincode');
            $table->index(['city', 'state_code']);
            $table->index('is_serviceable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceable_zipcodes');
    }
};
