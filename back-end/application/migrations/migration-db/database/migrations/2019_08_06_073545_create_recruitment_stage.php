<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentStage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_stage', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 200);
            $table->string('stage_label_id')->comment('Auto increment id of tbl_recruitment_stage_label');;
            $table->string('stage',100);
            $table->unsignedInteger('stage_order');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->unsignedInteger('archive')->comment('0 -NO /1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_stage');
    }

}
