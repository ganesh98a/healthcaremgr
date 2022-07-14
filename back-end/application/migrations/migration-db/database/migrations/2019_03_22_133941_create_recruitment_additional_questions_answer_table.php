<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentAdditionalQuestionsAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_additional_questions_answer')) {
            Schema::create('tbl_recruitment_additional_questions_answer', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('question');
                $table->smallInteger('answer')->default('0');
                $table->string('question_option',255);
                $table->char('serial',1);
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
        Schema::dropIfExists('tbl_recruitment_additional_questions_answer');
    }
}
