<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblLocationAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_location_address', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('location_id')->unsigned()->comment("tbl_locations_master.id");
            $table->text('address', 255)->nullable();
            $table->string('street', 200)->nullable();
            $table->string('suburb', 100)->nullable();
            $table->unsignedInteger('postcode')->nullable();            
            $table->integer('state')->unsigned()->nullable()->comment("tbl_state.id");
            $table->foreign('state')->references('id')->on('tbl_state');             
            $table->string('lat', 200)->nullable();
            $table->string('lng', 200)->nullable();
            $table->unsignedTinyInteger('archive')->default(0)->comment('0 - Not/ 1 - Yes');
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('tbl_location_address');
    }
}
