<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceInvoiceDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_invoice_docs')) {
        Schema::create('tbl_finance_invoice_docs', function(Blueprint $table)
          {
            $table->increments('id');
            $table->string('html_url', 32);
            $table->string('pdf_url', 200);
            $table->integer('invoice_id');
            $table->integer('status')->comment('1- Active, 0- Inactive');
            $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_finance_invoice_docs');
    }
}
