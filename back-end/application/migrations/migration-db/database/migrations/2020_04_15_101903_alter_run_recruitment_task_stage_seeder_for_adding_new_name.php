<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;



class AlterRunRecruitmentTaskStageSeederForAddingNewName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
         if (!Schema::hasColumn('tbl_recruitment_task_stage', 'sort_order')) {
                $table->unsignedInteger('sort_order')->after('stage_label_id');
            }
        });	

        $seeder = new RecruitmentTaskStage();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_task_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_task_stage', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
}
