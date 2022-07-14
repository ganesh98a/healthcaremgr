<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantSchecduleTaskAddColumnLeadId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
        public function up()
        {        
            Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
                $table->unsignedInteger('lead_id')->nullable()->after('crm_participant_id')->comment('tb_leads');
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
                if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'lead_id')) {
                    $table->dropColumn('lead_id');
                }
            });
        }
}
