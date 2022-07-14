<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberDocumentsStatusComment extends Migration
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
                $table->unsignedInteger('status')->nullable()->comment('0 - Submitted / 1 - Valid / 2 - InValid / 3 - Expired / 4 - Draft')->change();
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
            if (Schema::hasColumn('tbl_member_documents','status')) {
                $table->unsignedInteger('status')->nullable()->comment('1 - Submitted / 2 - Valid / 3 - InValid / 4 - Expired / 5 - Draft')->change();
            }
        });
    }
}
