<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSeedDataTblFinanceAddtitonalPaypointRatetype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_finance_addtitonal_paypoint_ratetype')) {
            DB::table('tbl_finance_addtitonal_paypoint_ratetype')->truncate();
            $seeder = new FinanceAdditionalPaypointRateType();
            $seeder->run();
            DB::table('tbl_finance_payrate_paypoint')->where('archive', 0)->where('rate_type','>',5)->update(array('archive' => 1));
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
