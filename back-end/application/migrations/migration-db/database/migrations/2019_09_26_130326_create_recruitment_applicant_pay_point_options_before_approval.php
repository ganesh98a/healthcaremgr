<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantPayPointOptionsBeforeApproval extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_applicant_pay_point_options_before_approval', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pay_point_approval_id')->comment('Primary key of table "tbl_recruitment_applicant_pay_point_approval"');
            $table->unsignedInteger('applicant_id')->comment('auto increment id of "tbl_recruitment_applicant" table.');
            $table->unsignedInteger('work_area')->comment('auto increment id of tbl_recruitment_applicant table.');
            $table->unsignedInteger('pay_point')->comment('auto increment id of tbl_recruitment_applicant table.');
            $table->unsignedInteger('pay_level')->comment('auto increment id of tbl_recruitment_applicant table.');
            $table->unsignedTinyInteger('is_approved')->default('0')->comment('1- approved, 0- Default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_pay_point_options_before_approval');
    }

}
