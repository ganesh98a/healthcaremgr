<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentQuestionTopicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_question_topic')) {
            Schema::create('tbl_recruitment_question_topic', function (Blueprint $table) {
                $table->increments('id');
                $table->string('topic',100);
                $table->unsignedSmallInteger('status');
                $table->timestamp('created');
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
        Schema::dropIfExists('tbl_recruitment_question_topic');
    }
}
