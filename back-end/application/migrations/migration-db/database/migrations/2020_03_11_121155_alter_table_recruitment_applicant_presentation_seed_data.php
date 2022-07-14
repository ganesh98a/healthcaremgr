<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentApplicantPresentationSeedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
		if( Schema::hasTable('tbl_recruitment_applicant_presentation')){
			$seeder = new RecruitmentApplicantPresentation();
			$seeder->run();
		}
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_presentation', function (Blueprint $table) {
            //
        });
    }
}
