<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftTimeOfTheDay extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_shift_time_category')) {
            Schema::create('tbl_shift_time_category', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId')->comment('primary key tbl_shift');
                $table->unsignedInteger('timeId')->comment('primary key tbl_finance_time_of_the_day');
                $table->dateTime('created');
                $table->unsignedTinyInteger('archive')->comment('0- Not/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_shift_time_of_the_day');
    }

}
