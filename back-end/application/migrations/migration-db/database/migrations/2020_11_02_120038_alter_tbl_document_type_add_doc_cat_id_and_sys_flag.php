<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentTypeAddDocCatIdAndSysFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_document_type', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_document_type', 'doc_category_id')){
                $table->unsignedInteger('doc_category_id')->nullable()->comment('reference of tbl_document_category.id')->after('active');
            }
            if (!Schema::hasColumn('tbl_document_type', 'system_gen_flag')){
                $table->unsignedInteger('system_gen_flag')->default(0)->comment('determine the document type is used for system generated. if it is 1 - not editable')->after('active');
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
        Schema::table('tbl_document_type', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_type', 'doc_category_id')) {
                $table->dropColumn('doc_category_id');
            }
            if (Schema::hasColumn('tbl_document_type', 'system_gen_flag')) {
                $table->dropColumn('system_gen_flag');
            }
        });
    }
}
