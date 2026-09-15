<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'nomor_laptop')) {
                $table->string('nomor_laptop')->nullable()->after('ip_address');
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'hostname')) {
                $table->string('hostname')->nullable()->after('type');
            }
            if (!Schema::hasColumn('assets', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('hostname');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'nomor_laptop')) {
                $table->dropColumn('nomor_laptop');
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'hostname')) {
                $table->dropColumn('hostname');
            }
            if (Schema::hasColumn('assets', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};

