<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantKinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_kin')) {
            Schema::create('tbl_participant_kin', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->index();
                $table->string('firstname',32);
                $table->string('lastname',32);
                $table->string('relation',24);
                $table->string('phone',20);
                $table->string('email',64);
                $table->unsignedTinyInteger('primary_kin')->comment('1- Primary, 2- Secondary');
                $table->unsignedInteger('archive')->comment('0 - Not / - archive');
                $table->timestamp('updated')->useCurrent();
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
        Schema::dropIfExists('tbl_participant_kin');
    }
}
