<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRemoveParticipantEmailPlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_plan_management') && Schema::hasColumn('tbl_plan_management', 'participant_email') ) {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
              $table->dropColumn('participant_email');
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
      if(Schema::hasTable('tbl_plan_management') && Schema::hasColumn('tbl_plan_management', 'participant_email')  ) {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
              $table->dropColumn('participant_email');
        });
      }
    }
}
