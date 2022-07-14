<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantScheduleTaskAsUpdateDatatypeOfColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
         $table->string('task_name',200)->change();
         $table->text('relevant_task_note')->change();
     });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            $table->string('task_name',30)->change();
            $table->string('relevant_task_note',100)->change();
        });
    }
}
