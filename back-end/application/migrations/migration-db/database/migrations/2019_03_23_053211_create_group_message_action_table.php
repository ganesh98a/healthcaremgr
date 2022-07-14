<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupMessageActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_group_message_action')) {
            Schema::create('tbl_group_message_action', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('messageId');
                    $table->unsignedInteger('senderId');
                    $table->unsignedTinyInteger('status')->comment('1 - unread/ 2 - read / 3 - archive');
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
        Schema::dropIfExists('tbl_group_message_action');
    }
}
