<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAddColumnHiredAs extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant', 'hired_as')) {
                    $table->unsignedSmallInteger('hired_as')->after("hired_date")->default(0)->comment("1 - member/2 -hired only");
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
                if (Schema::hasColumn('tbl_recruitment_applicant', 'hired_as')) {
                    $table->dropColumn('hired_as');
                }
            });
        }
    }

}
