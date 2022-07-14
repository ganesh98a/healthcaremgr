<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvitedOnInTblRecruitmentInterviewApplicant extends Migration
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
                if (!Schema::hasColumn('tbl_recruitment_interview_applicant', 'invited_on')) {
                    $table->dateTime('invited_on')->nullable()->after('interview_meeting_status')->comment('email sent date');
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
                if (Schema::hasColumn('tbl_recruitment_interview_applicant', 'invited_on')) {
                    $table->dropColumn('invited_on');
                }
            });
        }
    }
}
