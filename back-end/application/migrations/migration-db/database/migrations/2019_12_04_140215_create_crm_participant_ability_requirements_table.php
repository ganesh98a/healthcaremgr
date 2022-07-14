<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantAbilityRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('tbl_crm_participant_ability_requirements')) {
            Schema::create('tbl_crm_participant_ability_requirements', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('requirment');
                    $table->string('type', 100);
                    $table->unsignedTinyInteger('status')->comment('0-for inactive/ 1-for-active');
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_crm_participant_ability_requirements');
    }
}
