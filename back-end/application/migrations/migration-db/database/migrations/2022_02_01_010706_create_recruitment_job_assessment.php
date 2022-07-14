<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentJobAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_job_assessment')) {
            Schema::create('tbl_recruitment_job_assessment', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('job_id')->comment('id of tbl_recruitment_job');
                $table->unsignedInteger('applicant_id')->comment('tbl_person.id');
                $table->dateTime('expiry_date')->comment('Expiry date & time of each assessment'); 
                $table->uuid('uuid')->comment('unique id of each assessment'); 
                $table->tinyInteger('status')->comment('1-pending, 2-inprogress, 3-completed, 4-marked, 5-expired');
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id');
                $table->foreign('applicant_id')->references('id')->on('tbl_person');
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
        if (!Schema::hasTable('tbl_recruitment_job_assessment')) {
            Schema::dropIfExists('tbl_recruitment_job_assessment');
        }
    }
}
