<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantScheduleTaskDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_schedule_task_docs')) {
            Schema::create('tbl_crm_participant_schedule_task_docs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_task_id');
                $table->string('documents');
                $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_crm_participant_schedule_task_docs');
    }
}
