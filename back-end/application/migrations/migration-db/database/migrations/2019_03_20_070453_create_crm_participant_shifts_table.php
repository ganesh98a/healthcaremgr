<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_shifts')) {
            Schema::create('tbl_crm_participant_shifts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->string('title',228);
                $table->timestamp('start_date')->useCurrent();
                $table->timestamp('end_date')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('status')->comment('1- Active / 2 - Archive');
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
        Schema::dropIfExists('tbl_crm_participant_shifts');
    }
}
