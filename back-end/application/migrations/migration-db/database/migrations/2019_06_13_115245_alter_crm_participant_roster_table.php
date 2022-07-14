<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantRosterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_roster')) {
        Schema::table('tbl_crm_participant_roster', function (Blueprint $table) {
            $table->string('shift_requirement',25)->change();
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
      if (Schema::hasTable('tbl_crm_participant_roster') && Schema::hasColumn('tbl_crm_participant_roster','shift_requirement')) {
        Schema::table('tbl_crm_participant_roster', function (Blueprint $table) {
            $table->dropColumn('shift_requirement');
        });
      }
    }
}
