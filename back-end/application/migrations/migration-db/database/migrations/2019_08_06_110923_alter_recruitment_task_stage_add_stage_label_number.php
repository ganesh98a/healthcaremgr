<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentTaskStageAddStageLabelNumber extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {

            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_number')) {
                $table->unsignedInteger('stage_label_number')->commnet('it is stage_number of tbl_recruitment_stage_label table');
            }

            if (Schema::hasColumn('tbl_recruitment_task_stage', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('tbl_recruitment_task_stage', 'stage_order')) {
                $table->dropColumn('stage_order');
            }

            if (Schema::hasColumn('tbl_recruitment_task_stage', 'main_stage')) {
                $table->dropColumn('main_stage');
            }
            
            $table->unsignedInteger('archive')->commnet('0 - No/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'stage_order')) {
                $table->unsignedInteger('stage_order')->comment('In order create task');
            }

            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'main_stage')) {
                $table->unsignedInteger('main_stage')->comment('0 - No/ 1 - Yes');
            }

            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'status')) {
                $table->tinyInteger('status')->comment('1 - active/ 2 - inactive');
            }

            if (Schema::hasColumn('tbl_recruitment_task_stage', 'status')) {
                $table->dropColumn('stage_label_number');
            }
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }

}
