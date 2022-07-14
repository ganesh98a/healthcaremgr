<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantCommunicationLogAsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
            $table->unsignedInteger('recruiter_id')->default('0')->after('applicant_id')->comment('primary key tbl_member');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
            $table->dropColumn('recruiter_id');
        });
    }
}
