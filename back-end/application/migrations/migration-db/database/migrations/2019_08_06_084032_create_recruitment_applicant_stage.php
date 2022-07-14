<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantStage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_applicant_stage', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id');
            $table->unsignedInteger('stageId')->comment('Primary key of table tbl_recruitment_stage');
            $table->unsignedInteger('status')->comment('1 - Pending /2 - In-progress / 3 - completed / 4 - Unsuccessful');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->unsignedInteger('archive')->comment('0 - No /1 - Yes');
            $table->dateTime('completed_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_stage');
    }

}
