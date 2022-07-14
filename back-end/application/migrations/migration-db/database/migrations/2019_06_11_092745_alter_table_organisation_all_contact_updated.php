<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOrganisationAllContactUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_organisation_all_contact')) {
            Schema::table('tbl_organisation_all_contact', function (Blueprint $table) {
                $table->timestamp('updated')->useCurrent();
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
        Schema::table('tbl_organisation_all_contact', function (Blueprint $table) {
            //
        });
    }
}
