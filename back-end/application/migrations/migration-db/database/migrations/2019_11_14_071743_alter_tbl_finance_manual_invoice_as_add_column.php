<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceAsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {
         $table->date('invoice_shift_start_date')->default('0000-00-00')->change();
         $table->date('invoice_shift_end_date')->default('0000-00-00')->change();
         $table->date('invoice_date')->default('0000-00-00')->change();
         $table->date('pay_by')->default('0000-00-00')->change();

         $table->float('sub_total',10,2)->comment('Ex GST')->after('status');
         $table->float('gst',10,2)->after('status');
         $table->float('total',10,2)->comment('Incl GST')->after('status');
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
            if (Schema::hasColumn('tbl_finance_manual_invoice', 'sub_total')) {
                $table->dropColumn('sub_total');
            }

            if (Schema::hasColumn('tbl_finance_manual_invoice', 'gst')) {
                $table->dropColumn('gst');
            }

            if (Schema::hasColumn('tbl_finance_manual_invoice', 'total')) {
                $table->dropColumn('total');
            } 
        });
    }
}
