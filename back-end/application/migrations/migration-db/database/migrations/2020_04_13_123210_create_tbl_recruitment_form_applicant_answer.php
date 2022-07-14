<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentFormApplicantAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_form_applicant_answer', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_applicant_id')->comment('tbl_recruitment_form_applicant.id');
            $table->unsignedInteger('question_id')->comment('tbl_recruitment_additional_question.id');
            $table->unsignedInteger('answer_id')->comment('provided answer id, null is short answer')->nullable();;
            $table->text('answer_text')->nullable();
            $table->dateTime('created')->nullable();
            $table->unsignedSmallInteger('archive')->comment("0 - Not/1 - Yes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_form_applicant_answer');
    }
}
