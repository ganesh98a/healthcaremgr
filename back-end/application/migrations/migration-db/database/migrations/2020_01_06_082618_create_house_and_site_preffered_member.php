<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseAndSitePrefferedMember extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_house_and_site_preferred_member', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('siteId');
            $table->unsignedInteger('user_type')->comment('1 - site/2 - house');
            $table->unsignedInteger('memberId');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->unsignedSmallInteger('archive')->default('0')->comment('1- Yes, 0- No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_house_and_site_preferred_member');
    }

}
