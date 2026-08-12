<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move admin roles from a single column to a pivot so an admin can hold
     * several roles at once, their access being the union of all of them.
     */
    public function up(): void
    {
        Schema::create('admin_role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'admin_role_id']);
        });

        // Carry existing single-role assignments over to the pivot.
        $assignments = DB::table('users')
            ->whereNotNull('admin_role_id')
            ->get(['id', 'admin_role_id']);

        foreach ($assignments as $assignment) {
            DB::table('admin_role_user')->insert([
                'user_id' => $assignment->id,
                'admin_role_id' => $assignment->admin_role_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_role_id')
                ->nullable()
                ->after('role')
                ->constrained('admin_roles')
                ->nullOnDelete();
        });

        // Only one role can survive the trip back; keep the first assigned.
        $pivots = DB::table('admin_role_user')->orderBy('id')->get();
        $seen = [];

        foreach ($pivots as $pivot) {
            if (isset($seen[$pivot->user_id])) {
                continue;
            }

            $seen[$pivot->user_id] = true;
            DB::table('users')->where('id', $pivot->user_id)->update(['admin_role_id' => $pivot->admin_role_id]);
        }

        Schema::dropIfExists('admin_role_user');
    }
};
