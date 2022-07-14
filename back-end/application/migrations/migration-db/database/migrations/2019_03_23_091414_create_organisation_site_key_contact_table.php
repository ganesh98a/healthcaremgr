<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationSiteKeyContactTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_site_key_contact')) {
            Schema::create('tbl_organisation_site_key_contact', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('siteId');
                $table->string('firstname', 150);
                $table->string('lastname', 150);
                $table->string('position', 32);
                $table->string('department', 32);
                $table->unsignedInteger('type')->comment('1- Support Coordinator, 2- Member, 3- Key Contact, 4-Billing');
                $table->string('city', 64);
                $table->string('postal', 10);
                $table->unsignedTinyInteger('state');
                $table->unsignedTinyInteger('archive')->comment('0- Not, 1- Yes');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_organisation_site_key_contact');
    }

}
