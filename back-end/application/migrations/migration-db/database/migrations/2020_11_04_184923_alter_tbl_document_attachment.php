<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_document_attachment', 'application_id')){
                $table->unsignedInteger('application_id')->nullable()->comment('reference of tbl_recruitment_job.id')->after('id');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'applicant_id')){
                $table->unsignedInteger('applicant_id')->nullable()->comment('reference of tbl_recruitment_applicant.id')->after('application_id');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'stage')){
                $table->unsignedInteger('stage')->nullable()->comment('primary key of tbl_recruitment_stage table')->after('applicant_id');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'draft_contract_type')){
                $table->unsignedInteger('draft_contract_type')->nullable()->comment('0-none, 1- group_interview, 2-cabday and this file not archive in ui functionlity side')->after('stage');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'is_main_stage_label')){
                $table->unsignedInteger('is_main_stage_label')->nullable()->comment('0- sub stage and stage column value is primary key tbl_recruitment_stage,1 - is main stage and stage column value is primary key tbl_recruitment_stage_label')->after('draft_contract_type');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'uploaded_by_applicant')){
                $table->unsignedInteger('uploaded_by_applicant')->nullable()->comment('0 for recuirter 1-for applicant')->after('is_main_stage_label');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'member_move_archive')){
                $table->unsignedInteger('member_move_archive')->nullable()->comment('0-already archive,1-active attachment archive when applicant move on member table,2-active attachment archive when applicant rejected')->after('uploaded_by_applicant');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'related_to')){
                $table->unsignedInteger('related_to')->nullable()->comment('1 - Recruitement / 2 - Member module')->after('member_move_archive');
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
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment', 'application_id')) {
                $table->dropColumn('application_id');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'applicant_id')) {
                $table->dropColumn('applicant_id');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'stage')) {
                $table->dropColumn('stage');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'draft_contract_type')) {
                $table->dropColumn('draft_contract_type');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'is_main_stage_label')) {
                $table->dropColumn('is_main_stage_label');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'uploaded_by_applicant')) {
                $table->dropColumn('uploaded_by_applicant');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'member_move_archive')) {
                $table->dropColumn('member_move_archive');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'related_to')) {
                $table->dropColumn('related_to');
            }
        });
    }
}
