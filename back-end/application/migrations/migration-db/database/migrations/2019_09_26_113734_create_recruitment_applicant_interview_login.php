<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantInterviewLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_applicant_interview_login', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_applicant_id')->comment('auto increment id of tbl_recruitment_task_applicant table.');
            $table->text('login_token');
            $table->unsignedInteger('interviewtype')->comment('1 -group_interview / 2 - cap_day');
            $table->unsignedInteger('archive')->comment('0 -NO /1 - Yes');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_interview_login');
    }
}
