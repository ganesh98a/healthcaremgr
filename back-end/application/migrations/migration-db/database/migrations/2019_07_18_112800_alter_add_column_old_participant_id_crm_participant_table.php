<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnOldParticipantIdCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant')) {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
          $table->unsignedInteger('old_participant_id')->default(0);
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
        if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'old_participant_id')  ) {
          Schema::table('tbl_crm_participant', function (Blueprint $table) {
              $table->dropColumn('old_participant_id');
          });
        }
    }
}
