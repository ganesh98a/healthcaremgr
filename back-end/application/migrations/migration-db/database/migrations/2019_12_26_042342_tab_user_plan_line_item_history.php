<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TabUserPlanLineItemHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_user_plan_line_item_history')) {
            Schema::create('tbl_user_plan_line_item_history', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_plan_line_items_id')->nullable()->comment('auto increment id tbl_user_plan_line_items');
                $table->double('line_item_fund_used', 10, 2)->default('0.00')->comment('total amount including gst');
                $table->unsignedSmallInteger('line_item_fund_used_type')->comment('1 - for shift line item ,2 for manual invoice line item,3 -for manual miscellaneous item');
                $table->unsignedInteger('line_item_use_id')->comment('auto increment id of tbl_finance_manual_invoice_miscellaneous_items,tbl_finance_manual_invoice_line_items,tbl_shift_line_item_attached. id depend on  line_item_fund_used_type column');
                $table->unsignedSmallInteger('status')->default('0')->comment('0- for fund block,1-for used,2-for relased/cancelled');
                $table->unsignedSmallInteger('archive')->default('0')->comment('1- Yes, 0- No');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_user_plan_line_item_history');
    }
}
