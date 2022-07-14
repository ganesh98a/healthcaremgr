<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsOtherrsRequireMobilityRequireAssistanceCrmParticipantAbility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_ability')) {
        Schema::table('tbl_crm_participant_ability', function (Blueprint $table) {
            $table->string('require_assistance_other',25)->nullable();
            $table->string('require_mobility_other',25)->nullable();
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
      if(Schema::hasTable('tbl_crm_participant_ability') && Schema::hasColumn('tbl_crm_participant_ability', 'require_assistance_other') && Schema::hasColumn('tbl_crm_participant_ability', 'require_mobility_other') ) {
        Schema::table('tbl_crm_participant_ability', function (Blueprint $table) {
            $table->dropColumn('require_assistance_other');
            $table->dropColumn('require_mobility_other');
        });
      }
    }
}
