<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateIndexTblRecruitmentFormApplicantQuestionAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_recruitment_form_applicant_question', function (Blueprint $table) {
            $table->index(['form_applicant_id']);
         });
         Schema::table('tbl_recruitment_form_applicant_answer', function (Blueprint $table) {
            $table->index(['form_applicant_id']);
         });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_form_applicant_question', function (Blueprint $table) {
            $table->dropindex(['form_applicant_id']);
         });
         Schema::table('tbl_recruitment_form_applicant_answer', function (Blueprint $table) {
            $table->dropindex(['form_applicant_id']);
         });
    }
}
