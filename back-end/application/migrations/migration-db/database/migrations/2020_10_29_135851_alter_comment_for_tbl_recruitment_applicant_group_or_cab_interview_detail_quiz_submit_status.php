<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommentForTblRecruitmentApplicantGroupOrCabInterviewDetailQuizSubmitStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->unsignedInteger('quiz_submit_status')->default('0')->after('recruitment_task_applicant_id')->comment('0=pending,1=submit,2=failed,3=inprogress')->change();

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
    }
}
