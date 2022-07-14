<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_address')) {
            Schema::create('tbl_participant_address', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('participantId')->default(0)->index('participantId');
                    $table->string('street', 128);
                    $table->string('city', 64);
                    $table->unsignedInteger('postal')->unsigned();
                    $table->unsignedInteger('state');
                    $table->string('lat', 200)->nullable();
                    $table->string('long', 200)->nullable();
                    $table->unsignedTinyInteger('site_category');
                    $table->unsignedTinyInteger('primary_address')->comment('1- Primary, 2- Secondary');
                    $table->unsignedInteger('archive')->comment('0-for-not-deleted/1-for delete');
                });
                DB::statement('ALTER TABLE `tbl_participant_address` CHANGE `postal` `postal` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_participant_address');
    }
}
