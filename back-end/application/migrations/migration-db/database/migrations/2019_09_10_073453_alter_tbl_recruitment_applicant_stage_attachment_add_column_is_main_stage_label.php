<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantStageAttachmentAddColumnIsMainStageLabel extends Migration
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
                
                
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','is_main_stage_label')) {
                    $table->unsignedSmallInteger('is_main_stage_label')->default(0)->comment('0- sub stage and stage column value is primary key tbl_recruitment_stage,1 - is main stage and stage column value is primary key tbl_recruitment_stage_label');
                }
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','document_status')) {
                    $table->unsignedSmallInteger('document_status')->default(0)->comment('0-Pending,1-Successful,2-Rejected');
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
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','is_main_stage_label')){
                    $table->dropColumn('is_main_stage_label');
                }
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','document_status')){
                    $table->dropColumn('document_status');
                }
            });

        }
    }
}
