<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationSitePhoneTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_site_phone')) {
            Schema::create('tbl_organisation_site_phone', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('siteId');
                $table->string('phone', 20);
                $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary')->default('1');
                $table->unsignedTinyInteger('archive')->default('0');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_organisation_site_phone');
    }

}
