<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentOaApplicantAnswerAddInsertByAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'insert_by_admin')) {
                $table->unsignedTinyInteger('insert_by_admin')->nullable()->comment('1 - this record inserted by admin if the assessment is not submitted or auto submitted');
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
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_oa_applicant_answer', 'insert_by_admin')) {
                $table->dropColumn('participant_type');
            }
        });
    }
}
