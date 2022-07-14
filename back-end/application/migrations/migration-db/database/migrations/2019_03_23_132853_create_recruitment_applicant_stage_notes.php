<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantStageNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant_stage_notes')) {
        Schema::create('tbl_recruitment_applicant_stage_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id');
            $table->string('notes',200);
            $table->datetime('created');
            $table->unsignedTinyInteger('stage');
            $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
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
        Schema::dropIfExists('tbl_recruitment_applicant_stage_notes');
    }
}
