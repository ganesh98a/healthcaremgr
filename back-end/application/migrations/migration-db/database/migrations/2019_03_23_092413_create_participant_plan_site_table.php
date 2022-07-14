<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_plan_site')) {
            Schema::create('tbl_participant_plan_site', function (Blueprint $table) {
                $table->unsignedInteger('planId')->default('0')->index();
                $table->unsignedInteger('participantId')->index();
                $table->string('address',128);
                $table->string('city',32);
                $table->string('postal',10);
                $table->unsignedTinyInteger('state');
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
        Schema::dropIfExists('tbl_participant_plan_site');
    }
}
