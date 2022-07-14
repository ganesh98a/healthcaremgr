<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceLineItemForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `tbl_finance_invoice_line_item` DROP FOREIGN KEY `tbl_f_li_li_fk`;
            ALTER TABLE `tbl_finance_invoice_line_item` ADD CONSTRAINT `tbl_f_li_li_fk` FOREIGN KEY (`line_item_id`) 
            REFERENCES `tbl_finance_line_item`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
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
