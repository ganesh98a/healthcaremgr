<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantRosterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_roster')) {
            Schema::create('tbl_participant_roster', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId');
                $table->string('title',50);
                $table->unsignedTinyInteger('is_default')->comment('1- No, 0- Yes');
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->string('shift_round',20)->nullable();
                $table->unsignedTinyInteger('status')->comment('1 - Active / 2 - Pending /3 - Inactive /4 - Reject / 5 - Archive');
                $table->timestamp('created');
                $table->timestamp('updated')->useCurrent();
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
        Schema::dropIfExists('tbl_participant_roster');
    }
}
