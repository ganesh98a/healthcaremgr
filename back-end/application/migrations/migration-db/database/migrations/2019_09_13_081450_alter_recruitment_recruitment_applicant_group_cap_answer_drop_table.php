<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentRecruitmentApplicantGroupCapAnswerDropTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_cap_answer', function (Blueprint $table) {
            Schema::dropIfExists('tbl_recruitment_applicant_group_cap_answer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_cap_answer', function (Blueprint $table) {
            
        });
    }

}
