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
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Super Admin always has every menu; its permission list is ignored.
        DB::table('admin_roles')->insert([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to every admin menu. Cannot be edited or deleted.',
            'permissions' => json_encode([]),
            'is_super_admin' => true,
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
