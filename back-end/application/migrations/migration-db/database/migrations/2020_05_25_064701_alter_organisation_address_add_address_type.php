<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrganisationAddressAddAddressType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation_address', 'address_type')) {
                $table->unsignedSmallInteger('address_type')->comment('1 Billing/2 - shipping');
            }
        });
		
		Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'created_by')) {
                $table->unsignedInteger('created_by')->comment('tbl_member.id')->after("created");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_address', 'address_type')) {
                $table->dropColumn('address_type');
            }
        });
		
		Schema::table('tbl_organisation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }

}
