<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationAllContactEmailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_all_contact_email')) {
            Schema::create('tbl_organisation_all_contact_email', function (Blueprint $table) {
                $table->unsignedInteger('contactId');
                $table->string('email', 64);
                $table->unsignedTinyInteger('primary_email')->comment('1- Primary, 2- Secondary');
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
        Schema::dropIfExists('tbl_organisation_all_contact_email');
    }

}
