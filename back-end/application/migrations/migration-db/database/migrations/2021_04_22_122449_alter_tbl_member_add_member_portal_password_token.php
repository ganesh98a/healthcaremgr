<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAddMemberPortalPasswordToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'member_password_token')) {
                $table->text('member_password_token')->nullable()->after('two_factor_login')->comment('Reset password token for member only');
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
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'member_password_token')) {
                $table->dropColumn('member_password_token');
            }
        });
    }
}
