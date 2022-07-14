<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_email')) {
            Schema::create('tbl_crm_participant_email', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->string('email',64);
                $table->unsignedTinyInteger('primary_email')->comment('1- Primary, 2- Secondary');
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
        Schema::dropIfExists('tbl_crm_participant_email');
    }
}
