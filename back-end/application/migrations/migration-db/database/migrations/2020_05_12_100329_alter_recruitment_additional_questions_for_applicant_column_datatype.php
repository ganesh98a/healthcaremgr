<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentAdditionalQuestionsForApplicantColumnDatatype extends Migration
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
                if (Schema::hasColumn('tbl_recruitment_additional_questions_for_applicant', 'question_id')) {
                    $table->unsignedInteger('question_id')->dafault(0)->change();
                }
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
