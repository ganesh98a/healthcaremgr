<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_shift_crm_participant')) {
        Schema::create('tbl_shift_crm_participant', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('participantId')->index();
            $table->unsignedTinyInteger('status');
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
        Schema::dropIfExists('tbl_shift_crm_participant');
    }
}
