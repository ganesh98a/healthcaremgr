<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceQuoteEnqueryCustomerTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_quote_enquiry_customer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->unsignedInteger('customerCategoryId')->comment('priamry key tbl_finance_enquiry_customer_catogory');
            $table->string('contact_name', 150);
            $table->string('company_name', 200);
            $table->string('email', 150);

            $table->string('primary_phone', 20);
            $table->string('seconday_phone', 20);

            $table->string('street', 200);
            $table->string('suburb', 100);
            $table->unsignedInteger('state')->comment('primary key of tbl_state');
            $table->string('poscode', 10);

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
        Schema::dropIfExists('tbl_finance_quote_enquery_customer');
    }

}
