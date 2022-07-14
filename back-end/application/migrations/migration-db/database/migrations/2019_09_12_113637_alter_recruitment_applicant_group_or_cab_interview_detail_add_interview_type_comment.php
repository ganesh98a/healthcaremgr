<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailAddInterviewTypeComment extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'interview_type')) {
                $table->unsignedInteger('interview_type')->comment('primary key of tbl_recruitment_interview_type')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'interview_type')) {
                $table->unsignedInteger('interview_type')->comment('1 - Group Interview/ 2 - Cab Day')->change();
            }
        });
    }

}
