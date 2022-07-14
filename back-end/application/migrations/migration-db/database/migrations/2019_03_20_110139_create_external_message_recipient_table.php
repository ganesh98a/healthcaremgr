<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalMessageRecipientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_external_message_recipient')) {
            Schema::create('tbl_external_message_recipient', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('messageContentId')->index();
                $table->unsignedInteger('messageId')->index();
                $table->unsignedTinyInteger('recipinent_type')->comment('1 - admin / 2 - participant / 3 - member / 4 - organisation');
                $table->unsignedInteger('recipinentId');
                $table->unsignedTinyInteger('is_read')->default(0)->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('is_notify')->comment('0- not / 1 - yes');
                $table->timestamps();
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
        Schema::dropIfExists('tbl_external_message_recipient');
    }
}
