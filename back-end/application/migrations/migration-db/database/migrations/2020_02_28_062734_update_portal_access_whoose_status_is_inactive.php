<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePortalAccessWhooseStatusIsInactive extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_member', function (Blueprint $table) {
            DB::update('update tbl_participant set portal_access = 0 where status = 0');
            DB::update('update tbl_member set enable_app_access = 0 where status = 0');
            DB::update('update tbl_organisation set enable_portal_access = 0 where status = 0');
            DB::update('update tbl_organisation_site set enable_portal_access = 0 where status = 0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member', function (Blueprint $table) {
            //
        });
    }

}
