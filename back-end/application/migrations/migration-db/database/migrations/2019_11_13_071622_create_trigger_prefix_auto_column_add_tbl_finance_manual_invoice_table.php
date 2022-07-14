<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerPrefixAutoColumnAddTblFinanceManualInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_manual_invoice_befor_insert_add_invoiceid`');
        DB::unprepared("CREATE TRIGGER `tbl_finance_manual_invoice_befor_insert_add_invoiceid` BEFORE INSERT ON `tbl_finance_manual_invoice` FOR EACH ROW
        IF NEW.invoice_id IS NULL or NEW.invoice_id='' THEN
          SET NEW.invoice_id = (SELECT CONCAT('X_',((SELECT id FROM tbl_finance_manual_invoice ORDER BY id DESC LIMIT 1) + 1)+10000));
          END IF;"); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_manual_invoice_befor_insert_add_invoiceid`');
    }
}
