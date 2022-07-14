<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantStageAttachmentAddIssueDateAndReferenceNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'issue_date')) {
                $table->dateTime('issue_date')->nullable()->comment('Eg. passport issue date by government');
            }

            if ( ! Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'reference_number')) {
                $table->string('reference_number', 20)->nullable();
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
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'issue_date')) {
                $table->dropColumn('issue_date');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
        });
    }
}
