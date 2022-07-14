<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsForApplicantAsAddIdofrecruitmenttaskapplicantColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_for_applicant')) {
                $table->unsignedInteger('recruitment_task_applicant_id')->comment('id of tbl_recruitment_task_applicant')->after('question_id');
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
        Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_for_applicant')) {
                $table->dropColumn('recruitment_task_applicant_id');
                $table->unsignedInteger('applicant_id');
            }
        });
    }
}
