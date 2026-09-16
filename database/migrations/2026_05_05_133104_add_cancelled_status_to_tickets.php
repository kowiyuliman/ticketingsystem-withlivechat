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
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE tickets 
                MODIFY status ENUM('open','on_progress','pending','closed','cancelled') 
                NOT NULL DEFAULT 'open'");
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE tickets 
                MODIFY status ENUM('open','on_progress','pending','closed') 
                NOT NULL DEFAULT 'open'");
        }
    }
};
