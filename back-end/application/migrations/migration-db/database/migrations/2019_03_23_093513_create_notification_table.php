<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_notification')) {
            Schema::create('tbl_notification', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('userId');
                    $table->unsignedInteger('user_type')->comment('1- Member, 2- Participant');
                    $table->string('title', 200)->comment('like UpdateProfile,Care Requirement');
                    $table->text('shortdescription', 65535);
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->unsignedTinyInteger('status')->comment('0 Not read / 1 Read');
                    $table->unsignedTinyInteger('sender_type')->default(1)->comment('1- user / 2- admin');
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
        Schema::dropIfExists('tbl_notification');
    }
}
