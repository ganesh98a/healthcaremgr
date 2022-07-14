<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantGroupInterviewDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_applicant_group_interview_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id');
            $table->unsignedInteger('taskId');
            $table->unsignedInteger('deviceId')->comment('when device is assigned')->nullable();
            $table->unsignedTinyInteger('session_status')->comment('0- not,1- start,2- stop')->default(0);
            $table->unsignedTinyInteger('archive')->default(0);
            $table->unsignedTinyInteger('status')->comment('applicant task status (Pending- 0,Unsuccessful- 2,Successful- 1)')->default(0);
           /* $table->string('device_name', 200);
            $table->string('device_number', 200);
            $table->string('device_location', 200);*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_group_interview_detail');
    }
}
