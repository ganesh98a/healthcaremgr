<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePayrollKeypayGraphDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_payroll_keypay_graph_data')) {
            Schema::create('tbl_finance_payroll_keypay_graph_data', function (Blueprint $table) {
                $table->increments('id');
                $table->date('month_date')->nullable()->default('0000-00-00')->comment('Month wise data');
                $table->string('total_wages',255)->nullable()->comment('keypay total_wages');
                $table->string('total_expenses',255)->nullable()->comment('keypay total_expenses');
                $table->string('total_super',255)->nullable()->comment('keypay total_super');
                $table->string('total_gross',255)->nullable()->comment('keypay total_grosswithsuper');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('archive')->default('0');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_payroll_keypay_graph_data');
    }
}
