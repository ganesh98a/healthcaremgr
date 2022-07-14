<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantPlanChangePlanNameDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_plan','plan_name')) {
                $table->string('plan_name',50)->change();
            }
            else{
                $table->string('plan_name',50)->after('crm_participant_id'); 
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
        Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_plan','plan_name')) {
                $table->dropColumn('plan_name');
            }
        });
    }
}
