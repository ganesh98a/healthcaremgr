<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLastFetchTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_email_last_fetch_time', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamp('last_fetch')->default('0000-00-00 00:00:00');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_email_last_fetch_time');
    }
}
