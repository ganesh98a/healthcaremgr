<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentFlagApplicant extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_flag_applicant')) {
            Schema::create('tbl_recruitment_flag_applicant', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id');
                $table->string('reason_title', 255);
                $table->text('reason_note');
                $table->unsignedInteger('flaged_request_by')->comment('recruiter id who request');
                $table->unsignedInteger('flaged_approve_by')->comment('recruiter id who approve or denied');
                $table->unsignedInteger('flag_status')->comment('1 - Pending/ 2 - approve/3 - denied request');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated');
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
        Schema::dropIfExists('tbl_recruitment_flag_applicant');
    }

}
