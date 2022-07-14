<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferredByInTblRecruitmentApplicantAppliedApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'referred_by')) {
                    $table->text('referred_by')->nullable();
                }
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
        //
    }
}
