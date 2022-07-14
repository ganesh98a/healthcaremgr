<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantScheduleTaskAsUpdateDatatypeOfUpdatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `tbl_crm_participant_schedule_task`
                CHANGE `updated_at` `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `task_status`");
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
            //
        });
    }
}
