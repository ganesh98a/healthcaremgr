<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberLoginHistoryAddLocationArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_login_history', function (Blueprint $table) {
            $table->boolean('archive')->default(0)->after('status')->comment('1- Yes, 0- No');
            $table->longText('location')->nullable()->after('details')->comment('1 - pinned / 0 - unpinned');
            $table->text('status_msg')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('tbl_member_login_history') && Schema::hasColumn('tbl_member_login_history', 'archive') && Schema::hasColumn('tbl_member_login_history', 'location')) {
            Schema::table('tbl_member_login_history', function (Blueprint $table) {
                $table->dropColumn('archive');
                $table->dropColumn('location');
                $table->dropColumn('status_msg');
            });
            }
    }
}
