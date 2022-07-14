<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRiskAssessmentRiskMatrixAndRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !  Schema::hasTable('tbl_crm_risk_assessment_risk_multiplier')) {
            Schema::create('tbl_crm_risk_assessment_risk_multiplier', function(Blueprint $table) {
                $table->increments('id');
                $table->string('type', 255)->comment('Type of factor');
                $table->string('name', 255)->comment('Name of multiplier/factor (eg. Likely, Almost certain, etc)');
                $table->unsignedSmallInteger('multiplier')->comment('Multiplier/factor');
            });
        }

        if ( !  Schema::hasTable('tbl_crm_risk_assessment_risk_matrix')) {
            Schema::create('tbl_crm_risk_assessment_risk_matrix', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('risk_assessment_id')->comment('tbl_crm_risk_assessment.id');
                $table->string('risk', 255);
                $table->unsignedInteger('impact_id')->comment('tbl_crm_risk_assessment_risk_multiplier.id');
                $table->unsignedInteger('probability_id')->comment('tbl_crm_risk_assessment_risk_multiplier.id');

                $table->boolean('archive')->default(0);

                $table->foreign('risk_assessment_id')->references('id')->on('tbl_crm_risk_assessment')->onDelete('cascade');
                $table->foreign('impact_id')->references('id')->on('tbl_crm_risk_assessment_risk_multiplier');
                $table->foreign('probability_id')->references('id')->on('tbl_crm_risk_assessment_risk_multiplier');

                // score is a derived value

                // blamable cols and timestamps
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_member.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('SET NULL');

                $table->timestamp('created')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            });
        }

        $seeder = new CrmRiskMatrixMultiplierSeeder();
        $seeder->run();
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_crm_risk_assessment_risk_matrix');
        Schema::dropIfExists('tbl_crm_risk_assessment_risk_multiplier');
    }
}
