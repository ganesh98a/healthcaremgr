<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceQuoteItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_quote_item', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_type')->comment('1 - Line Item/ 2 - Manual item');
            $table->unsignedInteger('itemId')->comment('primary key tbl_finance_quote_manaual_item| tbl_finance_line_item');

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
        Schema::dropIfExists('tbl_finance_quote_item');
    }

}
