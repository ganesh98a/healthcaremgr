<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_phone')) {
            Schema::create('tbl_participant_phone', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->default('0')->index();
                $table->string('phone',20);
                $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary');
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
        Schema::dropIfExists('tbl_participant_phone');
    }
}
