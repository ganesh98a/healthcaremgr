<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachmentForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment', 'created_by')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_created_by_foreign');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'updated_by')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_updated_by_foreign');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'doc_type_id')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_member_id_foreign');
            }

            $table->unsignedInteger('doc_type_id')->comment('reference of tbl_document_type.id')->change();
            $table->foreign('doc_type_id')->references('id')->on('tbl_document_type')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('created_by')->comment('reference of (if uploaded by applicant 1 then tbl_recruitment_applicant.id) or tbl_member.id')->change();
            $table->unsignedInteger('updated_by')->comment('reference of (if uploaded by applicant 1 then tbl_recruitment_applicant.id) or tbl_member.id')->change();
        });

        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment_property', 'created_by')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_attachment_created_by_foreign');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'updated_by')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_attachment_updated_by_foreign');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'doc_id')) {
                // Drop foreign key
                $table->dropForeign('tbl_member_documents_attachment_doc_id_foreign');
            }
            $table->foreign('doc_id')->references('id')->on('tbl_document_attachment')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('created_by')->comment('reference of (if uploaded by applicant 1 then tbl_recruitment_applicant.id) or tbl_member.id')->change();
            $table->unsignedInteger('updated_by')->comment('reference of (if uploaded by applicant 1 then tbl_recruitment_applicant.id) or tbl_member.id')->change();
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
            $table->unsignedInteger('created_by')->comment('reference of tbl_member.id')->change();
            $table->unsignedInteger('updated_by')->comment('reference of tbl_member.id')->change();
        });

        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->comment('reference of tbl_member.id')->change();
            $table->unsignedInteger('updated_by')->comment('reference of tbl_member.id')->change();
        });
    }
}
