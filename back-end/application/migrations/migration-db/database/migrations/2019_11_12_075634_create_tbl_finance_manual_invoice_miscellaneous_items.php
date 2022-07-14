<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceManualInvoiceMiscellaneousItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_manual_invoice_miscellaneous_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id')->comment('Autoincrement id of tbl_finance_manual_invoice');
            $table->string('item_name', 150);
            $table->text('item_description');
            $table->float('item_cost',10,2);
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_finance_manual_invoice_miscellaneous_items');
    }
}
