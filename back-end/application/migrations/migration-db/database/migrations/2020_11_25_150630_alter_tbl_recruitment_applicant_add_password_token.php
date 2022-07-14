<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAddPasswordToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'password_token')) {
                $table->text('password_token')->nullable()->after('person_id')->comment('Reset password token');
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
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'password_token')) {
                $table->dropColumn('password_token');
            }
        });
    }
}
