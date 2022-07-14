<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title',255);
            $table->unsignedInteger('issue_date_mandatory')->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('expire_date_mandatory')->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('reference_number_mandatory')->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('active')->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
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
        Schema::dropIfExists('tbl_documents');
    }
}
