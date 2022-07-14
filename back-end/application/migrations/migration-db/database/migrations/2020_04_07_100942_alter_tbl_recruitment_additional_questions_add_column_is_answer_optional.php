<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAddColumnIsAnswerOptional extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_additional_questions', 'is_answer_optional')) {
                    $table->unsignedSmallInteger('is_answer_optional')->default(0)->comment("0 - Not optional/1 - optional");
                }
            });
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
       
            if (Schema::hasTable('tbl_recruitment_additional_questions')) {
                Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                    if (Schema::hasColumn('tbl_recruitment_additional_questions', 'is_answer_optional')) {
                        $table->dropColumn('is_answer_optional');
                    }
                });
            }
        
    }

}
