<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederForRecruitmentInterviewType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_interview_type', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_interview_type', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->comment("used for sorting purpose");
            }
        });

        $TypeSeederObj = new RecruitmentInterviewType();
        $TypeSeederObj->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_interview_type', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_interview_type', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
}
