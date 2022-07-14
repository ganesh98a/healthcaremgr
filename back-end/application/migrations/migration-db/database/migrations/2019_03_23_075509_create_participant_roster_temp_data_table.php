<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantRosterTempDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_roster_temp_data')) {
            Schema::create('tbl_participant_roster_temp_data', function (Blueprint $table) {
                $table->unsignedInteger('rosterId');
                $table->longText('rosterData');
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
        Schema::dropIfExists('tbl_participant_roster_temp_data');
    }
}
