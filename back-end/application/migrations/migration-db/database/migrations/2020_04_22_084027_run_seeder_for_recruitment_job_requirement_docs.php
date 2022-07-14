<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederForRecruitmentJobRequirementDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The seeder below syncs the db rows with the json file. 
        //
        // Let's run the seeder in separate migration because it has new column/attr.
        // Let's NOT run this migration within another because the seeder will run first
        // Run them separately in another migration
        Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
            // $seeder = new RecruitmentJobRequirementDocs();
            // $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
