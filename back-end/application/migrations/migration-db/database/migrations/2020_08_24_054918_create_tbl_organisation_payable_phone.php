<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOrganisationPayablePhone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_organisation_accounts_payable_phone', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organisationId')->comment('reference unique id of tbl_organisation');
            $table->foreign('organisationId')->references('id')->on('tbl_organisation')->onDelete('cascade');
            $table->string('phone', 20);
            $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary')->default('1');
            $table->unsignedTinyInteger('archive')->default('0')->comment('0 - Not, 1 - Yes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_organisation_accounts_payable_phone');
    }
}
