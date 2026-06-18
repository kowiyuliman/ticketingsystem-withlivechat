<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update assets table
        Schema::table('assets', function (Blueprint $table) {
            // Drop model if exists
            if (Schema::hasColumn('assets', 'model')) {
                $table->dropColumn('model');
            }
            // Drop asset_code if exists
            if (Schema::hasColumn('assets', 'asset_code')) {
                $table->dropColumn('asset_code');
            }
            // Add received_at
            if (!Schema::hasColumn('assets', 'received_at')) {
                $table->date('received_at')->nullable()->after('serial_number');
            }
            // Ensure serial_number is unique and not null
            $table->string('serial_number')->change();
        });

        // Update asset_repairs table (drop repair_cost)
        Schema::table('asset_repairs', function (Blueprint $table) {
            if (Schema::hasColumn('asset_repairs', 'repair_cost')) {
                $table->dropColumn('repair_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('model', 100)->nullable()->after('brand');
            $table->string('asset_code')->unique()->after('id');
            $table->dropColumn('received_at');
        });

        Schema::table('asset_repairs', function (Blueprint $table) {
            $table->decimal('repair_cost', 12, 2)->nullable()->after('completed_date');
        });
    }
};
