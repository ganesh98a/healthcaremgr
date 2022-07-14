<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAsRemoveAndAddColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->renameColumn('status', 'document_status');
            $table->renameColumn('task_complete', 'quiz_status');
        });
        
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {

            $table->unsignedInteger('document_status')->comment('status of document,Column used for CAB day')->change();
            $table->unsignedInteger('quiz_status')->comment('status of Quiz,Column used for CAB/Group interview day')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->renameColumn('document_status', 'status');
            $table->renameColumn('quiz_status', 'task_complete');
        });

        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->unsignedInteger('status')->comment('applicant task status (Pending- 0,Unsuccessful- 2,Successful- 1)')->change();
            $table->unsignedInteger('task_complete')->comment('0 not complete,1 complete')->change();
        });
    }

}
