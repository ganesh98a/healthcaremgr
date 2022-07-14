<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantScheduleTaskAddCreatedByCoulmn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable();
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
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
}
