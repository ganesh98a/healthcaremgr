<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_email')) {
            Schema::create('tbl_member_email', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('email', 64);
                    $table->unsignedInteger('memberId');
                    $table->unsignedTinyInteger('primary_email')->comment('1- Primary, 2- Secondary');
                    $table->unsignedTinyInteger('archive')->default(0)->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_email');
    }
}
