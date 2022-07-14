<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_organisation_address')) {
            Schema::create('tbl_organisation_address', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('organisationId')->default(0)->index('organisationId');
                    $table->string('street', 128);
                    $table->string('city', 64);
                    $table->unsignedInteger('postal')->unsigned();
                    $table->unsignedInteger('state');
                    $table->unsignedTinyInteger('category');
                    $table->unsignedTinyInteger('primary_address')->comment('1- Primary, 2- Secondary');
                });
                DB::statement('ALTER TABLE `tbl_organisation_address` CHANGE `postal` `postal` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_organisation_address');
    }
}
