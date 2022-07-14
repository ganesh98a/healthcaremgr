<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrganisationSiteKeyContactAddStreetColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_organisation_site_key_contact')) {
            Schema::table('tbl_organisation_site_key_contact', function (Blueprint $table) {
                $table->string('street',128)->after('type');
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
        if (Schema::hasTable('tbl_organisation_site_key_contact') && Schema::hasColumn('tbl_organisation_site_key_contact','street')) {
            Schema::table('tbl_organisation_site_key_contact', function ($table) {
               $table->dropColumn('street');
           });
       }
    }
}
