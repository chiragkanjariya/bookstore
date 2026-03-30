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
        Schema::create('shree_maruti_series', function (Blueprint $table) {
            $table->id();
            $table->integer('series_id')->index();
            $table->string('awb_number')->unique();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->boolean('is_used')->default(false)->index();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shree_maruti_series');
    }
};
