<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentOaApplicantAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('job_assessment_id')->comment('tbl_recruitment_job_assessment.id');
            // $table->foreign('job_assessment_id')->references('id')->on('tbl_recruitment_job_assessment');
            $table->unsignedInteger('application_id')->comment('tbl_recruitment_applicant_applied_application.id');
            $table->foreign('application_id')->references('id')->on('tbl_recruitment_applicant_applied_application');
            $table->unsignedInteger('applicant_id')->comment('tbl_recruitment_applicant.id');
            $table->foreign('applicant_id')->references('id')->on('tbl_recruitment_applicant');
            $table->unsignedInteger('question_id')->comment('tbl_recruitment_oa_questions.id');
            $table->foreign('question_id')->references('id')->on('tbl_recruitment_oa_questions');
            $table->unsignedInteger('answer_id')->comment('provided answer id - tbl_recruitment_oa_answer_options.id, null is short answer')->nullable();
            $table->text('answer_text')->nullable();
            $table->unsignedSmallInteger('archive')->comment("0 - Not/1 - Yes");
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->comment('tbl_recruitment_applicant.id');
            $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
            $table->foreign('created_by')->references('id')->on('tbl_recruitment_applicant');
            $table->foreign('updated_by')->references('id')->on('tbl_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_oa_applicant_answer');
    }
}
