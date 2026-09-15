<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->string('jenis');
                $table->string('merk');
                $table->string('sn')->unique();
                $table->date('tanggal_masuk')->nullable();
                $table->string('kepemilikan')->default('perusahaan');
                $table->string('pengguna')->nullable();
                $table->string('kontak')->nullable();
                $table->string('department')->nullable();
                $table->date('tanggal_signin')->nullable();
                $table->string('team_leader')->nullable();
                $table->string('lokasi')->default('office');
                $table->boolean('hak_bawa_pulang')->default(false);
                $table->string('kondisi')->default('baik');
                $table->string('status')->default('digunakan');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('kondisi');
                $table->index('pengguna');
                $table->index('jenis');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};

