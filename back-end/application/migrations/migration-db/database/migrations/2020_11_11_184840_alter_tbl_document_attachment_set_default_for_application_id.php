<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachmentSetDefaultForApplicationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // creating tbl_document_attachment
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment', 'application_id')){
                $table->unsignedInteger('application_id')->default(0)->nullable(false)->comment('reference of tbl_recruitment_job.id')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'draft_contract_type')){
                $table->unsignedInteger('draft_contract_type')->default(0)->nullable(false)->comment('0-none, 1- group_interview, 2-cabday and this file not archive in ui functionlity side')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'is_main_stage_label')){
                $table->unsignedInteger('is_main_stage_label')->default(0)->nullable(false)->comment('0- sub stage and stage column value is primary key tbl_recruitment_stage,1 - is main stage and stage column value is primary key tbl_recruitment_stage_label')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'stage')){
                $table->unsignedInteger('stage')->default(0)->nullable(false)->comment('primary key of tbl_recruitment_stage table')->after('applicant_id')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'uploaded_by_applicant')){
                $table->unsignedInteger('uploaded_by_applicant')->default(0)->nullable(false)->comment('0 for recuirter 1-for applicant')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'member_move_archive')){
                $table->unsignedInteger('member_move_archive')->default(0)->nullable(false)->comment('0-already archive,1-active attachment archive when applicant move on member table,2-active attachment archive when applicant rejected')->change();
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
            if (Schema::hasColumn('tbl_document_attachment', 'application_id')){
                $table->unsignedInteger('application_id')->nullable()->comment('reference of tbl_recruitment_job.id')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'application_id')){
                $table->unsignedInteger('application_id')->nullable()->comment('reference of tbl_recruitment_job.id')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'draft_contract_type')){
                $table->unsignedInteger('draft_contract_type')->nullable()->comment('0-none, 1- group_interview, 2-cabday and this file not archive in ui functionlity side')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'is_main_stage_label')){
                $table->unsignedInteger('is_main_stage_label')->nullable()->comment('0- sub stage and stage column value is primary key tbl_recruitment_stage,1 - is main stage and stage column value is primary key tbl_recruitment_stage_label')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'stage')){
                $table->unsignedInteger('stage')->nullable()->comment('primary key of tbl_recruitment_stage table')->after('applicant_id')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'uploaded_by_applicant')){
                $table->unsignedInteger('uploaded_by_applicant')->nullable()->comment('0 for recuirter 1-for applicant')->change();
            }
            if (Schema::hasColumn('tbl_document_attachment', 'member_move_archive')){
                $table->unsignedInteger('member_move_archive')->nullable()->comment('0-already archive,1-active attachment archive when applicant move on member table,2-active attachment archive when applicant rejected')->change();
            }
        });
    }
}
