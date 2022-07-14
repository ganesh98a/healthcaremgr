<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentInterviewType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_interview_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('archive')->comment('0- not/ 1 - archive');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_interview_type');
    }

}
