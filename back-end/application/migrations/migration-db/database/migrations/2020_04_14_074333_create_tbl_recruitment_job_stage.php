<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobStage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_stage', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('jobId')->comment('primary key of tbl_recruitment_job');
            $table->unsignedInteger('stage_id')->comment('primary key of tbl_recruitment_stage_label');
            $table->unsignedSmallInteger('archive')->default('0')->comment('0=Not/1=Archive');
            $table->dateTime('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_job_stage');
    }
}
