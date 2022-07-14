<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachmentPropertyAddVersionId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_document_attachment_property', 'aws_file_version_id')){
                $table->text('aws_file_version_id')->nullable()->comment('it is used to get the file with version if duplicated')->after('aws_response');
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
        Schema::table('tbl_document_attachment_property', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment_property', 'aws_file_version_id')) {
                $table->dropColumn('aws_file_version_id');
            }
        });
    }
}
