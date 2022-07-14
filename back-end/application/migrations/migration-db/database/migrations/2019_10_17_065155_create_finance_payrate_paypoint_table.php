<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePayratePaypointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_payrate_paypoint', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payrate_id');
            $table->float('hourly_rate',10,2);
            $table->float('increased_by',10,2);
            $table->float('dollar_value',10,2);
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_payrate_paypoint');
    }
}
