<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceLineItemAppliedStateTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_line_item_applied_state', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('line_itemId')->comment('priamry key tbl_finance_line_item');
            $table->unsignedInteger('stateId')->comment('priamry key tbl_state');
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
        Schema::dropIfExists('tbl_finance_line_item_applied_state');
    }

}
