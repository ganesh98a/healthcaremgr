<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceAsAltertrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_manual_invoice_befor_insert_add_invoiceid`');
            DB::unprepared("CREATE TRIGGER `tbl_finance_manual_invoice_befor_insert_add_invoiceid` BEFORE INSERT ON `tbl_finance_manual_invoice` FOR EACH ROW
                IF NEW.invoice_id IS NULL or NEW.invoice_id='' THEN
                SET NEW.invoice_id = (SELECT CONCAT('',(COALESCE((SELECT id FROM tbl_finance_manual_invoice ORDER BY id DESC LIMIT 1),0) + 1)+100000));
                END IF;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_manual_invoice_befor_insert_add_invoiceid`');
        });
    }
}
