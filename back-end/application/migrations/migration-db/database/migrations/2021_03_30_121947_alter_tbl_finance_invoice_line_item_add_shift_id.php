<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceLineItemAddShiftId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice_line_item', 'shift_id')) {
                $table->unsignedInteger('shift_id')->nullable()->after("category_id")->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
        });
    }
}
