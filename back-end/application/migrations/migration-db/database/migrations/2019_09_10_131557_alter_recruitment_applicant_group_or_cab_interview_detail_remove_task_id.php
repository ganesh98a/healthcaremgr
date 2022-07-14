<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupOrCabInterviewDetailRemoveTaskId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'taskId')) {
                 $table->dropColumn('taskId');
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
             if (!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'taskId')) {
                 $table->unsignedInteger('taskId')->comment('primary key of tbl_recruitment_task table');
            }
        });
    }

}
