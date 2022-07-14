<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOcpIdColumnTblOrganisation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'ocp_id')) {
                $table->unsignedInteger('ocp_id')->comment('ocp_id_for_migration')->after('id');
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
            if (Schema::hasColumn('tbl_organisation', 'ocp_id')) {
                 $table->dropColumn('ocp_id');
            }
        });
    }
}
