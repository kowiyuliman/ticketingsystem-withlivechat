<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->enum('type', ['laptop', 'charger', 'mouse', 'lan_adapter', 'headset', 'usb_audio']);
            $table->string('brand')->nullable(); // HP, Dell, Lenovo untuk laptop
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('condition', ['good', 'repair', 'damaged'])->default('good');
            $table->enum('status', ['available', 'assigned', 'on_loan', 'in_repair', 'damaged'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
