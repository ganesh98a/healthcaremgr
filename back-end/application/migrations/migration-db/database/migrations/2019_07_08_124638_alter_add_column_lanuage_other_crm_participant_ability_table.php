<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnLanuageOtherCrmParticipantAbilityTable extends Migration
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
            $table->string('languages_spoken_other',25);
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
        if(Schema::hasTable('tbl_crm_participant_ability') && Schema::hasColumn('tbl_crm_participant_ability', 'languages_spoken_other')) {
          Schema::table('tbl_crm_participant_ability', function (Blueprint $table) {
              $table->dropColumn('languages_spoken_other');
          });
      }
    }
}
