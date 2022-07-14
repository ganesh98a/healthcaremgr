<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceNoTriggerOnInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_finance_invoice')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_invoice_invoice_no_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_finance_invoice_invoice_no_after_id` BEFORE INSERT ON `tbl_finance_invoice` FOR EACH ROW
                IF NEW.invoice_no IS NULL or NEW.invoice_no=''  THEN 
                SET NEW.invoice_no=  (SELECT CONCAT('INV',(select LPAD(d.autoid_data,9,0)  from (select sum(Coalesce((SELECT id FROM tbl_finance_invoice ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_finance_invoice')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_invoice_invoice_no_after_id`');
        }

    }
   
}