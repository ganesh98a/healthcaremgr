<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAddServiceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation', 'org_source_code')) {
                $table->renameColumn('org_source_code', 'org_type');
            }

            if (!Schema::hasColumn('tbl_organisation', 'org_service_type')) {
                $table->unsignedInteger('org_service_type')->nullable()->after("org_source_code");
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
            if (Schema::hasColumn('tbl_organisation', 'org_type')) {
                $table->renameColumn('org_type', 'org_source_code');
            }

            if (Schema::hasColumn('tbl_organisation', 'org_service_type')) {
                $table->dropColumn('org_service_type');
            }
        });
    }
}
