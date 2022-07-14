<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberDocumentsAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_documents_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('doc_id')->nullable()->comment('reference id of tbl_member_documents.id');
            $table->foreign('doc_id')->references('id')->on('tbl_member_documents')->onUpdate('cascade')->onDelete('cascade');
            $table->string('file_name',255)->nullable();
            $table->string('file_type',255)->nullable();
            $table->text('file_path')->nullable();
            $table->string('raw_name',255)->nullable();
            $table->string('file_ext',255)->nullable();
            $table->string('file_size',255)->nullable();
            $table->text('aws_object_uri')->nullable();
            $table->text('aws_response')->nullable();
            $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->comment('reference id of tbl_member.id');
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('reference id of tbl_member.id');
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member_documents_attachment');
    }
}
