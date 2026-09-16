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
            if (!Schema::hasColumn('ticket_comments', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
            }
            if (!Schema::hasColumn('ticket_comments', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_comments', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('ticket_comments', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
        });
    }
};
