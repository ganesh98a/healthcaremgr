<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceLineItemUpdateLineIdNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            DB::unprepared("UPDATE tbl_finance_invoice_line_item SET line_item_id = NULL 
            WHERE line_item_id NOT IN (SELECT id FROM tbl_finance_line_item)");
        });
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
