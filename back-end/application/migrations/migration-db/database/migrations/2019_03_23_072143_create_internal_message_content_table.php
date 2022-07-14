<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_content')) {
            Schema::create('tbl_internal_message_content', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('messageId')->index('messageId');
                    $table->unsignedInteger('senderId')->index('senderId');
                    $table->text('content');
                    $table->unsignedTinyInteger('is_priority')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('is_draft')->comment('0- not / 1 - yes');
                    $table->unsignedTinyInteger('is_reply')->comment('0- not / 1 - yes');
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_internal_message_content');
    }
}
