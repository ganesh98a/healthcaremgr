<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentAdditionalQuestionsForApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
     if (!Schema::hasTable('tbl_recruitment_additional_questions_for_applicant')) {
        Schema::create('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('question_id');
            $table->unsignedTinyInteger('applicant_id');
            $table->text('question');
            $table->text('answer')->comment('answer submit by applicant');
            $table->unsignedTinyInteger('is_answer_correct')->comment('1- correct, 0- not checked, 2- wrong');
            $table->unsignedTinyInteger('question_type')->comment('0 Multiple , 1 Single');
            $table->unsignedTinyInteger('question_topic');
            $table->unsignedTinyInteger('training_category');
            $table->unsignedTinyInteger('archive');
            $table->unsignedInteger('created_by')->comment('created by staff id');
            $table->timestamp('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
        });
    }}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_additional_questions_for_applicant');
    }
}
