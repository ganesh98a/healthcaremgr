<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceLineItemsAddColumnMeasureBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_manual_invoice_line_items', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_finance_manual_invoice_line_items','measure_by')){
               $table->unsignedInteger('measure_by')->default(0)->nullable()->comment("priamry key tbl_finance_measure")->after("funding_type");
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
        Schema::table('tbl_finance_manual_invoice_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_manual_invoice_line_items', 'measure_by')) {
                $table->dropColumn('measure_by');
            }
        });
    }
}
