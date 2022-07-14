<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingNewQuestionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {
            
            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'answer_type')) {
                $table->unsignedInteger('answer_type')->comment('1 -Multiple Choice 2-Single Choice 3-True/False 4-Short Answers,5-Reading Comprehensive,6-Fill')->change();
            }
            if (!Schema::hasColumn('tbl_recruitment_oa_questions', 'parent_question_id')) {
                $table->unsignedInteger('parent_question_id')->comment('id of the passage question refers to same table')->after('id');
            }
          
            if (!Schema::hasColumn('tbl_recruitment_oa_questions', 'follow_up_questions_crp')) {
                $table->unsignedInteger('follow_up_questions_crp')->comment('follow_up_questions_for  the passage')->after('answer_type');
            }
            if (!Schema::hasColumn('tbl_recruitment_oa_questions', 'fill_up_formatted_question')) {
                $table->text('fill_up_formatted_question')->comment('fill_up_formatted_question  to identify fillable space')->after('answer_type');
            }
            if (!Schema::hasColumn('tbl_recruitment_oa_questions', 'blank_question_type')) {
                $table->unsignedInteger('blank_question_type')->comment('1 -freetext,2-choosable')->after('answer_type');
            }
            
        });
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            
            if (!Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'blank_question_position')) {
                $table->text('blank_question_position')->nullable()->comment('blank_question_position array');
            }
            if (Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'is_correct')) {
                $table->text('is_correct')->default(NULL)->nullable()->comment('0 - Incorrect,1 -correct,2-partially correct')->change();
            }
            
           
        });
        Schema::table('tbl_recruitment_oa_answer_options', function (Blueprint $table) {
            
            if (Schema::hasColumn('tbl_recruitment_oa_answer_options', 'is_correct')) {
                $table->text('is_correct')->default(NULL)->nullable()->comment('1 -Yes, 0 - No is_correct changing as text for storing as array')->change();
            }
             
            if (!Schema::hasColumn('tbl_recruitment_oa_answer_options', 'blank_question_position')) {
                $table->unsignedInteger('blank_question_position')->nullable()->comment('blank_question_position in number');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {

            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'answer_type')) {
                $table->unsignedInteger('answer_type')->comment('1 -Multiple Choice 2-Single Choice 3-True/False 4-Short Answers')->change();
            }
           
            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'parent_question_id')) {
                $table->dropColumn('parent_question_id');
            }
            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'follow_up_questions_crp')) {
                $table->dropColumn('follow_up_questions_crp');
            }
            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'fill_up_formatted_question')) {
                $table->dropColumn('fill_up_formatted_question');
            }
            if (Schema::hasColumn('tbl_recruitment_oa_questions', 'blank_question_type')) {
                $table->dropColumn('blank_question_type');
            }
            
        });

        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            
            if (Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'blank_question_position')) {
                 $table->dropColumn('blank_question_position');
            }
            if (Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'is_correct')) {
                $table->text('is_correct')->default(NULL)->nullable()->comment('1 -Yes,0 - No')->change();
            }
           
        });

        Schema::table('tbl_recruitment_oa_answer_options', function (Blueprint $table) {
            
            if (Schema::hasColumn('tbl_recruitment_oa_answer_options', 'is_correct')) {
                $table->text('is_correct')->default(NULL)->nullable()->comment('1 -Yes, 0 - No')->change();
            }
            if (Schema::hasColumn('tbl_recruitment_oa_answer_options', 'blank_question_position')) {
                $table->dropColumn('blank_question_position');
            }
        });
    }
}
