<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantOcServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_oc_services')) {
            Schema::create('tbl_participant_oc_services', function (Blueprint $table) {
                $table->unsignedInteger('participantId')->default('0')->index();
                $table->smallInteger('oc_service')->index();
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
        Schema::dropIfExists('tbl_participant_oc_services');
    }
}
