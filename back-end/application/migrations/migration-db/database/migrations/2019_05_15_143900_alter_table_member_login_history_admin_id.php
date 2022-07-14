<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMemberLoginHistoryAdminId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_member_login_history')) {
            Schema::table('tbl_member_login_history', function (Blueprint $table) {
                $table->renameColumn('adminId', 'memberId');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_member_login_history')) {
            Schema::table('tbl_member_login_history', function (Blueprint $table) {
                $table->renameColumn('memberId', 'adminId');
            });
        }
    }

}
