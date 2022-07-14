<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationSiteTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_site')) {
            Schema::create('tbl_organisation_site', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisationId')->index();
                $table->string('site_name', 150);
                $table->string('street', 128);
                $table->string('city', 64);
                $table->string('postal', 10);
                $table->string('abn', 20);
                $table->unsignedTinyInteger('state');
                $table->unsignedTinyInteger('archive')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('status')->comment('1- Active, 0 - No');
                $table->unsignedTinyInteger('enable_portal_access')->comment('1- Yes, 0- No');
                $table->string('logo_file', 200);
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
        Schema::dropIfExists('tbl_organisation_site');
    }

}
