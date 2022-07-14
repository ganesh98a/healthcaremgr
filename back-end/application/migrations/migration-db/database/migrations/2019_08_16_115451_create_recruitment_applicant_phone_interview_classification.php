<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantPhoneInterviewClassification extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_applicant_phone_interview_classification')) {
            Schema::create('tbl_recruitment_applicant_phone_interview_classification', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id')->comment('auto increment id of tbl_recruitment_applicant table.');
                $table->unsignedInteger('classfication');
                $table->unsignedInteger('updated_by')->comment('recruiter or recrruiter admin id');;
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('archive')->default('0')->comment('0 - Not/1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_phone_interview_classification');
    }

}
