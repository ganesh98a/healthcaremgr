<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentActionSubtaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_action_subtask')) {
                Schema::create('tbl_recruitment_action_subtask', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('action_id');
                $table->string('subtask',50);
                $table->text('subtask_detail');
                $table->string('notes',200);
                $table->unsignedInteger('assigned_to');
                $table->datetime('due_date')->default('0000-00-00 00:00:00');
                $table->string('attachment',50);
                $table->unsignedTinyInteger('task_completed')->comment('0=Not Completed ,1=Completed');
                $table->unsignedTinyInteger('archive')->comment('1- Delete')->default('0');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_action_subtask');
    }
}
