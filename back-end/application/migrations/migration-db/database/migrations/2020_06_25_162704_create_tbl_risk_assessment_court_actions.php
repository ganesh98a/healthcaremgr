<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRiskAssessmentCourtActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_crm_risk_assessment_court_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('risk_assessment_id')->comment('tbl_crm_risk_assessment.id');
            $table->foreign('risk_assessment_id')->references('id')->on('tbl_crm_risk_assessment')->onDelete('CASCADE');
            $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- applicable")->nullable();
            $table->unsignedSmallInteger('inter_order')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('com_ser_order')->comment("1- No, 2- Yes")->nullable();
            $table->unsignedSmallInteger('com_cor_order')->comment("1- No, 2- Yes")->nullable();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('updated_date')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_crm_risk_assessment_court_actions')) {
            Schema::table('tbl_crm_risk_assessment_court_actions', function (Blueprint $table) {
                // Check the field is exist.
                if (Schema::hasColumn('tbl_crm_risk_assessment_court_actions', 'risk_assessment_id')) {
                    // Drop foreign key
                    $table->dropForeign(['risk_assessment_id']);
                }
                // Check the field is exist.
                if (Schema::hasColumn('tbl_crm_risk_assessment_court_actions', 'created_by')) {
                    // Drop foreign key
                    $table->dropForeign(['created_by']);
                }
                // Check the field is exist.
                if (Schema::hasColumn('tbl_crm_risk_assessment_court_actions', 'updated_by')) {
                    // Drop foreign key
                    $table->dropForeign(['updated_by']);
                }
            });
        }
        Schema::dropIfExists('tbl_crm_risk_assessment_court_actions');
    }
}
