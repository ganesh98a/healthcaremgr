<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsForApplicantAsDropColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_additional_questions_for_applicant')) {
            Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'question'))
                    $table->dropColumn('question');

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'question_type'))
                    $table->dropColumn('question_type');

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'question_topic'))
                    $table->dropColumn('question_topic');

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'training_category'))
                    $table->dropColumn('training_category');

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'created_by'))
                    $table->dropColumn('created_by');

                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'created'))
                    $table->dropColumn('created');
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
        Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
            //
        });
    }
}
