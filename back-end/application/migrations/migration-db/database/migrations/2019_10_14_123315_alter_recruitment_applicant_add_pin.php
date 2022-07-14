<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantAddPin extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'pin')) {
                $table->string('pin', 60);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'long_name')) {
                $table->dropColumn('pin');
            }
        });
    }

}
