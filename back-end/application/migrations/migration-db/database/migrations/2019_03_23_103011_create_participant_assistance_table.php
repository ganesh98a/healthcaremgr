<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantAssistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_assistance')) {
            Schema::create('tbl_participant_assistance', function(Blueprint $table)
                {
                    $table->unsignedInteger('participantId')->default(0)->index('participantId');
                    $table->unsignedSmallInteger('assistanceId')->index('assistanceId');
                    $table->string('type', 100);
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
        Schema::dropIfExists('tbl_participant_assistance');
    }
}
