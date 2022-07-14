<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachmentPropertyAddDocusign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            $table->string('envelope_id',255)->nullable()->comment('docusign api unique id');
            $table->text('unsigned_file')->nullable();
            $table->unsignedSmallInteger('signed_status')->default('0')->comment('0- mean not signed yet,1-sigend');
            $table->dateTime('signed_date')->nullable()->default('0000-00-00 00:00:00');
            $table->unsignedInteger('sent_by')->nullable()->comment('reference of tbl_member.id');
        });

        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            $table->unsignedInteger('task_applicant_id')->nullable()->comment('auto increment id of tbl_recruitment_task_applicant table.');
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
            if (Schema::hasColumn('tbl_document_attachment', 'task_applicant_id')) {
                $table->dropColumn('task_applicant_id');
            }
        });
        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment_property', 'task_applicant_id')) {
                $table->dropColumn('task_applicant_id');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'envelope_id')) {
                $table->dropColumn('envelope_id');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'unsigned_file')) {
                $table->dropColumn('unsigned_file');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'signed_date')) {
                $table->dropColumn('signed_date');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'sent_by')) {
                $table->dropColumn('sent_by');
            }
            if (Schema::hasColumn('tbl_document_attachment_property', 'signed_status')) {
                $table->dropColumn('signed_status');
            }
        });
    }
}
