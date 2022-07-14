<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAsRemoveColumnApplicantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'applicant_id')) {
                 $table->dropColumn('applicant_id');
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
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
         if (!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'applicant_id')) {
             $table->unsignedInteger('applicant_id')->comment('primary key of tbl_recruitment_applicant table');
         }
     });
    }
}
