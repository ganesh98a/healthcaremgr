<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAsRenameColumnsDeviceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            //
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
            //
        });
    }
}
