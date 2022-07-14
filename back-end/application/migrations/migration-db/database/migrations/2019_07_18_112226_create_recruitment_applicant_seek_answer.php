<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantSeekAnswer extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_applicant_seek_answer')) {
            Schema::create('tbl_recruitment_applicant_seek_answer', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicantId');
                $table->unsignedInteger('jobId');
                $table->unsignedInteger('questionId');
                $table->text('answer');
                $table->tinyInteger('answer_status')->comment('0 - false/ 1 - true');
                $table->timestamp('created')->default('0000-00-00 00:00:00');;
                $table->tinyInteger('archive')->comment('0 - No/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_seek_answer');
    }

}
