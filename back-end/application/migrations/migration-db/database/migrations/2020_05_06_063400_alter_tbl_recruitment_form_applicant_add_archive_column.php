<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantAddArchiveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_recruitment_form_applicant','archive')){
                $table->unsignedSmallInteger('archive')->default('0')->comment("0 -Not / 1 - Yes");
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
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_recruitment_form_applicant','archive')){
                $table->dropColumn('archive');
            }
        });
    }
}
