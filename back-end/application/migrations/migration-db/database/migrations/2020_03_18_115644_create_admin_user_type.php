<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUserType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_admin_user_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('key_name', 150);
            $table->unsignedSmallInteger('archive')->comment('0 - No/ 1 - Yes');
        });


        $seeder = new AdminUserType();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_admin_user_type');
    }

}
