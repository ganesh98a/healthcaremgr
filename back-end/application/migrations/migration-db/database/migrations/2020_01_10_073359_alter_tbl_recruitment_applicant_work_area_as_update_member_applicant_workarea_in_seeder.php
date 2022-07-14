<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantWorkAreaAsUpdateMemberApplicantWorkareaInSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_work_area', function (Blueprint $table) {
           $seeder = new RecruitmentApplicantWorkArea();
           $seeder->run();
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_work_area', function (Blueprint $table) {
            //
        });
    }
}
