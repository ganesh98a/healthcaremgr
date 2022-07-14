<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRosterShiftTimeCategory extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_roster_shift_time_category')) {
            Schema::create('tbl_roster_shift_time_category', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('roster_shiftId')->comment('primary key tbl_roster_shift');
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
        Schema::dropIfExists('tbl_roster_shift_time_category');
    }

}
