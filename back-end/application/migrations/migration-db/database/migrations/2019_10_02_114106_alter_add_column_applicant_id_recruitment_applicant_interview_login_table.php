<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnApplicantIdRecruitmentApplicantInterviewLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_interview_login')) {
            Schema::table('tbl_recruitment_applicant_interview_login', function (Blueprint $table) {
                //$table->string('applicant_id',150)->after('id');
                $table->unsignedInteger('applicant_id')->default('0')->after('id')->comment('primary key tbl_recruitment_applicant');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){       
        if (Schema::hasTable('tbl_recruitment_applicant_interview_login') && Schema::hasColumn('tbl_recruitment_applicant_interview_login', 'applicant_id')) {
         Schema::table('tbl_recruitment_applicant_interview_login', function (Blueprint $table) {
            $table->dropColumn('applicant_id');
         });
        }
    }
}
