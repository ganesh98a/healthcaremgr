<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupCapQuestionAsAddArchiveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_applicant_group_cap_question')) {
                $table->unsignedInteger('archive')->comment('1 - delete')->default('0');
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
        Schema::table('tbl_recruitment_applicant_group_cap_question', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_applicant_group_cap_question')) {
                $table->dropColumn('archive');
            }
        });
    }
}
