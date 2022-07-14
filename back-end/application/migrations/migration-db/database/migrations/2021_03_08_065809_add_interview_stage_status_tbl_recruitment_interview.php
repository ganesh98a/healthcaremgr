<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterviewStageStatusTblRecruitmentInterview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_interview')) {
            Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_interview', 'interview_stage_status')) {
                    $table->unsignedInteger('interview_stage_status')->default(0)->after('meeting_link')->comment('0 -Open/ 1 -Scheduled/ 2 -Inprogress/ 3 -Successful/ 4 -Unsuccessful');
                }
            });
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_recruitment_interview')) {
            Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_interview', 'interview_stage_status')) {
                    $table->dropColumn('interview_stage_status');
                }
            });
        }
    }
}
