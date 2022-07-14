<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentTaskRecruiterAssign extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_task_recruiter_assign', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_recruitment_task_recruiter_assign')) {
                $table->increments('id');
                $table->unsignedInteger('recruiterId');
                $table->unsignedInteger('taskId');
                $table->tinyInteger('primary_recruiter')->comment('1 - primary/ 2 - secondary');
                $table->timestamp('created')->nullable();
                $table->tinyInteger('archive')->comment('0 - Not/ 1 - Yes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_task_recruiter_assign');
    }

}
