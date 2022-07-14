<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('tbl_finance_invoice')) {
      			Schema::create('tbl_finance_invoice', function (Blueprint $table) {
              $table->increments('id');
        			$table->string('participant_email', 35);
        			$table->integer('participant_id');
        			$table->string('invoice_number', 30);
        			$table->date('invoice_date');
        			$table->date('due_date');
        			$table->string('company_name', 35);
        			$table->string('account_number', 32);
        			$table->float('invoice_amount', 20);
        			$table->integer('biller_code');
        			$table->string('reference_no', 32);
        			$table->string('po_number', 32);
        			$table->float('gst_value', 20);
        			$table->boolean('status')->default(4)->comment('1.paid, 2.duplicate, 3.follow up, 4. queue');
        			$table->integer('read_status')->default(0)->comment('1- Read, 0- Unread');
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
        Schema::dropIfExists('tbl_finance_invoice');
    }
}
