<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentOaAnswerOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_oa_answer_options')) {
            Schema::create('tbl_recruitment_oa_answer_options', function (Blueprint $table) {
                $table->increments('id');               
                $table->unsignedInteger('oa_template_id')->comment('tbl_recruitment_oa_template.id');
                $table->unsignedInteger('question_id')->comment('tbl_recruitment_oa_questions.id');
                $table->text('option')->comment('answer option'); 
                $table->unsignedInteger('is_correct')->comment('is this option correct 1-correct,2-incorrect'); 
                $table->smallInteger('archive')->default(0);        
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('question_id','oa_answer_options_question_id')->references('id')->on('tbl_recruitment_oa_questions');
                $table->foreign('oa_template_id','oa_answer_options_template_id')->references('id')->on('tbl_recruitment_oa_template');
                $table->foreign('created_by','oa_answer_options_created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by','oa_answer_options_updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_recruitment_oa_answer_options')) {
            Schema::dropIfExists('tbl_recruitment_oa_answer_options');
        }
    }
}
