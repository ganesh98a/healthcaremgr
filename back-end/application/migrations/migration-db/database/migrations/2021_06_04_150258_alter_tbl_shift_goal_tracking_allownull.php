<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftGoalTrackingAllownull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_goal_tracking', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_goal_tracking', 'snapshot')) {
                $table->text('snapshot')->nullable()->change();
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
        Schema::table('tbl_shift_goal_tracking', function (Blueprint $table) {
            //
        });
    }
}
