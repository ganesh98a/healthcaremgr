<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberLoginHistoryTblMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_login_history', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_login_history', 'access_using')) {
                $table->unsignedInteger('access_using')->default(1)->comment('1 = password, 2 = ping')->after('application');
            }
        });

        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'is_locked')) {
                $table->unsignedInteger('is_locked')->default(0)->comment('0 = not locked, 1 = locked')->after('person_id');
            }

            if (!Schema::hasColumn('tbl_member', 'date_unlocked')) {
                $table->dateTime('date_unlocked')->nullable()->after('is_locked');
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
            if (Schema::hasColumn('tbl_member_login_history', 'access_using')) {
                $table->dropColumn('access_using');
            }
        });

        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
            if (Schema::hasColumn('tbl_member', 'date_unlocked')) {
                $table->dropColumn('date_unlocked');
            }
        });
    }
}
