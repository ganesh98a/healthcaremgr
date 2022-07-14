<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailAddInterviewType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->unsignedInteger('interview_type')->comment('1 - Group Interview/ 2 - Cab Day')->after('session_status');
            $table->dateTime('device_allocated_at')->default('0000-00-00 00:00:00')->after('interview_type');
            $table->unsignedInteger('recruitment_task_applicant_id')->comment('id of tbl_recruitment_task_applicant')->after('device_allocated_at');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->dropColumn('interview_type');
            $table->dropColumn('device_allocated_at');
            $table->dropColumn('created');
            $table->dropColumn('updated');
            $table->dropColumn('device_allocated_at');
        });
    }

}
