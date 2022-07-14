<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRenameAndAddColumnInvoiceTypeInTblFinanceManualInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        global $queryRun;
        $queryRun =0;
        if (Schema::hasTable('tbl_finance_manual_invoice')) {
            Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {
                Schema::rename('tbl_finance_manual_invoice', 'tbl_finance_invoice');
            });

            Schema::table('tbl_finance_invoice', function (Blueprint $table){
                if(!Schema::hasColumn('tbl_finance_invoice','invoice_type')){
                    global $queryRun;
                    $queryRun =1;
                    $table->unsignedSmallInteger('invoice_type')->default(1)->comment('1 for auto generated invoice,2- for manual generated invoice');
                }
            });

            if(Schema::hasColumn('tbl_finance_invoice','invoice_type') &&  $queryRun==1){
                DB::table('tbl_finance_invoice')->where('invoice_type', 1)->update(array('invoice_type' => 2));  
            } 

            if(Schema::hasColumn('tbl_finance_invoice','invoice_type') &&  $queryRun==1){
                DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_manual_invoice_befor_insert_add_invoiceid`');
                DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_invoice_before_insert_add_invoiceid`');
                DB::unprepared("CREATE TRIGGER `tbl_finance_invoice_before_insert_add_invoiceid` BEFORE INSERT ON `tbl_finance_invoice` FOR EACH ROW
                IF NEW.invoice_id IS NULL or NEW.invoice_id='' THEN
                SET NEW.invoice_id = (SELECT CONCAT('',(COALESCE((SELECT id FROM tbl_finance_invoice ORDER BY id DESC LIMIT 1),0) + 1)+100000));
                END IF;"); 
            }
           
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_finance_invoice')) {
            Schema::table('tbl_finance_invoice', function (Blueprint $table) {
                Schema::rename('tbl_finance_invoice', 'tbl_finance_manual_invoice');
            });
          }
    }
}
