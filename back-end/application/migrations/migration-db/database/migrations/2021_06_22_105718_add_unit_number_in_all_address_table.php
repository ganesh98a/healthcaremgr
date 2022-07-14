<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitNumberInAllAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_address', 'unit_number')) {
                $table->string('unit_number',255)->nullable()->after("applicant_id")->comments('Apartment/Unit number');
            }
        });

        Schema::table('tbl_location_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_location_address', 'unit_number')) {
                $table->string('unit_number',255)->nullable()->after("location_id")->comments('Apartment/Unit number');
            }
        });

        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation_address', 'unit_number')) {
                $table->string('unit_number',255)->nullable()->after("organisationId")->comments('Apartment/Unit number');
            }
        });

        Schema::table('tbl_person_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person_address', 'unit_number')) {
                $table->string('unit_number',255)->nullable()->after("address_type")->comments('Apartment/Unit number');
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
        Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_address', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });

        Schema::table('tbl_location_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_location_address', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });

        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_address', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });

        Schema::table('tbl_person_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person_address', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });
    }
}
