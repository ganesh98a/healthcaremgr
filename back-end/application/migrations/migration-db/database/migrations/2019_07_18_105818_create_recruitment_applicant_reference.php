<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantReference extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_applicant_reference')) {
            Schema::create('tbl_recruitment_applicant_reference', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id');
                $table->string('name', 200);
                $table->string('email', 200);
                $table->string('phone', 200);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->tinyInteger('archive')->commnet('0 - No/ 1 - Yes');
                $table->timestamp('updated');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_reference');
    }

}
