<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanNew extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_participant_plan')) {
            Schema::create('tbl_participant_plan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->comment('primary key tbl_participant');
                $table->unsignedInteger('funding_type')->comment('primary key tbl_funding_type');
                $table->date('start_date')->default('0000-00-00');
                $table->date('end_date')->default('0000-00-00');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('archive')->comment('0- Not/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_participant_plan');
    }

}
