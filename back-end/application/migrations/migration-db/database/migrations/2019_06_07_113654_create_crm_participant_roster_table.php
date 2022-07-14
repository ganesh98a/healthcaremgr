<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantRosterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_crm_participant_roster')) {
        Schema::create('tbl_crm_participant_roster', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('participantId');
            $table->timestamp('start_date')->nullable();
            $table->unsignedTinyInteger('status')->comment('1- Inactive, 2- Active');
            $table->unsignedTinyInteger('archived')->comment('1- Yes, 0- No');
            $table->string('shift_requirement',25);
            $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_crm_participant_roster');
    }
}
