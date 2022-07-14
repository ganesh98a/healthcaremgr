<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageRecipientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_recipient')) {
            Schema::create('tbl_internal_message_recipient', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('messageContentId');
                    $table->unsignedInteger('messageId')->index('messageId');
                    $table->unsignedInteger('recipientId')->index('recipientId');
                    $table->unsignedTinyInteger('cc')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('is_read')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('is_notify')->comment('0- not / 1 - yes');
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
        Schema::dropIfExists('tbl_internal_message_recipient');
    }
}
