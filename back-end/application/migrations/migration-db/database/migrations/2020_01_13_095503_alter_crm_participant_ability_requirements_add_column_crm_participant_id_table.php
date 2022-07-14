<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantAbilityRequirementsAddColumnCrmParticipantIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('tbl_crm_participant_ability_requirements', function (Blueprint $table) {
            Schema::table('tbl_crm_participant_ability_requirements', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_crm_participant_ability_requirements', 'crm_participant_id')) {                    
					$table->unsignedInteger('crm_participant_id')->default(0)->comment('tbl_crm_participant_ability_requirements auto increment id')->after('id');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_ability_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_ability_requirements', 'crm_participant_id')) {
                $table->dropColumn('crm_participant_id');
            }
        });
    }
}
