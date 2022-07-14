<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTblOpportunityStaffSafteyChecklistAddParticipantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity_staff_saftey_checklist', function (Blueprint $table) {
            $table->unsignedInteger('participant_id')->nullable()->comment('primay key of tbl_participants_master')->after('opportunity_id');
            $table->foreign('participant_id')->references('id')->on('tbl_participants_master')->onUpdate(null)->onDelete(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity_staff_saftey_checklist', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_staff_saftey_checklist', 'participant_id')) {
                $table->dropForeign(['participant_id']);
                $table->dropColumn('participant_id');
            }
        });
    }
}
