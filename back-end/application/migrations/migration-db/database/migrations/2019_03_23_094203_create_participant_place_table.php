<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_place')) {
            Schema::create('tbl_participant_place', function (Blueprint $table) {
                $table->mediumInteger('placeId');
                $table->unsignedInteger('participantId');
                $table->unsignedTinyInteger('type')->comment('1- Favourite, 2- Least Favourite');
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
        Schema::dropIfExists('tbl_participant_place');
    }
}
