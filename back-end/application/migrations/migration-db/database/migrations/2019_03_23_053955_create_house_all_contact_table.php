<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseAllContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_all_contact')) {
            Schema::create('tbl_house_all_contact', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->string('name', 64);
                    $table->string('phone', 20);
                    $table->string('email', 64);
                    $table->string('position', 20);
                    $table->unsignedTinyInteger('type')->index('type')->comment('1- Support Coordinator, 2- Member, 3- Key Contact');
                    $table->unsignedTinyInteger('archive')->default(0)->comment('1- Yes, 0- No');
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
        Schema::dropIfExists('tbl_house_all_contact');
    }
}
