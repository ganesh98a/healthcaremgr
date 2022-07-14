<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnRosteridCrmParticipantShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_shifts')) {
        Schema::table('tbl_crm_participant_shifts', function (Blueprint $table) {
            $table->unsignedInteger('rosterId');
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
      if(Schema::hasTable('tbl_crm_participant_shifts') && Schema::hasColumn('tbl_crm_participant_shifts', 'rosterId') ) {
        Schema::table('tbl_crm_participant_shifts', function (Blueprint $table) {
            $table->dropColumn('rosterId');
        });
      }
    }
}
