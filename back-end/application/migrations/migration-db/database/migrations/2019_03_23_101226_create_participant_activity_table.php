<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_activity')) {
            Schema::create('tbl_participant_activity', function(Blueprint $table)
                {
                    $table->unsignedMediumInteger('activityId');
                    $table->unsignedInteger('participantId');
                    $table->unsignedSmallInteger('type')->comment('1- Favourite, 2- Least Favourite');
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
        Schema::dropIfExists('tbl_participant_activity');
    }
}
