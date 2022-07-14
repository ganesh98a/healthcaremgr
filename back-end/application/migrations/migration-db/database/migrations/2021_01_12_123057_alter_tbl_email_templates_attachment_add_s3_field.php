<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblEmailTemplatesAttachmentAddS3Field extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_email_templates_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'file_path')){
                $table->text('file_path')->nullable()->comment('file storage path')->after('filename');
            }
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'aws_object_uri')){
                $table->text('aws_object_uri')->nullable()->comment('AWS object file url')->after('file_path');
            }
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'aws_response')){
                $table->text('aws_response')->nullable()->comment('AWS response')->after('aws_object_uri');
            }
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'aws_uploaded_flag')){
                $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->comment('1 - Yes / 0 - No')->after('aws_response');
            }
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'aws_file_version_id')){
                $table->text('aws_file_version_id')->nullable()->comment('it is used to get the file with version if duplicated')->after('aws_uploaded_flag');
            }
            if (!Schema::hasColumn('tbl_email_templates_attachment', 'updated')){
                $table->datetime('updated')->nullable()->after('created');
            }
        });

        Schema::table('tbl_s3_logs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_s3_logs', 'module_id')) {
                $table->unsignedInteger('module_id')->comment('1-Recruitment/2-Member/3-Sales/4-Imail')->change();
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
        Schema::table('tbl_email_templates_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_email_templates_attachment', 'file_path')) {
                $table->dropColumn('file_path');
            }
            if (Schema::hasColumn('tbl_email_templates_attachment', 'aws_object_uri')) {
                $table->dropColumn('aws_object_uri');
            }
            if (Schema::hasColumn('tbl_email_templates_attachment', 'aws_response')) {
                $table->dropColumn('aws_response');
            }
            if (Schema::hasColumn('tbl_email_templates_attachment', 'aws_uploaded_flag')) {
                $table->dropColumn('aws_uploaded_flag');
            }
            if (Schema::hasColumn('tbl_email_templates_attachment', 'aws_file_version_id')) {
                $table->dropColumn('aws_file_version_id');
            }
            if (Schema::hasColumn('tbl_email_templates_attachment', 'updated')) {
                $table->dropColumn('updated');
            }
        });

        Schema::table('tbl_s3_logs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_s3_logs', 'module_id')) {
                $table->unsignedInteger('module_id')->comment('1-Recruitment/2-Member/3-Sales')->change();
            }
        });
    }
}
