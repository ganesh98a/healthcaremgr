<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityChangeSupportPlanNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            $table->string('need_support_plan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            $table->string('need_support_plan')->nullable(false)->change();
        });
    }
}
