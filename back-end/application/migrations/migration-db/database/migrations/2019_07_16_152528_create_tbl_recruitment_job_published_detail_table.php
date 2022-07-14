<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobPublishedDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_published_detail', function (Blueprint $table) {
           $table->increments('id');
           $table->unsignedInteger('jobId')->default(0)->index('jobId');
           $table->unsignedInteger('channel')->comment('1-seek/2-website');
           $table->dateTime('from_date');
           $table->dateTime('to_date');
           $table->unsignedInteger('is_recurring');
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
        Schema::dropIfExists('tbl_recruitment_job_published_detail');
    }
}
