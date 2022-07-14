<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentTaskAddNewTaskColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_task')) {
                $table->renameColumn('action_name', 'task_name');
                $table->renameColumn('action_type', 'task_stage')->comment('');
                $table->renameColumn('user', 'created_by');
                $table->text('relevant_task_note');
                $table->unsignedInteger('max_applicant');
                $table->tinyInteger('task_piority')->comment('1 - low/ 2 -medium/ 3 -high');
                $table->unsignedInteger('status')->comment('1 - In Progress/ 2 - Completed/ 3 -Canclled/ 4 - Archived')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_task')) {
                $table->renameColumn('task_name', 'action_name');
                $table->renameColumn('task_stage', 'action_type')->comment('');
                $table->renameColumn('created_by', 'user');
                $table->dropColumn('relevant_task_note');
                $table->dropColumn('max_applicant');
                $table->dropColumn('task_piority');
            }
        });
    }

}
