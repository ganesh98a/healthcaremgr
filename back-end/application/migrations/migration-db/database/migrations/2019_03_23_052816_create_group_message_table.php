<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_group_message')) {
            Schema::create('tbl_group_message', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('teamId');
                    $table->unsignedInteger('type')->comment('1 - Team / 2 - Department');
                    $table->unsignedInteger('message_type')->comment('1 - text/ 2- file');
                    $table->text('message');
                    $table->unsignedInteger('senderId');
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
        Schema::dropIfExists('tbl_group_message');
    }
}
