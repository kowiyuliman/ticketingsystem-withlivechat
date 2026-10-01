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
        Schema::create('chat_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->enum('category', ['general', 'on_progress', 'pending', 'closed'])->default('general');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'is_active'], 'idx_ct_cat_active');
            $table->index('order_index', 'idx_ct_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_templates');
    }
};
