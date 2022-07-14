<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentAdditionalQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::create('tbl_recruitment_additional_questions', function (Blueprint $table) {
                $table->increments('id');
                $table->text('question');
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->unsignedInteger('created_by')->comment('created by staff id');
                $table->unsignedTinyInteger('question_type')->comment('0 Multiple , 1 Single');
                $table->unsignedTinyInteger('question_topic');
                $table->unsignedTinyInteger('training_category');
                $table->timestamp('updated')->useCurrent();
                $table->unsignedTinyInteger('archive');
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
        Schema::dropIfExists('tbl_recruitment_additional_questions');
    }
}
