<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// I mean RunSeederForRecruitmentJobRequirementDocsAddWWCCReceipt
class RunSeederForRecruitmentJobRequirementDocsAddWWDCReceipt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }
}
