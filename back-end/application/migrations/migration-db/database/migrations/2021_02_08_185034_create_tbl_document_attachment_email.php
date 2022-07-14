<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocumentAttachmentEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_document_attachment_email', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('doc_attach_id')->nullable()->comment('tbl_document_attachment.id');
            $table->foreign('doc_attach_id')->references('id')->on('tbl_document_attachment')->onDelete('CASCADE');
            $table->text('subject',255);
            $table->text('email_content')->nullable();
            $table->unsignedInteger('cc_email_flag')->nullable()->comment('0 - No / 1 - Yes');
            $table->text('cc_email')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_document_attachment_email');
    }
}
