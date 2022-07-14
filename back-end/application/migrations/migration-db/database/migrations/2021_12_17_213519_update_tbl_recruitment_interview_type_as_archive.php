<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblRecruitmentInterviewTypeAsArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE tbl_recruitment_interview_type SET archive = 1 WHERE key_type NOT IN ('group_interview','cab_day')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE tbl_recruitment_interview_type SET archive = 0 WHERE key_type NOT IN ('group_interview','cab_day')");
    }
}
