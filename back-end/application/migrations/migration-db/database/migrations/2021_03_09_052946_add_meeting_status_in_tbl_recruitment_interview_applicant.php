<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingStatusInTblRecruitmentInterviewApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_interview_applicant')) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_interview_applicant', 'interview_meeting_status')) {
                    $table->unsignedInteger('interview_meeting_status')->default(0)->after('job_id')->comment('1 -Successful/ 2 -Unsuccessful');
                }
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
        if (Schema::hasTable('tbl_recruitment_interview_applicant')) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {               
                if (Schema::hasColumn('tbl_recruitment_interview_applicant', 'interview_meeting_status')) {
                    $table->dropColumn('interview_meeting_status');
                }
            });
        }
    }
}
