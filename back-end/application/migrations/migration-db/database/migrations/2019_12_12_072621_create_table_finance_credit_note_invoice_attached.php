<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFinanceCreditNoteInvoiceAttached extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_credit_note_invoice_attached')) {
            Schema::create('tbl_finance_credit_note_invoice_attached', function (Blueprint $table) {
              $table->increments('id');
              $table->string('description',255)->nullable();
              $table->double('amount',10,2)->nullable()->comment('tbl_member auto_increment id ');
              $table->unsignedInteger('invoice_id')->nullable()->comment('tbl_finance_invoice auto_increment id');
              $table->unsignedInteger('credit_note_id')->nullable()->comment('tbl_finance_credit_note auto_increment id');
              $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
              $table->dateTime('created')->default('0000-00-00 00:00:00');
              $table->unsignedSmallInteger('attached_type')->nullable()->comment('1- creidt notes use from invoice, 2- creidt notes applied to invoice id');
              $table->unsignedSmallInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_finance_credit_note_invoice_attached');
    }
}
