<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceEnquiryCustomerCatogoryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_enquiry_customer_catogory', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('key', 100)->comment('unique key');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->comment('0 -Not/1- Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_enquiry_customer_catogory');
    }

}
