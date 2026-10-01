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
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->index('ticket_id', 'idx_tc_ticket_id');
            $table->index(['ticket_id', 'created_at'], 'idx_tc_ticket_created');
            $table->index(['is_admin', 'read_at'], 'idx_tc_admin_read');
            $table->index(['ticket_id', 'is_admin', 'read_at'], 'idx_tc_ticket_admin_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex('idx_tc_ticket_id');
            $table->dropIndex('idx_tc_ticket_created');
            $table->dropIndex('idx_tc_admin_read');
            $table->dropIndex('idx_tc_ticket_admin_read');
        });
    }
};

