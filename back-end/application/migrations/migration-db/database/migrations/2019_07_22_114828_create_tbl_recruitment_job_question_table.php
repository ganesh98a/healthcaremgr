<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_question', function (Blueprint $table) {
           $table->increments('id');
           $table->unsignedInteger('jobId');
           $table->text('question');
           $table->unsignedInteger('published_detail_id');
           $table->tinyInteger('archive')->comment('0- not/ 1 - archive');
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
        Schema::dropIfExists('tbl_recruitment_job_question');
    }
}
