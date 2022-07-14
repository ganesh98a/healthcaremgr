<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumetAttachmentAddEntity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_document_attachment', 'entity_id')){
                $table->unsignedInteger('entity_id')->nullable()->comment('reference based on  entity_type-table.id')->after('related_to');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'entity_type')){
                $table->unsignedInteger('entity_type')->nullable()->comment('1 - Applicant (tbl_recruitment_applicant) / 2- Member (tbl_member)')->after('entity_id');
            }
            if (!Schema::hasColumn('tbl_document_attachment', 'updated_by_type')){
                $table->unsignedInteger('updated_by_type')->nullable()->comment('1 - Applicant (tbl_recruitment_applicant) / 2- Member (tbl_member) to determine who updated the record')->after('updated_by');
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
            if (Schema::hasColumn('tbl_document_attachment', 'entity_id')) {
                $table->dropColumn('entity_id');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'updated_by_type')) {
                $table->dropColumn('updated_by_type');
            }
        });
    }
}
