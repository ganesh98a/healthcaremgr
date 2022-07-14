<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_action')) {
            Schema::create('tbl_internal_message_action', function(Blueprint $table)
                {
                    $table->unsignedInteger('id', true);
                    $table->unsignedInteger('messageId');
                    $table->unsignedInteger('userId');
                    $table->unsignedTinyInteger('is_fav')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('archive')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('is_flage')->comment('0- not / 1 - yes');
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
        Schema::dropIfExists('tbl_internal_message_action');
    }
}
