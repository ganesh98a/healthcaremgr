<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAsUpdateCommentForTaskStage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_task', 'task_stage')) {
                $table->unsignedSmallInteger('task_stage')->unsigned()->comment('tbl_recruitment_task_stage.id')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_recruitment_task', 'task_stage')) {
            $table->unsignedSmallInteger('task_stage')->unsigned()->change();
        }
    });
    }
}
