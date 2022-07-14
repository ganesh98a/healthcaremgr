<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAddPrevName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'previous_name')) {
                $table->string('previous_name',255)->nullable()->comment('previousname data of applicant')->after('lastname');
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
            if (Schema::hasColumn('tbl_recruitment_applicant', 'previous_name')) {
                $table->dropColumn('previous_name');
            }
        });
    }
}
