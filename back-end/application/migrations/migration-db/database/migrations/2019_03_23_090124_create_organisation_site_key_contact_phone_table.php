<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationSiteKeyContactPhoneTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_site_key_contact_phone')) {
            Schema::create('tbl_organisation_site_key_contact_phone', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('contactId');
                $table->string('phone', 20);
                $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary')->default('1');
                $table->unsignedTinyInteger('archive')->default('0')->comment('0 - Not, 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_organisation_site_key_contact_phone');
    }

}
