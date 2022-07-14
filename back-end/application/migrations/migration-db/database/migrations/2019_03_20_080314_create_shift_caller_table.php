<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftCallerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_caller')) {
            Schema::create('tbl_shift_caller', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId')->index();
                $table->unsignedInteger('booker_id');
                $table->unsignedInteger('booking_method');
                $table->string('firstname',32);
                $table->string('lastname',32);
                $table->string('email',64);
                $table->string('phone',20);
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
        Schema::dropIfExists('tbl_shift_caller');
    }
}
