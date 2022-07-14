<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_participant_plan_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('participantId')->comment('primary key of tbl_participant');
			$table->unsignedInteger('ndis_num');
			$table->unsignedSmallInteger('type')->default('0')->comment('1=New/2=Renew');
			$table->string('notes', 500);			
			$table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->unsignedSmallInteger('archive')->default('0')->comment('0=Not/1=Archive');
        });				
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_participant_plan_notes');
    }
}
