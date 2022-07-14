<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantSkill extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_applicant_skill', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->comment('primary key of tbl_recruitment_applicant');
            $table->unsignedInteger('skillId')->comment('primary key of tbl_participant_genral and type assistance');
            $table->string('other_title', 255)->nullable();
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_skill');
    }

}
