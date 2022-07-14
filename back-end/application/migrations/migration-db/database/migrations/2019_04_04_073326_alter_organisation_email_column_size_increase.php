<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrganisationEmailColumnSizeIncrease extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_organisation_email') && Schema::hasColumn('tbl_organisation_email','email')) {
            Schema::table('tbl_organisation_email', function ($table) {
               $table->string('email',64)->change();
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
        Schema::table('tbl_organisation_email', function (Blueprint $table) {
            //
        });
    }
}
