<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentTaskStageAddOrder extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_task_stage')) {
            Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
                $table->unsignedInteger('stage_order')->comment('In order create task');
                $table->unsignedInteger('main_stage')->comment('0 - No/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_task_stage')) {
            Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
                $table->dropColumn('stage_order');
                $table->dropColumn('main_stage');
            });
        }
    }

}
