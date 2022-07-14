<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteKeyContactAsAddColumnBillPayBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_site_key_contact', function (Blueprint $table) {
            $table->unsignedSmallInteger('bill_pay_by')->unsigned()->comment('1- Parent Org,2- Sub-org,3-Self')->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_organisation_site_key_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_site_key_contact','bill_pay_by')) {
                 $table->dropColumn('bill_pay_by');
            }
        });
    }
}
