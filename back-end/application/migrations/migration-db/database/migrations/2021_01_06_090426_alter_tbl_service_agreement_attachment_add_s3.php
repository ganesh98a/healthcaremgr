<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAttachmentAddS3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'file_path')){
                $table->text('file_path')->nullable()->comment('file storage path')->after('signed_status');
            }
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'aws_object_uri')){
                $table->text('aws_object_uri')->nullable()->comment('AWS object file url')->after('file_path');
            }
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'aws_response')){
                $table->text('aws_response')->nullable()->comment('AWS response')->after('aws_object_uri');
            }
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'aws_uploaded_flag')){
                $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->comment('1 - Yes / 0 - No')->after('aws_response');
            }
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'aws_file_version_id')){
                $table->text('aws_file_version_id')->nullable()->comment('it is used to get the file with version if duplicated')->after('aws_uploaded_flag');
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
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'file_path')) {
                $table->dropColumn('file_path');
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'aws_object_uri')) {
                $table->dropColumn('aws_object_uri');
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'aws_response')) {
                $table->dropColumn('aws_response');
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'aws_uploaded_flag')) {
                $table->dropColumn('aws_uploaded_flag');
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'aws_file_version_id')) {
                $table->dropColumn('aws_file_version_id');
            }
        });
    }
}
