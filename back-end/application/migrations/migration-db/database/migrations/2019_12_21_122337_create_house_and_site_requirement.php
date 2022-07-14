<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseAndSiteRequirement extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_house_and_site_requirement', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('user_type')->comment("1- Site, 2 - House");
            $table->unsignedInteger('siteId')->comment('primary key tbl_house/tbl_organisation_site');
            $table->unsignedInteger('requirementId')->comment('primary key tbl_organisation_requirement');
            $table->dateTime('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_house_and_site_requirement');
    }

}
