<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberLoginHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_login_history', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_login_history', 'status_id')) {
                $table->unsignedInteger('status_id')->default(1)->comment('1 = success, 2 = failed')->after('status_msg');
            }

            if (!Schema::hasColumn('tbl_member_login_history', 'application')) {
                $table->unsignedInteger('application')->default(1)->comment('1 = desktop, 2 = mobile, 3 = tablet')->after('status_id');
            }

            if (!Schema::hasColumn('tbl_member_login_history', 'country')) {
                $table->text('country')->after('location');
            }

            if (!Schema::hasColumn('tbl_member_login_history', 'login_url')) {
                $table->text('login_url')->after('location');
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
            if (Schema::hasColumn('tbl_member_login_history', 'status_id')) {
                $table->dropColumn('status_id');
            }
            if (Schema::hasColumn('tbl_member_login_history', 'application')) {
                $table->dropColumn('application');
            }
            if (Schema::hasColumn('tbl_member_login_history', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('tbl_member_login_history', 'login_url')) {
                $table->dropColumn('login_url');
            }
        });
    }
}
