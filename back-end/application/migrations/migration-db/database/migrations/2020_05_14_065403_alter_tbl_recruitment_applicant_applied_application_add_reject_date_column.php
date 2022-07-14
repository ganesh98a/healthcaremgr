<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAppliedApplicationAddRejectDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_applied_application', function(Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'rejected_date')) {
                $table->dateTime('rejected_date')->nullable();
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
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'rejected_date')) {
                $table->dropColumn('rejected_date');
            }
        });
    }
}
