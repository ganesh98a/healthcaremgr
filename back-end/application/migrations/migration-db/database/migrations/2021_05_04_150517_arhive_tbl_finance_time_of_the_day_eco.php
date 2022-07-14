<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ArhiveTblFinanceTimeOfTheDayEco extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # update tbl_finance_time_of_the_day
        DB::statement("UPDATE `tbl_finance_time_of_the_day` SET `archive` = 1 WHERE key_name IN ('transferred', 'eco' ) ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        # update tbl_finance_time_of_the_day
        DB::statement("UPDATE `tbl_finance_time_of_the_day` SET `archive` = 0 WHERE key_name IN ('transferred', 'eco' ) ");
    }
}
