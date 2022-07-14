<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_phone')) {
            Schema::create('tbl_member_phone', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('phone', 20);
                    $table->unsignedTinyInteger('primary_phone')->default(1)->comment('1- Primary, 2- Secondary');
                    $table->unsignedInteger('memberId');
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_phone');
    }
}
