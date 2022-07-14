<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAddRoleIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'role_id')) {
                $table->unsignedSmallInteger('role_id')->default(0)->comment('tbl_member_role')->index();
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
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'role_id')) {
                $table->dropColumn('role_id');
            }
        });
    }
}
