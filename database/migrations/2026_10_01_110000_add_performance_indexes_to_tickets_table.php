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
        Schema::table('tickets', function (Blueprint $table) {
            // Index for fast laptop SN ticket history lookup ($O(1))
            $table->index('nomor_laptop', 'idx_tickets_nomor_laptop');
            $table->index(['nomor_laptop', 'created_at'], 'idx_tickets_laptop_created');

            // Composite indexes for status + sorting timestamps in admin tabs
            $table->index(['status', 'created_at'], 'idx_tickets_status_created');
            $table->index(['status', 'started_at'], 'idx_tickets_status_started');
            $table->index(['status', 'resolved_at'], 'idx_tickets_status_resolved');
            $table->index(['status', 'updated_at'], 'idx_tickets_status_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_nomor_laptop');
            $table->dropIndex('idx_tickets_laptop_created');
            $table->dropIndex('idx_tickets_status_created');
            $table->dropIndex('idx_tickets_status_started');
            $table->dropIndex('idx_tickets_status_resolved');
            $table->dropIndex('idx_tickets_status_updated');
        });
    }
};

