<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentTaskStageChangeStageLabelNumberToStageLabelId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_number') && !Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_id')) {
                $table->renameColumn('stage_label_number', 'stage_label_id');
            }
        });

        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'key')) {
                $table->string('key', 100)->comment('uniqe key')->after('name');
            }
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_id')) {
                $table->unsignedInteger('stage_label_id')->comment('Auto increment key of tbl_recruitment_stage_label')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_number') && Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_id')) {
                $table->renameColumn('stage_label_id', 'stage_label_number');
            }
        });

        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'key')) {
                $table->dropColumn('key');
            }
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'stage_label_number')) {
                $table->unsignedInteger('stage_label_number')->comment('')->change();
            }
        });
    }

}
