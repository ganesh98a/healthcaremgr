<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocumentTypeRelated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_document_type_related', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('doc_type_id')->default(0)->comment('reference of tbl_document_type.id');
            $table->foreign('doc_type_id')->references('id')->on('tbl_document_type')->onDelete('CASCADE');
            $table->unsignedInteger('related_to')->default(0)->comment('1 - Recruitment / 2 - Member');
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
        Schema::dropIfExists('tbl_document_type_related');
    }
}
