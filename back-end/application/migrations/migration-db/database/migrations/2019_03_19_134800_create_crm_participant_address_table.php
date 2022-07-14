<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_address')) {
            Schema::create('tbl_crm_participant_address', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->string('street',128);
                $table->string('city',64);
                $table->string('postal',10);
                $table->unsignedTinyInteger('state');
                $table->unsignedTinyInteger('address_type')->comment('1-Own Home,2-Family Home , 3 -Mum\'s House, 4- Dad\'s House , 5- Relative\'s House, 6- Friend\'s House, 7 - OnCall House');
                $table->string('lat',200)->nullable();
                $table->string('long',200)->nullable();
                $table->unsignedTinyInteger('site_category');
                $table->unsignedTinyInteger('primary_address')->comment('1- Primary, 2- Secondary');
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)'); 
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_crm_participant_address');
    }
}
