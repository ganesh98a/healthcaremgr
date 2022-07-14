<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsForApplicantAsRenameColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
             $table->dropColumn('interview_type');
              $table->unsignedInteger('training_category')->unsignedInteger()->default(0)->comment('1 - Group Interview/ 2 - Cab Day')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_additional_questions_for_applicant', function (Blueprint $table) {
            $table->unsignedInteger ('training_category')->tinyInteger()->comment('')->change();
            $table->unsignedInteger ('interview_type')->comment('1 - Group Interview/ 2 - Cab Day');
        });
    }
}
