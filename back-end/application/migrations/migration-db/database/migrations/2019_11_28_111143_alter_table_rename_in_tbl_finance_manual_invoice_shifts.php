<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRenameInTblFinanceManualInvoiceShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_manual_invoice_shifts')) {
            Schema::table('tbl_finance_manual_invoice_shifts', function (Blueprint $table) {
                Schema::rename('tbl_finance_manual_invoice_shifts', 'tbl_finance_invoice_shifts');
            });
            Schema::table('tbl_finance_invoice_shifts', function (Blueprint $table){
                if(Schema::hasColumn('tbl_finance_invoice_shifts','invoice_id')){
                    $table->unsignedInteger('invoice_id')->comment('Autoincrement id of tbl_finance_invoice')->change();
                }
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
        if (Schema::hasTable('tbl_finance_invoice_shifts')) {
            Schema::table('tbl_finance_invoice_shifts', function (Blueprint $table) {
                Schema::rename('tbl_finance_invoice_shifts', 'tbl_finance_manual_invoice_shifts');
            });
          }
    }
}
