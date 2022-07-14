<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAnswerForApplicantAsAddIdofquestiontableforapplicantColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_additional_questions_answer_for_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_answer_for_applicant')) {
                $table->unsignedInteger('additional_que_for_applicant_id')->comment('id of tbl_recruitment_additional_questions_for_applicant')->after('id');
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
        Schema::table('tbl_recruitment_additional_questions_answer_for_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_answer_for_applicant')) {
                $table->dropColumn('additional_que_for_applicant_id');
            }
        });
    }
}
