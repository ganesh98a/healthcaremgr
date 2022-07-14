<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAddApplicationProcessStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'application_process_status')) {
                    $table->unsignedInteger('application_process_status')->default(0)->comment('0 -New/ 1 -Screening/ 2 -Interviews/ 3 -References/ 4 -Documents/ 5 -CAB/ 6 -Offer/ 7 -Hired / 8 -Rejected /');
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
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'application_process_status')) {
                    $table->dropColumn('application_process_status');
                }
            });
        }
    }
}
