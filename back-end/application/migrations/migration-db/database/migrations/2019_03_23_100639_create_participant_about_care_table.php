<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantAboutCareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_about_care')) {
            Schema::create('tbl_participant_about_care', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('participantId');
                    $table->text('title');
                    $table->text('content');
                    $table->unsignedTinyInteger('primary_key')->comment('1-primary/2-secondary');
                    $table->string('categories', 200);
                    $table->unsignedTinyInteger('archive')->comment('0-for-not/1-for deleted');
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_participant_about_care');
    }
}
