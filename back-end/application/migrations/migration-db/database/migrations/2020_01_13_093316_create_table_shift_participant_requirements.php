<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableShiftParticipantRequirements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tbl_shift_participant_requirements')) {

            Schema::create('tbl_shift_participant_requirements', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId');
                $table->unsignedInteger('requirementId')->comment('tbl_participant_genral auto increment id')->nullable();
                $table->unsignedSmallInteger('requirement_type')->comment('1 for mobility AND 2 for assistance')->nullable();
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
        Schema::dropIfExists('tbl_shift_participant_requirements');
    }
}
