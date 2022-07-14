<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberBonusTraining extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_bonus_training', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('memberId')->comment('primary key of tbl_member');
            $table->string('title',100)->nullable();
            $table->date('date');
            $table->time('hour');
            $table->text('note')->nullable();
            $table->dateTime('created');
            $table->unsignedInteger('created_by')->comment('primary key of tbl_member');
            $table->unsignedSmallInteger('archive')->default('0')->comment('0=Not/1=Archive');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member_bonus_training');
    }
}
