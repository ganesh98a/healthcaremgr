<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantGoalNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('tbl_participant_goal_notes', function (Blueprint $table) {
			$table->increments('id');                
            $table->unsignedInteger('goal_id')->nullable()->comment('tbl_participant_goal auto_increment id');				
			$table->string('notes',255)->nullable()->comment('Notes for participant goal');
			$table->unsignedInteger('created_by')->nullable()->comment('tbl_member auto_increment id');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('created')->default('0000-00-00 00:00:00');                
            $table->unsignedSmallInteger('archive')->default('0')->comment('0- Not archive/ 1 - Yes');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_participant_goal_notes');
    }
}
