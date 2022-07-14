<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCostCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_cost_code')) {
            Schema::create('tbl_finance_cost_code', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users'); 
            });
            $seeder = new CostCode();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_finance_cost_code')) {
            Schema::dropIfExists('tbl_finance_cost_code');
        }
    }
}
