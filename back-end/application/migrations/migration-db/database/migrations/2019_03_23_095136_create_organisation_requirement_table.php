<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationRequirementTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_requirement')) {
            Schema::create('tbl_organisation_requirement', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedTinyInteger('archive')->comment('1- Yes, 0- No');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_organisation_requirement');
    }

}
