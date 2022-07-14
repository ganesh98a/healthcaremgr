<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailAddColumnIpadLastStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'ipad_last_stage')) {
            $table->unsignedTinyInteger('ipad_last_stage')->comment('0=no-action/1=login/2=Get Question/3=Submit Answer/4=Remaining Document list (CabDay Interview)/5= View Draft Contract/6= Send Draft Contract/7= Get Contract Pin')->default(0);
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
          if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'ipad_last_stage')) {
            $table->dropColumn('ipad_last_stage');
          }
        });
    }
}
