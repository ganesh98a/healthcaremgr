<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantCareRequirementAddColumnRequireMobilityOther extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_participant_care_requirement')) {
            Schema::table('tbl_participant_care_requirement', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_participant_care_requirement','require_mobility_other')) {
                    $table->string('require_mobility_other',255)->nullable();
                }
                //
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
        if(Schema::hasTable('tbl_participant_care_requirement')) {
            Schema::table('tbl_participant_care_requirement', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_participant_care_requirement','require_mobility_other')) {
                    $table->dropColumn('require_mobility_other');
                }
                //
            });
        }
    }
}
