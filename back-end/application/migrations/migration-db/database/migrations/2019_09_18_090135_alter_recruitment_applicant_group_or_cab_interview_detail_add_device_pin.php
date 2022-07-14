<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailAddDevicePin extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {

            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'session_status')) {
                $table->dropColumn('session_status');
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'device_pin')) {
                $table->string('device_pin', 200)->comment('Device pin for login ipad quiz')->after('deviceId');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'contract_status')) {
                $table->unsignedInteger('contract_status')->comment('1 -Successful/Generated, 0 -Pending')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('!tbl_recruitment_applicant_group_or_cab_interview_detail', 'session_status')) {
                $table->unsignedInteger('session_status')->comment('0- not,1- start,2- stop	');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'device_pin')) {
                $table->dropColumn('device_pin');
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'contract_status')) {
                $table->unsignedInteger('contract_status')->comment('1 -Successful, 0 -Pending')->change();
            }
        });
    }

}
