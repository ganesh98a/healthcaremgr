<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAsAddColumnCountOfIndividualInterview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_recruitment_job', 'individual_interview_count')) {
            $table->unsignedTinyInteger('individual_interview_count')->comment('')->default(0);
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
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_recruitment_job', 'individual_interview_count')) {
            $table->dropColumn('individual_interview_count');
          }
        });
    
    }
}
