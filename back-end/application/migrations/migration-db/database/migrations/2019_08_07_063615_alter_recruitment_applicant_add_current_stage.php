<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantAddCurrentStage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'current_stage')) {
                $table->unsignedInteger('current_stage')->comment('primary key of tbl_recruitment_stage');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant', 'current_stage')) {
                $table->dropColumn('current_stage');
            }
        });
    }

}
