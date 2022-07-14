<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_applicant_log')) {
            Schema::create('tbl_recruitment_applicant_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id');
                $table->string('action');
                $table->text('data');
                $table->unsignedInteger('stage');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->tinyInteger('archive')->commnet('0 - No/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_log');
    }

}
