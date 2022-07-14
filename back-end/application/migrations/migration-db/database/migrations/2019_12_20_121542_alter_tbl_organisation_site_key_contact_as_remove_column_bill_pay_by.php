<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteKeyContactAsRemoveColumnBillPayBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_house_and_site_key_contact','bill_pay_by')) {
                 $table->dropColumn('bill_pay_by');
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
        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
            //
        });
    }
}
