<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskApplicantAddApplicantTaskStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_task_applicant', 'applicant_task_status')) {
                $table->unsignedInteger('applicant_task_status')->nullable()->comment('1-Scheduled/2-Open/3-Inprogress/4-Submitted/5-Expired')->after('status');
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
        Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {  
            if (Schema::hasColumn('tbl_recruitment_task_applicant', 'applicant_task_status')) {
                $table->dropColumn('applicant_task_status');
            }
        });
    }
}
