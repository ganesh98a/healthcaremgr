<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantStageAddActionBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_stage', function (Blueprint $table) {
            $table->renameColumn('completed_at', 'action_at')->comment('completed date time/ uncompleted date time')->change();
            $table->unsignedInteger('action_by')->comment('completed by / uncompleted by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_stage', function (Blueprint $table) {
            $table->dropColumn('action_by');
            $table->renameColumn('action_at', 'completed_at');
        });
    }

}
