<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationDocsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_organisation_docs')) {
            Schema::create('tbl_organisation_docs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisationId')->index();
                $table->string('filename', 64);
                $table->string('title', 64);
                $table->date('expiry', 64);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_organisation_docs');
    }

}
