<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailAddComment extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant_group_or_cab_interview_detail')) {
            Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'app_orientation_status')) {
                    $table->unsignedSmallInteger('app_orientation_status')->default(0)->comment('0 - pending, 1 - yes, 2 - No')->change();
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'app_login_status')) {
                    $table->unsignedSmallInteger('app_login_status')->default(0)->comment('0 - pending, 1 - yes, 2 - No')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_applicant_group_or_cab_interview_detail')) {
            Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'app_orientation_status')) {
                    $table->unsignedSmallInteger('app_orientation_status')->default(0)->comment('0-pending/no,1-yes')->change();
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'app_login_status')) {
                    $table->unsignedSmallInteger('app_login_status')->default(0)->comment('0-pending/no,1-yes')->change();
                }
            });
        }
    }

}
