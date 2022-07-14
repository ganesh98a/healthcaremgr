<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentApplicantStageAttachmentAddDraftContractType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_recruitment_applicant_stage_attachment')){
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','draft_contract_type')){
                    $table->unsignedSmallInteger('draft_contract_type')->nullable()->default(0)->comment('0-none,1- group_interview,2-cabday and this file not archive in ui functionlity side')->after('doc_category');
                }
                //
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
        if(Schema::hasTable('tbl_recruitment_applicant_stage_attachment')){
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','draft_contract_type')){
                    $table->dropColumn('draft_contract_type');
                }
                //
            });
        }
    }
}
