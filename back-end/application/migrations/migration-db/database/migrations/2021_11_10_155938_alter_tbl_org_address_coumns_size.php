<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrgAddressCoumnsSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_address', 'city')) {
                $table->string('city',255)->change();
            }
            if (Schema::hasColumn('tbl_organisation_address', 'street')) {
                $table->string('street',255)->change();
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
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_address', 'city')) {
                $table->string('city',64)->change();
            }
            if (Schema::hasColumn('tbl_organisation_address', 'street')) {
                $table->string('street',128)->change();
            }
        });
       
    }
}
