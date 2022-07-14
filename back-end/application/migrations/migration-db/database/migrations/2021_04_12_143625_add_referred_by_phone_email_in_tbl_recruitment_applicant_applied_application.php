<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferredByPhoneEmailInTblRecruitmentApplicantAppliedApplication extends Migration
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
                if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'referred_phone')) {
                    $table->string('referred_phone',30)->nullable();
                }
                if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'referred_email')) {
                    $table->string('referred_email',50)->nullable();
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
