<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantScheduleTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_schedule_task')) {
            Schema::create('tbl_crm_participant_schedule_task', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->string('task_name',30);
                $table->unsignedInteger('category');
                $table->date('due_date')->default('0000-00-00');
                $table->string('assign_to',30);
                $table->string('relevant_task_note',100);
                $table->unsignedInteger('parent_id')->comment('0-for parent');
                $table->string('note',100);
                $table->unsignedTinyInteger('task_status')->comment('1-completed,2-selected');
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('tbl_crm_participant_schedule_task');
    }
}
