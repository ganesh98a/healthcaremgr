<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantGroupCapQuestionAddInterviewTypeAndRecruitmentTaskApplicantId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'type')) {
                $table->renameColumn('type', 'interview_type');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'task_id')) {
                $table->dropColumn('task_id');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'applicant_id')) {
                $table->dropColumn('applicant_id');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'recruitment_task_applicant_id')) {
                $table->unsignedInteger('recruitment_task_applicant_id')->comment('auto increment id of tbl_recruitment_task_applicant table.')->after('id');
            }
        });
        
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'interview_type')) {
                $table->unsignedInteger('interview_type')->comment('primary key of tbl_recruitment_interview_type')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'interview_type')) {
                $table->renameColumn('interview_type', 'type');
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'task_id')) {
                $table->unsignedInteger('task_id')->comment('primary key of tbl_recruitment_interview_type');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'applicant_id')) {
                $table->unsignedInteger('applicant_id')->comment('auto increment id of tbl_recruitment_applicant table.');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'recruitment_task_applicant_id')) {
                $table->dropColumn('recruitment_task_applicant_id');
            }
        });
        
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_cap_question', 'type')) {
                $table->unsignedInteger('type')->comment('0 -group_interview / 1 - cap_day')->change();
            }
        });
    }

}
