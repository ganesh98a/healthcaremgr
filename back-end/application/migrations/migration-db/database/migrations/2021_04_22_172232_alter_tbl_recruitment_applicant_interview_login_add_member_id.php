<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantInterviewLoginAddMemberId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_interview_login', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_interview_login', 'member_id')) {
                $table->unsignedInteger('member_id')->default(0)->nullable()->after('applicant_id')->comment('logged in only member');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_interview_login', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_interview_login', 'member_id')) {
                $table->dropColumn('member_id');
            }
        });
    }
}
