<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_attachment')) {
            Schema::create('tbl_internal_message_attachment', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('messageContentId');
                    $table->string('filename', 200);
                    $table->dateTime('created');
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
        Schema::dropIfExists('tbl_internal_message_attachment');
    }
}
