<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantPayPointApprovalTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_applicant_pay_point_approval')) {
            Schema::create('tbl_recruitment_applicant_pay_point_approval', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id')->comment('Primary key of table "tbl_recruitment_applicant"');
                $table->unsignedInteger('requested_by')->nullable();
                $table->unsignedInteger('approved_by')->nullable();
                $table->unsignedSmallInteger('status')->comment('1- Active, 0- Inactive');
                $table->unsignedSmallInteger('archived')->comment('1- archived, 0- Default');
                $table->dateTime('requested_at')->default('0000-00-00 00:00:00');
                $table->dateTime('approved_at')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->useCurrent();
                $table->string('relevant_notes', 500)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_applicant_pay_point_approval');
    }

}
