<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblUserPlanLineItemHistoryUpdateDataTypeValueForFundUsed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('tbl_user_plan_line_item_history', 'shiftId')) {
            DB::unprepared("ALTER TABLE `tbl_user_plan_line_item_history` CHANGE `line_item_fund_used` `line_item_fund_used` double(10,2) NOT NULL DEFAULT '0' COMMENT 'total amount including gst' AFTER `user_plan_line_items_id`;");
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
