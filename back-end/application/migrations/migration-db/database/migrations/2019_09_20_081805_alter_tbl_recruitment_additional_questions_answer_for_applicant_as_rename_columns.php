<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAnswerForApplicantAsRenameColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_recruitment_additional_questions_answer_for_applicant')) {
        Schema::table('tbl_recruitment_additional_questions_answer_for_applicant', function (Blueprint $table) {
            $table->renameColumn('question', 'question_id');
            $table->renameColumn('additional_que_for_applicant_id', 'additional_questions_for_applicant_id');
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
      if (Schema::hasTable('tbl_recruitment_additional_questions_answer_for_applicant') && Schema::hasColumn('tbl_recruitment_additional_questions_answer_for_applicant','question_id') ) {
        Schema::table('tbl_recruitment_additional_questions_answer_for_applicant', function (Blueprint $table) {
            $table->renameColumn('question_id','question');
        });
      }

      if (Schema::hasTable('tbl_recruitment_additional_questions_answer_for_applicant') && Schema::hasColumn('tbl_recruitment_additional_questions_answer_for_applicant','additional_questions_for_applicant_id') ) {
        Schema::table('tbl_recruitment_additional_questions_answer_for_applicant', function (Blueprint $table) {
            $table->renameColumn('additional_questions_for_applicant_id','additional_que_for_applicant_id');
        });
      }
    }
}
