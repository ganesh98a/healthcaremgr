<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceQuoteTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_quote', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_type')->comment('1 - Participant/ 2 - Organization / 3 - Other(new customer)');
            $table->unsignedInteger('userId')->comment('priamry key tbl_participant, tbl_organisation and tbl_finance_quote_enquery_customer');
            $table->datetime('quote_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('valid_until')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('quote_note');
            $table->unsignedSmallInteger('status')->comment('1 - Sent,2 - Sent & Read,3 - Accepted,4 - Not Accepted,5 - Draft,6 - Error Sending,8 - Archived');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->comment('0 -Not/1- Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_quote');
    }

}
