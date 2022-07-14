<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantCareRequirementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_care_requirement')) {
            Schema::create('tbl_participant_care_requirement', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('participantId')->index('participantId');
                    $table->text('diagnosis_primary');
                    $table->text('diagnosis_secondary');
                    $table->text('participant_care');
                    $table->string('cognition', 20);
                    $table->string('communication', 20);
                    $table->unsignedTinyInteger('english')->comment('1- Yes, 0- No, 2- Yes but not preferred');
                    $table->string('preferred_language', 16);
                    $table->string('preferred_language_other', 200);
                    $table->unsignedTinyInteger('linguistic_interpreter')->comment('1- Yes, 0- No');
                    $table->unsignedTinyInteger('hearing_interpreter')->comment('1- Yes, 0- No');
                    $table->string('require_assistance_other', 256);
                    $table->string('support_require_other', 256);
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
        Schema::dropIfExists('tbl_participant_care_requirement');
    }
}
