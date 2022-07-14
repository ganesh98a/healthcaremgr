<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnQuizSubmitStatusRecruitmentApplicantGroupOrCabInterviewDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        if (Schema::hasTable('tbl_recruitment_applicant_group_or_cab_interview_detail')) {
            Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {                
                $table->unsignedInteger('quiz_submit_status')->default('0')->after('recruitment_task_applicant_id')->comment('0=pending,1=submit,2=failed');
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
        if (Schema::hasTable('tbl_recruitment_applicant_group_or_cab_interview_detail') && Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'quiz_submit_status')) {
            Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
               $table->dropColumn('quiz_submit_status');
            });
        }
    }
}
