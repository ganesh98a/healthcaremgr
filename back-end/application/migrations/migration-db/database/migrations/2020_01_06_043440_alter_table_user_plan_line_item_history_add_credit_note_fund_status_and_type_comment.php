<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserPlanLineItemHistoryAddCreditNoteFundStatusAndTypeComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_user_plan_line_item_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'status')) {
                $table->unsignedSmallInteger('status')->defualt(0)->comment('0- for fund block,1-for used,2-for relased/cancelled,3- for add credit notes fund on other type line item')->change();
            }
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'line_item_fund_used_type')) {
                $table->unsignedSmallInteger('line_item_fund_used_type')->nullable()->comment('1 - for shift line item ,2 for manual invoice line item,3 -for manual miscellaneous item,4-used for plan managment,5-credit notes amount added on fund')->change();
            }
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'line_item_use_id')) {
                $table->unsignedInteger('line_item_use_id')->nullable()->comment('auto increment id of tbl_finance_manual_invoice_miscellaneous_items,tbl_finance_manual_invoice_line_items,tbl_shift_line_item_attached,tbl_finance_credit_note. id depend on  line_item_fund_used_type column')->change();
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
