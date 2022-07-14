<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantGenralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_genral')) {
            Schema::create('tbl_participant_genral', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('name',255);
                    $table->string('key_name',200);
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
        Schema::dropIfExists('tbl_participant_genral');
    }
}
