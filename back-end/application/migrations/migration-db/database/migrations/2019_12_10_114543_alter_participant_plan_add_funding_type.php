<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantPlanAddFundingType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant_plan', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_participant_plan','funding_type')){
                $table->unsignedInteger('funding_type')->comment('primary key tbl_funding_type')->after('participantId');
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
        Schema::table('tbl_participant_plan', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_participant_plan','completed_date')){
                $table->dropColumn('funding_type');
            }
        });
    }
}
