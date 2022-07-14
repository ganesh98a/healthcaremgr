<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAddHiredDateAndRejectedDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant', 'hired_date')) {
                    $table->dateTime('hired_date')->after("status");
                }
                
                if (!Schema::hasColumn('tbl_recruitment_applicant', 'rejected_date')) {
                    $table->dateTime('rejected_date')->after("hired_date");
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
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant', 'hired_date')) {
                    $table->dropColumn('hired_date');
                }
                
                if (Schema::hasColumn('tbl_recruitment_applicant', 'rejected_date')) {
                    $table->dropColumn('rejected_date');
                }
            });
        }
    }

}
