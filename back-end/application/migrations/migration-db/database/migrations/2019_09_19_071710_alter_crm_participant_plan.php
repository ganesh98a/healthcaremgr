<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
            $table->renameColumn('plan_id', 'plan_type');  
            $table->unsignedTinyInteger('status')->default(0)->comment('0- past,1 - ongoing');
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
            $table->renameColumn('plan_type', 'plan_id'); 
            if (Schema::hasColumn('tbl_crm_participant_plan','status')) {
                $table->dropColumn('status');
            } 
        });
    }
}
