<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblFinanceLineItemAppliedDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item_applied_days', function (Blueprint $table) {
            # update tbl_finance_time_of_the_day for week day
            DB::statement("UPDATE `tbl_finance_line_item_applied_days` SET `week_dayId` = 1 WHERE week_dayId_old IN ('1', '2', '3', '4', '5') ");
            # update tbl_finance_time_of_the_day for staturday
            DB::statement("UPDATE `tbl_finance_line_item_applied_days` SET `week_dayId` = 2 WHERE week_dayId_old = '6' ");
            # update tbl_finance_time_of_the_day for sunday
            DB::statement("UPDATE `tbl_finance_line_item_applied_days` SET `week_dayId` = 3 WHERE week_dayId_old = '7' ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_line_item_applied_days', function (Blueprint $table) {
            //
        });
    }
}
