<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_call_log')) {
            Schema::create('tbl_call_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('receiver_number',25);
                $table->string('caller_number',25);
                $table->string('audio_url',200);
                $table->string('duration',20);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->text('api_data');
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
        Schema::dropIfExists('tbl_call_log');
    }
}
