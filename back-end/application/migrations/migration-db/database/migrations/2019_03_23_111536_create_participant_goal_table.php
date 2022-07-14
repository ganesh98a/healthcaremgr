<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantGoalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_goal')) {
            Schema::create('tbl_participant_goal', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->index();
                $table->string('title',255);
                $table->timestamp('start_date')->default('0000-00-00 00:00:00');
                $table->timestamp('end_date')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('status')->comment('1- Active / 2 - Archive');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_participant_goal');
    }
}
