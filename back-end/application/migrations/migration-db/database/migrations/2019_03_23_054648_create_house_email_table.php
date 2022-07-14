<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_email')) {
            Schema::create('tbl_house_email', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->string('email', 64);
                    $table->unsignedTinyInteger('primary_email')->comment('1- Primary, 2- Secondary');
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
        Schema::dropIfExists('tbl_house_email');
    }
}
