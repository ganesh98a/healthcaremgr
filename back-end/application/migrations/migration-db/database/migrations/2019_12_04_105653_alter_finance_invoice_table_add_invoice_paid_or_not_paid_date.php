<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTableAddInvoicePaidOrNotPaidDate extends Migration
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
                if(!Schema::hasColumn('tbl_finance_invoice','invoice_finalised_date')){
                    $table->dateTime('invoice_finalised_date')->default('0000-00-00 00:00:00')->comment('when status update frome Payment Pending/ to Payment Received or Payment Not Received');
                }
                if(!Schema::hasColumn('tbl_finance_invoice','created')){
                    $table->dateTime('created')->default('0000-00-00 00:00:00')->comment('invoice genrated form hcm to xero');
                }
                if(!Schema::hasColumn('tbl_finance_invoice','updated')){
                    $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
                if(Schema::hasColumn('tbl_finance_invoice','invoice_finalised_date')){
                    $table->dropColumn('invoice_finalised_date');
                }
                if(!Schema::hasColumn('tbl_finance_invoice','created')){
                    $table->dropColumn('created');
                }
                if(!Schema::hasColumn('tbl_finance_invoice','updated')){
                    $table->dropColumn('updated');
                }
            });

        }
    }
}
