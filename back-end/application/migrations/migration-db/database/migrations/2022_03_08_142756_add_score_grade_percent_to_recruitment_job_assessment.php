<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScoreGradePercentToRecruitmentJobAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            
            $table->unsignedInteger('total_grade')->default(0)->comment('total_grade for the assessment')->after('status');
            $table->unsignedInteger('marks_scored')->default(0)->comment('total marks scored')->after('total_grade');
            $table->unsignedDecimal('percentage')->default(0)->nullable()->comment('percentage achieved')->after('marks_scored');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            $table->dropColumn('total_grade');
            $table->dropColumn('percentage');
            $table->dropColumn('marks_scored');
        });
    }
}
