<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentOaQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_oa_questions')) {
            Schema::create('tbl_recruitment_oa_questions', function (Blueprint $table) {
                $table->increments('id');  
                $table->text('question')->nullable();              
                $table->unsignedInteger('oa_template_id')->comment('tbl_recruitment_oa_template.id');
                $table->unsignedInteger('answer_type')->comment('1 -Multiple Choice 2-Single Choice 3-True/False 4-Short Answers');
                $table->unsignedInteger('grade')->comment('grade points for the question'); 
                $table->smallInteger('is_mandatory')->comment('whether the question is_mandatory');  
                $table->smallInteger('archive')->default(0);         
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('oa_template_id','oa_questions_oa_template_id')->references('id')->on('tbl_recruitment_oa_template');
                $table->foreign('created_by','oa_questions_created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by','oa_questions_updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_recruitment_oa_questions')) {
            Schema::dropIfExists('tbl_recruitment_oa_questions');
        }
    }
}
