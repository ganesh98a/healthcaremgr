<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'scheduled_duration')) {
                $table->string('scheduled_duration')->nullable()->after("scheduled_end_datetime");
            }
            if (!Schema::hasColumn('tbl_shift', 'actual_duration')) {
                $table->string('actual_duration')->nullable()->after("actual_end_datetime");
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'scheduled_duration')) {
                $table->dropColumn('scheduled_duration');
            }
            if (Schema::hasColumn('tbl_shift', 'actual_duration')) {
                $table->dropColumn('actual_duration');
            }
        });
    }
}
