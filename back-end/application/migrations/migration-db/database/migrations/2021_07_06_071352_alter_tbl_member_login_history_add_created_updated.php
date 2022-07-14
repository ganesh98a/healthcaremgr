<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberLoginHistoryAddCreatedUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_login_history', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_login_history', 'created')) {
                $table->dateTime('created')->nullable();
            } 
            if (!Schema::hasColumn('tbl_member_login_history', 'updated')) {
                $table->dateTime('updated')->nullable();
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
        Schema::table('tbl_member_login_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_login_history', 'created')) {
                $table->dropColumn('created');
            }
            if (Schema::hasColumn('tbl_member_login_history', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }
}
