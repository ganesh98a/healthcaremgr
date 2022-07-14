<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentTaskStage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_recruitment_task_stage')) {
                $table->increments('id');
                $table->string('name', '100');
                $table->tinyInteger('recruiter_view')->comment('0 - recruiter/ 1 - recriter admin');
                $table->tinyInteger('status')->comment('1 - active/ 2 - inactive');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_task_stage');
    }

}
