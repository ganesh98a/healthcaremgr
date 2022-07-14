<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAllContactAsUpdateComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_all_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_all_contact','type')) {
                $table->unsignedSmallInteger('type')->unsigned()->comment('1- Support Coordinator, 2- Member, 3- Key Contact, 4-Billing, 5-Other')->change();
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
        Schema::table('tbl_organisation_all_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_all_contact','type')) {
                $table->unsignedSmallInteger('type')->unsigned()->comment('1- Support Coordinator, 2- Member, 3- Key Contact, 4-Billing')->change();
            }
        });
    }
}
