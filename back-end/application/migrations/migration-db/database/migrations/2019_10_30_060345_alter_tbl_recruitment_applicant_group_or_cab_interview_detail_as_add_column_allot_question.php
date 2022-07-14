<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAsAddColumnAllotQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->integer("allot_question")->after('app_login_status')->default(0)->comment('1= when applicant login and questions are alloted to him');
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
             if(Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','allot_question')){
                $table->dropColumn('allot_question');
              }
        });
    }
}
