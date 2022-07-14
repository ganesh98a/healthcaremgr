<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddVisaTypeColumnInDocumentAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_document_attachment')) {
            Schema::table('tbl_document_attachment', function (Blueprint $table) {
                $table->unsignedInteger('visa_category')->nullable()->after('applicant_specific')->comment('tbl_member_visa_type_category.id');
                $table->unsignedInteger('visa_category_type')->nullable()->after('visa_category')->comment('tbl_member_visa_type.id');               
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
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment', 'visa_category')) {
                $table->dropColumn('visa_category');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'visa_category_type')) {
                $table->dropColumn('visa_category_type');
            }            
        });
    }
}
