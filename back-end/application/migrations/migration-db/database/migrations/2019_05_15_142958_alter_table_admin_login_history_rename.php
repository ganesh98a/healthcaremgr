<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAdminLoginHistoryRename extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_admin_login_history')) {
            Schema::rename('tbl_admin_login_history', 'tbl_member_login_history');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_member_login_history')) {
            Schema::rename('tbl_member_login_history', 'tbl_admin_login_history');
        }
    }

}
