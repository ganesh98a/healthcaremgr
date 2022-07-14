<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommentsColumnTblCrmParticipantStageTable extends Migration
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
          $table->unsignedInteger('status')->comment('0- Pending 1- Success, 2- Unsuccess 3- In Progress 4- Parked ')->change();
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
      if(Schema::hasTable('tbl_crm_participant_stage') && Schema::hasColumn('tbl_crm_participant_stage', 'status') ) {
        Schema::table('tbl_crm_participant_stage', function (Blueprint $table) {
          $table->unsignedInteger('status')->comment('0- InActive 1- Active')->change();
        });
      }
    }
}
