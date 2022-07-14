<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_stage')) {
            Schema::create('tbl_crm_participant_stage', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('stage_id');
                $table->unsignedInteger('crm_participant_id');
                $table->unsignedInteger('crm_member_id');
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
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
        Schema::dropIfExists('tbl_crm_participant_stage');
    }
}
