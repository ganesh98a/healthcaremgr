<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTableAddInvoiceFilePath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table){
            if(!Schema::hasColumn('tbl_finance_invoice','invoice_file_path')){
                $table->string('invoice_file_path',255)->nullable()->comment('location genrate invoice file');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_finance_invoice','invoice_file_path')){
                $table->dropColumn('invoice_file_path');
            }
        });
    }
}
