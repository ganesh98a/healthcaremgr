<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentStageLabel extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_stage_label', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('stage_number');
            $table->unsignedInteger('archive')->comment('0 -NO /1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_stage_label');
    }

}
