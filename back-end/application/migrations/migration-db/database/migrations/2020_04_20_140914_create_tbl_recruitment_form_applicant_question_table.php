<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentFormApplicantQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_form_applicant_question', function (Blueprint $table) {
            $table->increments('id');
            $table->text('question')->nullable();
            $table->unsignedInteger('form_applicant_id')->comment('tbl_recruitment_form_applicant.id');
            $table->unsignedInteger('question_type');
            $table->unsignedInteger('question_topic');
            $table->unsignedInteger('training_category');
            $table->unsignedInteger('display_order');
            $table->unsignedInteger('is_answer_optional');
            $table->unsignedInteger('is_required');
            $table->unsignedInteger('status');
            $table->unsignedInteger('archive');
        });

        Schema::create('tbl_recruitment_form_applicant_question_answer', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_applicant_id')->comment('tbl_recruitment_form_applicant.id');
            $table->unsignedInteger('question');
            $table->unsignedInteger('answer');
            $table->text('question_option')->nullable();
            $table->text('serial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_form_applicant_question');
        Schema::dropIfExists('tbl_recruitment_form_applicant_question_answer');
    }
}
