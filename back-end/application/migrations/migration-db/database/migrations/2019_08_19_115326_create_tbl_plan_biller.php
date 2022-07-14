<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPlanBiller extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_plan_biller', function (Blueprint $table) {
            $table->increments('id');
            $table->string('biller_code', 200);
            $table->string('biller_short_name', 200);
            $table->string('biller_long_name', 200);
            $table->string('biller_reference_number', 200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_plan_biller');
    }
}
