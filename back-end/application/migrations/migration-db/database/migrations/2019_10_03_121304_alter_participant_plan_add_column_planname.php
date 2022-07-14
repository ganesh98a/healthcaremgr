<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantPlanAddColumnPlanname extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_participant_plan')) {
            Schema::table('tbl_participant_plan', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_participant_plan','plan_name'))
                {
                    $table->string('plan_name',50)->after('participantId');
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

        if (Schema::hasTable('tbl_participant_plan')) {
            Schema::table('tbl_participant_plan', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_participant_plan','plan_name'))
                {
                    Schema::dropColumn('plan_name');
                }
            });
        }
    }
}
