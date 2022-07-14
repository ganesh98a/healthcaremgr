<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentAdditionalQuestionsAddTrainingCategoryCommnet extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'interview_type')) {
                $table->unsignedInteger('training_category')->comment('primary key of tbl_recruitment_interview_type')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail', 'interview_type')) {
                $table->unsignedInteger('training_category')->comment('')->change();
            }
        });
    }

}
