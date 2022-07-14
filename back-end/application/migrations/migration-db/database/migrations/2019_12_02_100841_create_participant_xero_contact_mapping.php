<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantXeroContactMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        if (!Schema::hasTable('tbl_participant_xero_contact_mapping')) {
            Schema::create('tbl_participant_xero_contact_mapping', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participant_id')->comment('auto increment id of tbl_participant table');
                $table->string('xero_contact_id', 150);
                $table->smallInteger('archive')->comment('0 -Not/1 - Archive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_participant_xero_contact_mapping');
    }
}
