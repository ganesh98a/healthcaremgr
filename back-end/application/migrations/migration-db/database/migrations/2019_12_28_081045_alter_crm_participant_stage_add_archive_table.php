<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantStageAddArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {       
		Schema::table('tbl_crm_participant_stage', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_crm_participant_stage', 'archive')) {
                $table->unsignedInteger('archive')->comment('1 - Yes/ 0 - Not');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_stage', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }
}
