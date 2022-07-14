<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSiteAsUpdateCommentColumnForDraftSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_site', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_organisation_site','status')) {
            $table->unsignedSmallInteger('status')->unsigned()->comment('1- Active, 0- Inactive, 2-Draft')->change();
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
        Schema::table('tbl_organisation_site', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_site','status')) {
                $table->unsignedSmallInteger('status')->unsigned()->comment('1- Yes, 0- No')->change();
            }
        });
    }
}
