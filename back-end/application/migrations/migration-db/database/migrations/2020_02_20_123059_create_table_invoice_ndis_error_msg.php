<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInvoiceNdisErrorMsg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_invoice_ndis_error_message', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id')->nullable()->comment('tbl_finance_invoice autoincremnet id');
            $table->string('payment_request_number',100)->nullable()->comment('genrate random string when pending invoice csv export');
            $table->text('error_msg')->nullable()->comment('error msg column value save when import csv');
            $table->text('cancellation_reason')->nullable()->comment('error msg column value save when import csv');
            $table->string('import_batch',100)->nullable()->comment('group of batch number for linking import with tbl_finance_invoice_ndis_status_import_log');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0- not/1-archive');
            $table->dateTime('created')->default('0000-00-00 00:00:00')->comment('when csv import');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_invoice_ndis_error_message');
    }
}
