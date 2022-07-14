<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHousePhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_phone')) {
            Schema::create('tbl_house_phone', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->string('phone', 20);
                    $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary');
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
        Schema::dropIfExists('tbl_house_phone');
    }
}
