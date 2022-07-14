<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceQuoteManualItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_quote_manual_item', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_name');
            $table->unsignedInteger('description');
            $table->double('cost', 14, 2);
            $table->unsignedInteger('charge_by');
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
        Schema::dropIfExists('tbl_finance_quote_manual_item');
    }

}
