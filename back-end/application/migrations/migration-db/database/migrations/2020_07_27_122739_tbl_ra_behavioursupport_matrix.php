<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblRaBehavioursupportMatrix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !  Schema::hasTable('tbl_ra_behavioursupport_matrix')) {
            Schema::create('tbl_ra_behavioursupport_matrix', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('risk_assessment_id')->comment('tbl_crm_risk_assessment.id');
                $table->string('behaviuor', 255);
                $table->unsignedInteger('likelyhood_id');
                $table->string('trigger', 255);
                $table->string('prevention_strategy', 255);
                $table->string('descalation_strategy', 255);
                $table->foreign('risk_assessment_id')->references('id')->on('tbl_crm_risk_assessment')->onDelete('cascade');
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_member.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('SET NULL');
                $table->timestamp('created')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_ra_behavioursupport_matrix');
    }
}
