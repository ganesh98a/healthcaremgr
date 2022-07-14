<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantCommentupdateTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant','flagged_status')) {
                    $table->smallInteger('flagged_status')->unsigned()->default(0)->comment('1-pendding,2-flagged,3-new')->change();
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
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            //
        });
    }
}
