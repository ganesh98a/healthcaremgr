<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentActionApplicantChangeActionIdToTaskId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_action_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_action_applicant')) {
                $table->renameColumn('action_id', 'taskId')->change();
                $table->tinyInteger('archive')->comment('0 - Not/ 1 - Yes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_action_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_action_applicant')) {
                $table->renameColumn('taskId', 'action_id')->change();
                $table->dropColumn('archive');
            }
        });
    }

}
