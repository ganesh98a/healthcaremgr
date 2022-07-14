<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_login')) {
            Schema::create('tbl_participant_login', function (Blueprint $table) {
                $table->unsignedInteger('participantId');
                $table->text('token');
                $table->string('ip_address',100);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->useCurrent();
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
        Schema::dropIfExists('tbl_participant_login');
    }
}
