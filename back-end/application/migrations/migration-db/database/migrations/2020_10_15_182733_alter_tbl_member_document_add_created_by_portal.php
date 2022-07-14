<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberDocumentAddCreatedByPortal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_documents', 'created_portal')) {
                $table->unsignedInteger('created_portal')->default(1)->nullable()->comment('1 - HCM / 2 - Member')->after("archive");
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
            if (Schema::hasColumn('tbl_member_documents', 'created_portal')) {
                $table->dropColumn('created_portal');
            }
        });
    }
}
