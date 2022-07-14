<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskApplicantAddColumnCreatedDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_task_applicant')) {
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_task_applicant','created')) {
                    $table->dateTime('created')->default('0000-00-00 00:00:00')->comment('when attached applicant on this task create date');
                }
            });

            if (Schema::hasColumn('tbl_recruitment_task_applicant','created')) {
                DB::unprepared("UPDATE tbl_recruitment_task_applicant set created=CASE when invitation_send_at!='0000-00-00 00:00:00' THEN invitation_send_at ELSE now() END where archive=0");
              }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_recruitment_task_applicant')) {
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_task_applicant','created')) {
                    $table->dropColumn('created');
                }
            });
        }
    }
}
