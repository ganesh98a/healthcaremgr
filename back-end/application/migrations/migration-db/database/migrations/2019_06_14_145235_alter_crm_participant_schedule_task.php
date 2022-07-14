<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantScheduleTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            $table->unsignedTinyInteger('archive')->after('created_at')->default(0)->comment('0-not/1-yes');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if(Schema::hasTable('tbl_crm_participant_schedule_task') && Schema::hasColumn('tbl_crm_participant_schedule_task', 'archive')) {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            $table->dropColumn('archive');
        });
      }
    }
}
