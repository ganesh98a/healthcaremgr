<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_contact')) {
            Schema::create('tbl_member_contact', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->string('created', 20);
                    $table->char('type', 1)->comment('P- Phone, M- InMessage');
                    $table->string('record_file', 64);
                    $table->unsignedInteger('messageId');
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
        Schema::dropIfExists('tbl_member_contact');
    }
}
