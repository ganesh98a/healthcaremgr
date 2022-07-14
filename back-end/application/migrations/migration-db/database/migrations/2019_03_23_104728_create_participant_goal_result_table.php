<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantGoalResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_goal_result')) {
            Schema::create('tbl_participant_goal_result', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->index();
                $table->unsignedInteger('goalId')->index();
                $table->unsignedInteger('shiftId');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->smallInteger('rating');
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
        Schema::dropIfExists('tbl_participant_goal_result');
    }
}
