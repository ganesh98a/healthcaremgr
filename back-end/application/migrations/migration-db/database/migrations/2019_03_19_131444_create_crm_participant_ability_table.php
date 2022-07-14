<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantAbilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_ability')) {
            Schema::create('tbl_crm_participant_ability', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id');
                $table->string('cognitive_level',64)->nullable();
                $table->string('communication',64)->nullable();
                $table->unsignedTinyInteger('hearing_interpreter')->nullable()->default(1)->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('language_interpreter')->nullable()->default(1)->comment('1- Yes, 0- No');
                $table->string('languages_spoken',64)->nullable();
                $table->string('require_assistance',64)->nullable();
                $table->string('require_mobility',64)->nullable();
                $table->unsignedTinyInteger('linguistic_diverse')->nullable()->default(1)->comment('1- Yes, 0- No');
                $table->string('docs',255)->nullable();
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
        Schema::dropIfExists('tbl_crm_participant_ability');
    }
}
