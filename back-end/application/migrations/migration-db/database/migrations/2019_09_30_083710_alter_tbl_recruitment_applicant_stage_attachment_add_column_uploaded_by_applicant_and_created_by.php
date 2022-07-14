<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantStageAttachmentAddColumnUploadedByApplicantAndCreatedBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','created_by')) {
                    $table->unsignedInteger('created_by')->default(0)->comment('uploaded_by_applicant value 0 for recuirter (tbl_member) and 1-for applicant(tbl_recruitment_applicant) table primary key');
                }
                
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','uploaded_by_applicant')) {
                    $table->unsignedSmallInteger('uploaded_by_applicant')->default(0)->comment('0 for recuirter 1-for applicant');
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
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','created_by')){
                    $table->dropColumn('created_by');
                }

                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','uploaded_by_applicant')){
                    $table->dropColumn('uploaded_by_applicant');
                } 
            });

        }
    }
}
