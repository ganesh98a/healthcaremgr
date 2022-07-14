<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceTimeOfTheDayTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_time_of_the_day', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->unsignedInteger('order')->comment('Order to show');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_time_of_the_day');
    }

}
