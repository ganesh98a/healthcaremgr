<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceManualInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_manual_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('invoice_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('pay_by')->default('0000-00-00 00:00:00');
            $table->dateTime('invoice_shift_start_date')->default('0000-00-00 00:00:00');
            $table->dateTime('invoice_shift_end_date')->default('0000-00-00 00:00:00');
            $table->text('invoice_shift_notes')->nullable();
            $table->text('line_item_notes')->nullable();
            $table->text('manual_invoice_notes')->nullable();
            $table->unsignedSmallInteger('status')->comment('0 - created/ 1 - Yes');
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_manual_invoice');
    }
}
