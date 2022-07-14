<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberPositionAwardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_position_award')) {
            Schema::create('tbl_member_position_award', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('companyId');
                    $table->unsignedInteger('memberId');
                    $table->unsignedTinyInteger('position');
                    $table->unsignedTinyInteger('award');
                    $table->unsignedTinyInteger('level');
                    $table->dateTime('created');
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
                    $table->unsignedTinyInteger('archive')->default('0')->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_position_award');
    }
}
