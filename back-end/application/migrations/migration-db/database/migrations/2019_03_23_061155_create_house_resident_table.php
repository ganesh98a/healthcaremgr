<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseResidentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_resident')) {
            Schema::create('tbl_house_resident', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->string('name', 64);
                    $table->string('phone', 20);
                    $table->string('email', 64);
                    $table->string('dob', 15);
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_house_resident');
    }
}
