<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantAddReferenceUrl extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'referrer_url')) {
                    $table->string('referrer_url',255)->after("from_status")->nullable()->comment('applicant channel URL');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'referrer_url')) {
                    $table->dropColumn('referrer_url');
                }
            });
        }
    }

}
