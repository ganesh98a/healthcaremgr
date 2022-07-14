<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTableAddColumnXeroInvoiceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (Schema::hasTable('tbl_finance_invoice')) {
            Schema::table('tbl_finance_invoice', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_invoice','xero_invoice_id')){
                    $table->string('xero_invoice_id',255)->nullable()->comment('xero unique invoice Id');
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
        if (Schema::hasTable('tbl_finance_invoice')) {
            Schema::table('tbl_finance_invoice', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_invoice','xero_invoice_id')){
                    $table->dropColumn('xero_invoice_id');
                }
            });

        }
    }
}
