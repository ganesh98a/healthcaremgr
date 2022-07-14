<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantScheduleTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
               $table->renameColumn('category', 'priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            $table->renameColumn('priority', 'category');
        });
    }
}
