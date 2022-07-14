<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteStatusColumnDefaultValueSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_organisation_site') && Schema::hasColumn('tbl_organisation_site','status')) {
            Schema::table('tbl_organisation_site', function (Blueprint $table) {
                $table->smallInteger('status')->tinyInteger('status')->unsigned()->default(1)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_organisation_site', function (Blueprint $table) {
            //
        });
    }
}
