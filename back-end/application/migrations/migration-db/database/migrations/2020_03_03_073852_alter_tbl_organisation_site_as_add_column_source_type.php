<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteAsAddColumnSourceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_site', function (Blueprint $table) {
            $table->unsignedInteger('source_type')->default('0')->comment('0 - HCM/1 - org portal');
            $table->unsignedSmallInteger('status')->unsigned()->comment('1- Active/Approve, 0- Inactive, 2- Draft, 3- Pending, 4- Reject')->change();
            $table->datetime('approve_reject_time');
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
         $table->dropColumn('source_type');
         $table->dropColumn('approve_reject_time');
         $table->unsignedSmallInteger('status')->unsigned()->comment('1- Active, 0- Inactive, 2-Draft')->change();
     });
    }
}
