<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceStatementInvoiceAttachTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_statement_attach', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('statement_id')->comment('primary key of tbl_finance_statement');
			$table->unsignedInteger('invoice_id')->comment('primary key of tbl_finance_invoice');
			$table->smallInteger('archive')->default(0)->comment('0 -Not/1 - Archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_statement_attach');
    }
}
