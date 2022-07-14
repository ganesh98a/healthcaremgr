<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_location', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('jobId')->default(0)->index('jobId');
            $table->string('street', 128);
            $table->string('city', 64);
            $table->unsignedInteger('postal')->unsigned();
            $table->unsignedInteger('state');
            $table->string('lat', 200);
            $table->string('long', 200);
            $table->string('phone', 200);
            $table->string('email', 200);
            $table->string('website', 200);
            $table->unsignedTinyInteger('archive')->default(0)->comment('1- Delete');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_job_location');
    }
}
