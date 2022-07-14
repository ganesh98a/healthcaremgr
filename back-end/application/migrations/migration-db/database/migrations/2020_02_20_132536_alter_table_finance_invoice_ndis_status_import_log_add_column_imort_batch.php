<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFinanceInvoiceNdisStatusImportLogAddColumnImortBatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_finance_invoice_ndis_status_import_log')){
            Schema::table('tbl_finance_invoice_ndis_status_import_log', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_invoice_ndis_status_import_log','import_batch')){
                    $table->string('import_batch',100)->nullable()->comment('group of batch number for linking import with tbl_finance_invoice_ndis_status_import_log')->after('file_title');
                }
                //
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
        if(Schema::hasTable('tbl_finance_invoice_ndis_status_import_log')){
            Schema::table('tbl_finance_invoice_ndis_status_import_log', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_invoice_ndis_status_import_log','import_batch')){
                    $table->dropColumn('import_batch');
                }
            });
        }
    }
}
