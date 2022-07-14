<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantAddGender extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            $table->unsignedInteger('gender')->default('1')->comment('1 - male/2- female');
            $table->unsignedInteger('flagged_status')->default('0')->comment('0 - not/ 1 - Pending/ 2 - flagged/ 3 - new')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->unsignedInteger('flagged_status')->default('0')->comment('1 - Pending/ 2 - flagged/ 3 - new')->change();
        });
    }

}
