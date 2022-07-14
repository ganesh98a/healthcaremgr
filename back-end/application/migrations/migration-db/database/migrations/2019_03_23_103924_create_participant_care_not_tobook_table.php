<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantCareNotTobookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_care_not_tobook')) {
            Schema::create('tbl_participant_care_not_tobook', function(Blueprint $table)
                {
                    $table->unsignedInteger('participantId')->index('participantId');
                    $table->unsignedTinyInteger('gender');
                    $table->string('ethnicity', 20);
                    $table->string('religious', 20);
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
        Schema::dropIfExists('tbl_participant_care_not_tobook');
    }
}
