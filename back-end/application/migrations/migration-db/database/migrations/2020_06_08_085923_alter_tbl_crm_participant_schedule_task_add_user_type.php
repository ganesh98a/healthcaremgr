<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantScheduleTaskAddUserType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'user_type')) {
                $table->unsignedInteger('user_type')->comment('1 - crm participant/ 2 - sales contact')->default(1)->after("crm_participant_id");
            }

            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'related_to')) {
                $table->unsignedInteger('related_to')->comment('primary key of as per relation_type (tbl_opportunity|tbl_leads| | |tbl_crm_risk_assessment)');
            }

            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'related_type')) {
                $table->unsignedInteger('related_type')->comment('1-opportunity/2-lead/3- service agreement/4-needs assessment/5-Risk assessment');
            }

            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'task_status')) {
                $table->unsignedInteger('task_status')->comment('0-Assigned,1-completed, 3-Archived')->change();
            }

            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_id')) {
                $table->bigInteger('entity_id')->unsigned();
            }

            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_type')) {
                $table->unsignedSmallInteger('entity_type')->unsigned()->comment('1-contact, 2-organisation, 3-opportunity');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'user_type')) {
                $table->dropColumn('user_type');
            }

            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'related_to')) {
                $table->dropColumn('related_to');
            }

            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'related_type')) {
                $table->dropColumn('related_type');
            }

            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_id')) {
                $table->dropColumn('entity_id');
            }

            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
        });
    }

}
