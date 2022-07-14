<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentActionApplicantChangeNameAsTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_action_applicant', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_action_applicant', 'tbl_recruitment_task_applicant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_action_applicant', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_task_applicant', 'tbl_recruitment_action_applicant');
        });
    }
}
