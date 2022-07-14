<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceLineItemAppliedTimeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_line_item_applied_time', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('line_itemId')->comment('priamry key tbl_finance_line_item');
            $table->unsignedInteger('finance_timeId')->comment('priamry key tbl_finance_time_of_the_day');
            $table->unsignedSmallInteger('archive')->comment('0 -Not/1- Yes');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_line_item_applied_time');
    }

}
