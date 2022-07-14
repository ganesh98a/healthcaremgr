<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalMessageContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_external_message_content')) {
            Schema::create('tbl_external_message_content', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('messageId')->index();
                $table->unsignedTinyInteger('sender_type')->comment('1 - admin / 2 - participant / 3 - member / 4 - organisation');
                $table->unsignedInteger('userId');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->text('content');
                $table->unsignedTinyInteger('is_priority')->comment('0 - No / 1 - Yes');
                $table->unsignedTinyInteger('is_reply')->comment('0 - No / 1 - Yes');
                $table->unsignedTinyInteger('is_draft')->comment('0 - No / 1 - Yes');
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
        Schema::dropIfExists('tbl_external_message_content');
    }
}
