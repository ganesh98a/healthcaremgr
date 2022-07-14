<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantContract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_contract', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_contract', 'applicant_id')) {
                $table->unsignedInteger('applicant_id')->nullable()->after('task_applicant_id');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_contract', 'application_id')) {
                $table->unsignedInteger('application_id')->nullable()->after('applicant_id');
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
        Schema::table('tbl_recruitment_applicant_contract', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_contract', 'applicant_id')) {
                $table->dropColumn('applicant_id');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_contract', 'application_id')) {
                $table->dropColumn('application_id');
            }
        });
    }
}
