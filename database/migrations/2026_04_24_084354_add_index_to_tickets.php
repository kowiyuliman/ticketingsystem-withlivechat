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
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index('status');
                $table->index('assigned_to');
                $table->index('user_id');
                $table->index('created_at');
            });
        } catch (\Throwable $e) {
            // Indexes already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
