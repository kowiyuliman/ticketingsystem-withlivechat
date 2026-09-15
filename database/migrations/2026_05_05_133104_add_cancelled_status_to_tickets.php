<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets 
                MODIFY status ENUM('open','on_progress','pending','closed','cancelled') 
                NOT NULL DEFAULT 'open'");
        }
    }

    public function down()
    {
        DB::statement("ALTER TABLE tickets 
            MODIFY status ENUM('open','on_progress','pending','closed') 
            NOT NULL DEFAULT 'open'");
    }
};
