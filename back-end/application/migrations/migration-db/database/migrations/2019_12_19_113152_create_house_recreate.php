<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseRecreate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropIfExists('tbl_house');

        if (!Schema::hasTable('tbl_house')) {
            Schema::create('tbl_house', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name', 200);

                $table->string('street', 128);
                $table->string('suburb', 128);
                $table->string('postal', 10);
                $table->unsignedInteger('state');

                $table->string('logo_file', 255);

                $table->unsignedTinyInteger('status')->comment("1- Yes, 0- No");
                $table->unsignedTinyInteger('enable_portal_access')->comment("1- Yes, 0- No");;
                $table->dateTime('created');
                $table->dateTime('updated');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_house');
    }

}
