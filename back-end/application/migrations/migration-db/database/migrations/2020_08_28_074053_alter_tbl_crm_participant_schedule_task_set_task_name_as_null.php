<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantScheduleTaskSetTaskNameAsNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant_schedule_task')) {
            Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_id')) {
                    $table->bigInteger('entity_id')->unsigned()->default(0)->change();
                }
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
        if (Schema::hasTable('tbl_crm_participant_schedule_task')) {
            Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_id')) {
                    $table->bigInteger('entity_id')->unsigned()->default(null)->change();
                }
            });
        }
    }
}
