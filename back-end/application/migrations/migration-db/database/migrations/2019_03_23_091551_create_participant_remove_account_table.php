<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantRemoveAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_remove_account')) {
            Schema::create('tbl_participant_remove_account', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->default('0')->index();
                $table->string('reason',20);
                $table->unsignedTinyInteger('contact')->comment('1- Yes, 0- No');
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
        Schema::dropIfExists('tbl_participant_remove_account');
    }
}
