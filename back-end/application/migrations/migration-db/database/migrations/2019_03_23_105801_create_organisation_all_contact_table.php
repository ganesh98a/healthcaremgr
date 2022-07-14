<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationAllContactTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_all_contact')) {
            Schema::create('tbl_organisation_all_contact', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisationId')->index();
                $table->string('name', 64);
                $table->string('lastname', 64);
                $table->string('position', 20);
                $table->string('department', 32);
                $table->unsignedTinyInteger('type')->comment('1- Support Coordinator, 2- Member, 3- Key Contact, 4-Billing');
                $table->unsignedTinyInteger('archive')->comment('1- Yes, 0- No');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_organisation_all_contact');
    }

}
