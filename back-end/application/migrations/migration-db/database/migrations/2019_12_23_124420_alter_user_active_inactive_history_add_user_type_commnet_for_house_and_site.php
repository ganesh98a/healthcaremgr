<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserActiveInactiveHistoryAddUserTypeCommnetForHouseAndSite extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_user_active_inactive_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_user_active_inactive_history', 'user_type')) {
                $table->unsignedInteger('user_type')->comment('1 - member/2 - Participant/3 - organsiation/4 - house/5 - site')->change();
            }
        });

        Schema::table('tbl_disable_portal_access_note', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_disable_portal_access_note', 'user_type')) {
                $table->unsignedInteger('user_type')->comment('1 - member/2 - Participant/3 - organsiation/4 - house/5 - site')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_user_active_inactive_history', function (Blueprint $table) {
            //
        });
    }

}
