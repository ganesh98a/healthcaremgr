<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblInvoiceAddressedTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_invoice_addressed_to', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoiceId')->comment('primary key of tbl_finance_invoice');
            $table->unsignedInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7-house');
            $table->string('firstname',50);
            $table->string('lastname',50);
            $table->string('email',64);
            $table->string('phone',20);
            $table->string('complete_address',100);
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->default('0')->comment('0=Not/1=Archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_invoice_addressed_to');
    }
}
