<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'access_role_id')){
                $table->unsignedInteger('access_role_id')->nullable()->comment('tbl_access_role.id')->after('date_unlocked');
                $table->foreign('access_role_id')->references('id')->on('tbl_access_role')->onDelete(DB::raw('SET NULL'));
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
            if (Schema::hasColumn('tbl_member', 'access_role_id')) {
                // Drop foreign key
                $table->dropForeign(['access_role_id']);
                $table->dropColumn('access_role_id');
            }
        });
    }
}
