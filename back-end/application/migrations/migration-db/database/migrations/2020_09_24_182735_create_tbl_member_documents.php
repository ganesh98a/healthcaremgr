<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->nullable()->comment('reference id of tbl_member.id');
            $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('doc_type_id')->nullable()->comment('reference id of tbl_documents.id');
            $table->foreign('doc_type_id')->references('id')->on('tbl_documents')->onUpdate('cascade')->onDelete('cascade');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('reference_number')->nullable();
            $table->unsignedInteger('status')->nullable()->comment('1 - Submitted / 2 - Valid / 3 - InValid / 4 - Expired / 5 - Draft');
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
        Schema::dropIfExists('tbl_member_documents');
    }
}
