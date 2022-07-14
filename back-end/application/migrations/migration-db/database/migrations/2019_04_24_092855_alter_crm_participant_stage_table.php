<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_stage')) {
        Schema::table('tbl_crm_participant_stage', function (Blueprint $table) {
          $table->timestamp('created')->useCurrent();
          $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
      if (Schema::hasTable('tbl_crm_participant_stage') && Schema::hasColumn('tbl_crm_participant_stage','created') && Schema::hasColumn('tbl_crm_participant_stage','updated')) {
        Schema::table('tbl_crm_participant_stage', function (Blueprint $table) {
          $table->dropColumn('created');
          $table->dropColumn('updated');
        });
      }
    }
}
