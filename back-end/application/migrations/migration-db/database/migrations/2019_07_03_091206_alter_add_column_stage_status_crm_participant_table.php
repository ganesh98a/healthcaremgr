<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnStageStatusCrmParticipantTable extends Migration
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
          $table->unsignedTinyInteger('stage_status');
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
      if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'stage_status')) {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            $table->dropColumn('stage_status');
        });
      }
    }
}
