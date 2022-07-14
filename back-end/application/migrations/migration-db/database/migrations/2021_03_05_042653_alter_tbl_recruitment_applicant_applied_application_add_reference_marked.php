<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAppliedApplicationAddReferenceMarked extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'is_reference_marked')) {
                $table->unsignedInteger('is_reference_marked')->default(0)->comment('0 - No / 1 - Yes reference is marked as verfied or not');
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
        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'is_reference_marked')) {
                $table->dropColumn('is_reference_marked');
            }
        });
    }
}
