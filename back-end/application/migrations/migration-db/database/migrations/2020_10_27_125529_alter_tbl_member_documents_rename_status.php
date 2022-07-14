<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberDocumentsRenameStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_documents', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_documents','status')) {
                $table->renameColumn('status', 'document_status');
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
        Schema::table('tbl_member_documents', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_documents','document_status')) {
                $table->renameColumn('document_status', 'status');
            }
        });
    }
}
