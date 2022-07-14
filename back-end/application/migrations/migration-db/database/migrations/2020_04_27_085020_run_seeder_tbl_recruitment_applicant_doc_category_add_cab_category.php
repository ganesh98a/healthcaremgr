<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederTblRecruitmentApplicantDocCategoryAddCabCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
			if(!Schema::hasColumn('tbl_recruitment_job_requirement_docs','key_name')){
                $table->string('key_name', 255)->comment("uniqe name");
            }
			
			
        });
		
		$seeder = new RecruitmentJobRequirementDocs();
		$seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_recruitment_job_requirement_docs','key_name')){
                $table->dropColumn('key_name');
            }
        });
    }
}
