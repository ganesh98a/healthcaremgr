<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinancePayrateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_payrate', function (Blueprint $table) {
            $table->increments('id');            
            $table->unsignedInteger('category')->comment('Primary key of "tbl_finance_payrate_category" table.');
            $table->unsignedInteger('type')->comment('Primary key of "tbl_finance_payrate_type"');
            $table->unsignedInteger('level_number');
            $table->unsignedInteger('paypoint');
            $table->string('name',200);
            $table->timestamp('start_date')->default('0000-00-00 00:00:00');
            $table->timestamp('end_date')->default('0000-00-00 00:00:00');
            $table->unsignedTinyInteger('status')->default('0')->comment('1- approved, 0- Default');
            $table->text('comments');
            $table->unsignedTinyInteger('archieve')->comment('1- archieve');
            $table->timestamp('created')->default('0000-00-00 00:00:00');
            $table->dateTime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_payrate');
    }
}
