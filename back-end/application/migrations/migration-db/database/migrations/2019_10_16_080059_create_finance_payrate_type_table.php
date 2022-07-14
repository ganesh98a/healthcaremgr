<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePayrateTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_payrate_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',150);
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_payrate_type');
    }
}
