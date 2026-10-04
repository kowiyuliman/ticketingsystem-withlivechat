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
        Schema::table('chat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_templates', 'shortcut')) {
                $table->string('shortcut', 50)->nullable()->after('category')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('chat_templates', 'shortcut')) {
                $table->dropColumn('shortcut');
            }
        });
    }
};

