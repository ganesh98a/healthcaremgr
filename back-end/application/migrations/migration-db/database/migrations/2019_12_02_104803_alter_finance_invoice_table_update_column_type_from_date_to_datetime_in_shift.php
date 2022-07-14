<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTableUpdateColumnTypeFromDateToDatetimeInShift extends Migration
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
                if(Schema::hasColumn('tbl_finance_invoice','invoice_shift_start_date')){
                    $table->dateTime('invoice_shift_start_date')->default('0000-00-00 00:00:00')->comment('if invoice_type 1 then tbl_shift start_time store on this column otherwise manual date inserted with 00:00:00')->change();
                }
                if(Schema::hasColumn('tbl_finance_invoice','invoice_shift_end_date')){
                    $table->dateTime('invoice_shift_end_date')->default('0000-00-00 00:00:00')->comment('if invoice_type 1 then tbl_shift end_time store on this column otherwise manual date inserted with 00:00:00')->change();
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
                if(Schema::hasColumn('tbl_finance_invoice','invoice_shift_start_date')){
                    $table->date('invoice_shift_start_date')->default('0000-00-00')->comment('')->change();
                }
                if(Schema::hasColumn('tbl_finance_invoice','invoice_shift_end_date')){
                    $table->date('invoice_shift_end_date')->default('0000-00-00')->comment('')->change();
                }

            });

        }
    }
}
