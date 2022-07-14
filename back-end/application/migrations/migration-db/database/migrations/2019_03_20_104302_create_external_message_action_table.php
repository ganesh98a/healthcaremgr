<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalMessageActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_external_message_action')) {
            Schema::create('tbl_external_message_action', function (Blueprint $table) {
                $table->increments('messageId');
                $table->unsignedInteger('user_type')->comment('1 - admin , 2 - participant , 3 - member , 4 - organisation');
                $table->unsignedInteger('userId')->comment('');
                $table->unsignedTinyInteger('is_fav')->defualt(0)->comment('0- not / 1 - yes');
                $table->unsignedTinyInteger('is_flage')->defualt(0)->comment('0- not / 1 - yes');
                $table->unsignedTinyInteger('archive')->defualt(0)->comment('0- not / 1 - yes');
                
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
        Schema::dropIfExists('tbl_external_message_action');
    }
}
