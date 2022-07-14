<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupCapQuestionAsRenameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_group_cap_question')) {
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_applicant_group_cap_question', 'tbl_recruitment_applicant_not_assign_question');
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
      if (Schema::hasTable('tbl_recruitment_applicant_not_assign_question')) {
        Schema::table('tbl_recruitment_applicant_not_assign_question', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_applicant_not_assign_question', 'tbl_recruitment_applicant_group_cap_question');
        });
      }
    }
}
