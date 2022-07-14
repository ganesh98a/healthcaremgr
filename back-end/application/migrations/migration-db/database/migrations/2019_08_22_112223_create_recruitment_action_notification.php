<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentActionNotification extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('tbl_recruitment_action_notification', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id');
            $table->string('action_type')->comment('1 - Flagged applicants/ 2 - Pay rate approvals/ 3 - Duplicate applicants');
            $table->string('recruiterId');
            $table->timestamp('created')->default('0000-00-00 00:00:00');
            $table->tinyInteger('archive')->commnet('0 - No/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_action_notification');
    }

}
