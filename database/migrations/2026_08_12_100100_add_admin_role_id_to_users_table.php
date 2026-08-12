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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_role_id')
                ->nullable()
                ->after('role')
                ->constrained('admin_roles')
                ->nullOnDelete();
        });

        // Existing admins keep the access they had before this change.
        $superAdminId = DB::table('admin_roles')->where('slug', 'super-admin')->value('id');

        if ($superAdminId) {
            DB::table('users')->where('role', 'admin')->update(['admin_role_id' => $superAdminId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_role_id']);
            $table->dropColumn('admin_role_id');
        });
    }
};
