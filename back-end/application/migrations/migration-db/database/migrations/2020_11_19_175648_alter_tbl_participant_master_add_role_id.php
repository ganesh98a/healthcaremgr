<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantMasterAddRoleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participants_master', 'role_id')){
                $table->unsignedInteger('role_id')->nullable()->comment('reference of tbl_member_role.id')->after('active');
                $table->foreign('role_id')->references('id')->on('tbl_member_role')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participants_master', 'role_id')) {
                // Drop foreign key
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });
    }
}
