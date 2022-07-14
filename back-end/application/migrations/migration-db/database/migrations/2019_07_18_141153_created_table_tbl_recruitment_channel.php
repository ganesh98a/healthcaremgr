<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatedTableTblRecruitmentChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_channel', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel_name',50)->nullable();
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
            $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_channel');
    }
}
