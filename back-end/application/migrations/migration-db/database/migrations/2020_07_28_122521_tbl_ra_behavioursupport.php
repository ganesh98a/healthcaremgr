<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblRaBehavioursupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

   

    public function up()
    {
        if ( !  Schema::hasTable('tbl_ra_behavioursupport')) {
            Schema::create('tbl_ra_behavioursupport', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('risk_assessment_id')->comment('tbl_crm_risk_assessment.id');
            $table->foreign('risk_assessment_id')->references('id')->on('tbl_crm_risk_assessment')->onDelete('CASCADE');
            $table->unsignedSmallInteger('bs_not_applicable')->comment("1- Not applicable, 2- applicable")->nullable();
            $table->unsignedSmallInteger('bs_plan_status')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('seclusion')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('chemical_constraint')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('mechanical_constraint')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('physical_constraint')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('environmental')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('bs_noplan_status')->comment("1- No, 2- Yes")->nullable();
            $table->string('bs_plan_available_date',10)->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('updated_date')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');            
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
        Schema::dropIfExists('tbl_ra_behavioursupport');
    }
}
