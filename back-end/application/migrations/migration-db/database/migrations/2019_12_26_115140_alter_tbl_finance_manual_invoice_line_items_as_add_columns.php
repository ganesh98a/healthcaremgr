<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceLineItemsAsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_manual_invoice_line_items', function (Blueprint $table) {
          $table->double('sub_total',10,2)->unsigned()->comment('exclude gst')->after('cost');
          $table->double('gst',10,2)->unsigned()->after('sub_total');
          $table->double('total',10,2)->unsigned()->comment('total + gst')->after('gst');
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
        Schema::table('tbl_finance_manual_invoice_line_items', function (Blueprint $table) {
            //if (Schema::hasColumn('tbl_finance_manual_invoice_line_items', 'sub_total')) {
                $table->dropColumn('sub_total');
           // }

           // if (Schema::hasColumn('tbl_finance_manual_invoice_line_items', 'gst')) {
                $table->dropColumn('gst');
           // }

           // if (Schema::hasColumn('tbl_finance_manual_invoice_line_items', 'total')) {
                $table->dropColumn('total');
            //}

            //if (Schema::hasColumn('tbl_finance_manual_invoice_line_items', 'plan_line_itemId')) {
                $table->dropColumn('plan_line_itemId');
            //}
        });
    }


}
