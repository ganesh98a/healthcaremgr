<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantPushInMemberLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_applicant_push_in_member_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->comment('primary key of tbl_recruitment_applicant');
            $table->unsignedInteger('memberId')->comment('primary key of tbl_recruitment_applicant');

            $table->unsignedSmallInteger('status')->comment('1 - move successfully/ 2 - failed');
            $table->unsignedSmallInteger('any_warning')->commnet('0 - No/ 1 -Yes');
            $table->text('message');
//            $table->text('applicant_data', 100);
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_push_in_member_log');
    }

}
