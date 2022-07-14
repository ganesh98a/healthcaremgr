<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceInvoiceItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_finance_invoice_item')) {
        Schema::create('tbl_finance_invoice_item', function(Blueprint $table)
        {
          $table->increments('id');
          $table->integer('invoice_id');
          $table->integer('product_code');
          $table->string('description');
          $table->integer('quantity');
          $table->decimal('unit_price', 9);
          $table->decimal('gst', 9);
          $table->decimal('total_incl_taxes', 9);
          $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
       Schema::drop('tbl_finance_invoice_item');
    }
}
