<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenameColumnCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_plan')) {
        Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
            $table->renameColumn('plan_type', 'plan_name');
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
      if (Schema::hasTable('tbl_crm_participant_plan') && Schema::hasColumn('tbl_crm_participant_plan','plan_name') ) {
        Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
            $table->renameColumn('plan_name','plan_type');
        });
      }
    }
}
