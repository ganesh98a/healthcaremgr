<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddMultipleColumnsParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant_docs', function (Blueprint $table) {
            if (
                !Schema::hasColumn('tbl_participant_docs', 'stage_id') &&
                !Schema::hasColumn('tbl_participant_docs', 'updated') &&
                !Schema::hasColumn('tbl_participant_docs', 'document_type') &&
                !Schema::hasColumn('tbl_participant_docs', 'document_signed') &&
                !Schema::hasColumn('tbl_participant_docs', 'envelope_id') &&
                !Schema::hasColumn('tbl_participant_docs', 'signed_file_path') &&
                !Schema::hasColumn('tbl_participant_docs', 'resend_docusign')
            ) {
                $table->unsignedInteger('stage_id');
                $table->timestamp('resend_docusign');
                $table->unsignedInteger('document_type')->comment('1=service agreement 2=funding concent 3=final service agreement');
                $table->unsignedInteger('document_signed')->comment('0=No 2=Yes');
                $table->string('envelope_id', 255);
                $table->string('signed_file_path', 255);
                $table->timestamp('updated');
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
        Schema::table('tbl_participant_docs', function (Blueprint $table) {
            if (
                Schema::hasColumn('tbl_participant_docs', 'stage_id') &&
                Schema::hasColumn('tbl_participant_docs', 'updated') &&
                Schema::hasColumn('tbl_participant_docs', 'document_type') &&
                Schema::hasColumn('tbl_participant_docs', 'document_signed') &&
                Schema::hasColumn('tbl_participant_docs', 'envelope_id') &&
                Schema::hasColumn('tbl_participant_docs', 'signed_file_path') &&
                Schema::hasColumn('tbl_participant_docs', 'resend_docusign')
            ) {
                $table->dropColumn('stage_id');
                $table->dropColumn('updated');
                $table->dropColumn('document_type');
                $table->dropColumn('document_signed');
                $table->dropColumn('envelope_id');
                $table->dropColumn('signed_file_path');
                $table->dropColumn('resend_docusign');
            }
        });
    }
}
