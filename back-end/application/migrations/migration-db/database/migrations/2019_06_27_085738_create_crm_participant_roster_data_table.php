<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantRosterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_crm_participant_roster_data')) {
        Schema::create('tbl_crm_participant_roster_data', function (Blueprint $table) {
          $table->increments('id');
          $table->unsignedInteger('rosterId')->index();
          $table->unsignedTinyInteger('week_day')->comment('1- Mon, 2- Tue... 7-Sun');
          $table->timestamp('start_time')->default('0000-00-00 00:00:00');
          $table->timestamp('end_time')->default('0000-00-00 00:00:00');
          $table->unsignedTinyInteger('week_number')->comment('1- First, 2- second, 3- Third, 4- Four');
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
        Schema::dropIfExists('tbl_crm_participant_roster_data');
    }
}
