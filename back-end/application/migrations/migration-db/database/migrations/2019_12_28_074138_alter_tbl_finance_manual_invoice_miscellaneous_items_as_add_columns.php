<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceMiscellaneousItemsAsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_manual_invoice_miscellaneous_items', function (Blueprint $table) {
          $table->double('gst',10,2)->unsigned()->after('item_cost');
          $table->double('total',10,2)->unsigned()->comment('item_cost + gst')->after('gst');
          $table->unsignedSmallInteger('plan_line_itemId')->unsigned()->comment('primary key tbl_user_plan_line_item')->after('total');
          
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_manual_invoice_miscellaneous_items', function (Blueprint $table) {
            $table->dropColumn('gst');
            $table->dropColumn('total');
            $table->dropColumn('plan_line_itemId');
        });
    }
}
