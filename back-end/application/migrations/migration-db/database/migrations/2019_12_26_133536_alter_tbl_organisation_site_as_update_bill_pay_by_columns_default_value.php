<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteAsUpdateBillPayByColumnsDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_site', function (Blueprint $table) {
            $table->unsignedInteger('bill_pay_by')->default('3')->change();
        });
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
