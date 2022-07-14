<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicHolidayTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropIfExists('tbl_participant_plan_breakdown');
        if (!Schema::hasTable('tbl_public_holiday')) {
            Schema::create('tbl_public_holiday', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('stateId');
                $table->date('holiday_date')->default('0000-00-00');
                $table->text('title');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_public_holiday');
    }

}
