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
        // Update any existing leader role to user
        DB::table('users')->where('role', 'leader')->update(['role' => 'user']);

        // Drop leader_id column if exists
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'leader_id')) {
                $table->dropColumn('leader_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'leader_id')) {
                $table->unsignedBigInteger('leader_id')->nullable();
            }
        });
    }
};

