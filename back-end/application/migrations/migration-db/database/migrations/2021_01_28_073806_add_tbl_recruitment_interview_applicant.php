<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTblRecruitmentInterviewApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_interview_applicant')) 
        {
            Schema::create('tbl_recruitment_interview_applicant', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('interview_id')->comment('tbl_recruitment_interview');
                $table->unsignedInteger('applicant_id');
                $table->unsignedInteger('application_id');
                $table->unsignedInteger('job_id');

                $table->unsignedTinyInteger('archive')->default(0)->comment('0- No, 1- Yes');
                
                $table->unsignedInteger('created_by')->nullable()->comment('reference of tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                
                $table->unsignedInteger('updated_by')->nullable()->comment('reference of tbl_member.id');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                
                $table->dateTime('created')->nullable();
                $table->dateTime('updated')->nullable();
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
        Schema::dropIfExists('tbl_recruitment_interview_applicant');
    }
}
