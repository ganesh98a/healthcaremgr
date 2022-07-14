<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantWorkAreaAsAddColumnKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_work_area', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_work_area', 'key')) {
                    $table->string("key",100)->nullable()->after('work_area');
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
        if (Schema::hasTable('tbl_recruitment_applicant_work_area')) {
            Schema::table('tbl_recruitment_applicant_work_area', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_work_area', 'key')) {
                    $table->dropColumn('key');
                } 
            });
        }
    }
}
