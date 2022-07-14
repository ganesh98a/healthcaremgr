<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_phone')) {
            Schema::create('tbl_crm_participant_phone', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
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
        Schema::dropIfExists('tbl_crm_participant_phone');
    }
}
