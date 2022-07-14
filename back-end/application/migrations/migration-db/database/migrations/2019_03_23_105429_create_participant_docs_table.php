<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_docs')) {
            Schema::create('tbl_participant_docs', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('participantId')->index('participantId');
                    $table->unsignedTinyInteger('type')->index('type')->comment('1- Service, 2- SIL Doc');
                    $table->string('title', 32);
                    $table->string('filename', 64);
                    $table->dateTime('created');
                    $table->unsignedTinyInteger('archive');
                    $table->date('expiry_date')->nullable()->default('0000-00-00');
                    $table->unsignedTinyInteger('category')->nullable()->default(0);
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
        Schema::dropIfExists('tbl_participant_docs');
    }
}
