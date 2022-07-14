<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftScheduledTravelDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift','scheduled_travel_duration')) {
                $table->string('scheduled_travel_duration', 255)->nullable()->comment('HH:MM')->change();
            }
            if (Schema::hasColumn('tbl_shift','actual_travel_duration')) {
                $table->string('actual_travel_duration', 255)->nullable()->comment('HH:MM')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
