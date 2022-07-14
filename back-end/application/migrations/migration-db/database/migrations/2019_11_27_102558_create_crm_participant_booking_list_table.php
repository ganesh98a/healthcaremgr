<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantBookingListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('tbl_crm_participant_booking_list')) {
            Schema::create('tbl_crm_participant_booking_list', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('crm_participant_id');
                    $table->string('firstname', 200);
                    $table->string('lastname', 200);
                    $table->string('relation', 200);
                    $table->string('phone', 100);
                    $table->string('email', 200);
                    $table->dateTime('created');
                    $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->unsignedTinyInteger('archive')->comment('0- not /1 - archive');
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
        Schema::dropIfExists('tbl_crm_participant_booking_list');
    }
}
