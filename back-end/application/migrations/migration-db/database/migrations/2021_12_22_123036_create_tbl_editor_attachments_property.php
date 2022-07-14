<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblEditorAttachmentsProperty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_editor_attachments_property')) {
            Schema::create('tbl_editor_attachments_property', function (Blueprint $table) {
                $table->increments('id');
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'file_name')) {
                    $table->text('file_name')->nullable()->comment('Name of the file');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'file_type')) {
                    $table->string('file_type', 30)->nullable()->comment('File type');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'file_size')) {
                    $table->string('file_size', 255)->nullable()->comment('File size');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'file_ext')) {
                    $table->string('file_ext', 30)->nullable()->comment('File size');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'archive')) {
                    $table->unsignedInteger('archive')->dafault(0)->comment('1 - Yes / 0 - No');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'file_path')) {
                    $table->text('file_path')->nullable()->comment('file storage path');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'aws_object_uri')) {
                    $table->text('aws_object_uri')->nullable()->comment('AWS object file url');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'aws_response')) {
                    $table->text('aws_response')->nullable()->comment('AWS response');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'aws_uploaded_flag')) {
                    $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'aws_file_version_id')) {
                    $table->text('aws_file_version_id')->nullable()->comment('it is used to get the file with version if duplicated');
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'created_by')) {
                    $table->unsignedInteger('created_by')->nullable();
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'updated_by')) {
                    $table->unsignedInteger('updated_by')->nullable();
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'created')) {
                    $table->datetime('created')->nullable();
                }
                if (!Schema::hasColumn('tbl_editor_attachments_property', 'updated')) {
                    $table->datetime('updated')->nullable();
                }
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
        Schema::dropIfExists('tbl_editor_attachments_property');
    }
}
