<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentDiagnosisNullablePlanEndDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            $table->date('plan_end_date')->nullable()->change();
           });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
		
		Schema::table('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            //
        });
    }
}
