<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertTblRecruitmentFlagReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new RecruitmentFlagReason();
        $seeder->run();

        # update all existing records with default reason
        DB::statement("UPDATE `tbl_recruitment_flag_applicant` SET `reason_id` = 1");
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
