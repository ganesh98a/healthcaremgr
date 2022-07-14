<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedKeypayAuthDetails extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $seeder = new KeyPayDetailsSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        
    }

}
