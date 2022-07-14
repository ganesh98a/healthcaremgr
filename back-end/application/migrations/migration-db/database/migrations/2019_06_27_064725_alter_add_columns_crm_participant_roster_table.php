<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsCrmParticipantRosterTable extends Migration
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
          $table->dropColumn('archived');
          $table->dropColumn('shift_requirement');
          $table->dropColumn('mobility_requirement');
          $table->string('title',50);
          $table->string('shift_round',20);
          $table->unsignedTinyInteger('is_default')->comment('1- No, 0- Yes');
          $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
          $table->timestamp('end_date')->nullable();
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
      if(Schema::hasTable('tbl_crm_participant_roster') && Schema::hasColumn('tbl_crm_participant_roster', 'archived') && Schema::hasColumn('tbl_crm_participant_roster', 'mobility_requirement') && Schema::hasColumn('tbl_crm_participant_roster', 'shift_requirement')) {
        Schema::table('tbl_crm_participant_roster', function (Blueprint $table) {
          $table->dropColumn('mobility_requirement');
          $table->dropColumn('archived');
          $table->dropColumn('shift_requirement');
          $table->dropColumn('title');
          $table->dropColumn('shift_round');
          $table->dropColumn('is_default');
          $table->dropColumn('updated');
          $table->dropColumn('end_date');
        });
      }
    }
}
